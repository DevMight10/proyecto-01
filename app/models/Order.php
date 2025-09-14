<?php
class Order {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function create($usuario_id, $numero_pedido, $total, $items) {
        try {
            $this->pdo->beginTransaction();
            
            // Crear pedido
            $stmt = $this->pdo->prepare("
                INSERT INTO pedidos (usuario_id, numero_pedido, total, estado) 
                VALUES (?, ?, ?, 'pendiente')
            ");
            $stmt->execute([$usuario_id, $numero_pedido, $total]);
            $pedido_id = $this->pdo->lastInsertId();
            
            // Crear detalles del pedido
            $stmt = $this->pdo->prepare("
                INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($items as $item) {
                $subtotal = $item['precio'] * $item['cantidad'];
                $stmt->execute([
                    $pedido_id, 
                    $item['producto_id'], 
                    $item['cantidad'], 
                    $item['precio'], 
                    $subtotal
                ]);
            }
            
            $this->pdo->commit();
            return $pedido_id;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function getAll($estado = null) {
        $sql = "SELECT p.*, u.nombre as usuario_nombre, u.email as usuario_email 
                FROM pedidos p 
                JOIN usuarios u ON p.usuario_id = u.id";
        
        $params = [];
        if ($estado) {
            $sql .= " WHERE p.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY p.fecha_pedido DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.nombre as usuario_nombre, u.email as usuario_email 
            FROM pedidos p 
            JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getDetails($pedido_id) {
        $stmt = $this->pdo->prepare("
            SELECT pd.*, pr.nombre as producto_nombre, pr.imagen as producto_imagen 
            FROM pedido_detalles pd 
            JOIN productos pr ON pd.producto_id = pr.id 
            WHERE pd.pedido_id = ?
        ");
        $stmt->execute([$pedido_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateStatus($id, $estado, $actualizado_por) {
        $stmt = $this->pdo->prepare("
            UPDATE pedidos 
            SET estado = ?, actualizado_por = ?, fecha_actualizacion = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$estado, $actualizado_por, $id]);
    }
    
    public function getByUser($usuario_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM pedidos 
            WHERE usuario_id = ? 
            ORDER BY fecha_pedido DESC
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPendingCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'");
        return $stmt->fetchColumn();
    }
    
    public function generateOrderNumber() {
        return 'MC' . date('Ymd') . rand(1000, 9999);
    }
}
?>
