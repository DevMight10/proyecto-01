<?php
class Product {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll($categoria_id = null, $activo = true) {
        $sql = "SELECT p.*, c.nombre as categoria_nombre 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.activo = ?";
        
        $params = [$activo];
        
        if ($categoria_id) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $categoria_id;
        }
        
        $sql .= " ORDER BY p.fecha_creacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.id = ? AND p.activo = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getFeatured($limit = 6) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM productos 
            WHERE activo = 1 
            ORDER BY fecha_creacion DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($nombre, $descripcion, $precio, $categoria_id, $imagen, $stock = 0) {
        $stmt = $this->pdo->prepare("
            INSERT INTO productos (nombre, descripcion, precio, categoria_id, imagen, stock) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $imagen, $stock]);
    }
    
    public function update($id, $nombre, $descripcion, $precio, $categoria_id, $imagen = null, $stock = 0) {
        if ($imagen) {
            $stmt = $this->pdo->prepare("
                UPDATE productos 
                SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?, imagen = ?, stock = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $imagen, $stock, $id]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE productos 
                SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?, stock = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $stock, $id]);
        }
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1");
        return $stmt->fetchColumn();
    }
}
?>
