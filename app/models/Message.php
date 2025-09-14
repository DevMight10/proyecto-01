<?php
class Message {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function create($nombre, $email, $asunto, $mensaje) {
        $stmt = $this->pdo->prepare("
            INSERT INTO mensajes (nombre, email, asunto, mensaje) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$nombre, $email, $asunto, $mensaje]);
    }
    
    public function getAll($leido = null) {
        $sql = "SELECT * FROM mensajes";
        $params = [];
        
        if ($leido !== null) {
            $sql .= " WHERE leido = ?";
            $params[] = $leido;
        }
        
        $sql .= " ORDER BY fecha_envio DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM mensajes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function markAsRead($id) {
        $stmt = $this->pdo->prepare("UPDATE mensajes SET leido = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM mensajes WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getUnreadCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mensajes WHERE leido = 0");
        return $stmt->fetchColumn();
    }
}
?>
