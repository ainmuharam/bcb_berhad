<?php
include_once __DIR__ . '/../database.php';

class Request {
    private $db;

    public function __construct($db) {
        $this->db = $db->conn;
    }

    public function getPendingManualLoginRequests() {
        $sql = "SELECT emp_id, date FROM manual_login WHERE status NOT IN ('approved', 'rejected')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->get_result();
    }
    public function getUserPendingRequest($emp_id) {
    $sql = "SELECT emp_id, date, status FROM manual_login WHERE emp_id = ? ORDER BY date DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    return $stmt->get_result();
}

}
?>
