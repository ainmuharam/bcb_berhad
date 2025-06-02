<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/bcb_berhad/database.php';

class Department {
    private $db;
    private $table_name = "departments"; // Your table name

    public function __construct() {
        $this->db = new Database(); // Assuming Database class handles the connection
    }

    public function getActiveDepartments() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 1";
        $stmt = $this->db->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }
    public function getActiveDepartmentsName() {
        $sql = "SELECT department_id, department_name FROM departments WHERE status = 1";
        $result = $this->db->conn->query($sql); 

        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[$row['department_id']] = $row['department_name'];
        }
        return $departments;
    }


    public function getDepartments($searchQuery = null) {
        $query = "SELECT department_id, department_name, created_at, status FROM " . $this->table_name;

        if (!empty($searchQuery)) {
            $query .= " WHERE department_id LIKE ? OR department_name LIKE ?";
        }

        // Prepare the statement
        $stmt = $this->db->conn->prepare($query);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->db->conn->error);
        }

        // Bind parameters if a search query is provided
        if (!empty($searchQuery)) {
            $searchTerm = '%' . $searchQuery . '%';
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
        }

        // Execute the statement
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all results as an associative array
        $departments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Return the departments and the total count
        return [
            'departments' => $departments,
            'total' => count($departments) // Return the total count of departments
        ];
    }

    // Function to add a new department
    public function addDepartment($departmentId, $departmentName) {
        // Check if the department ID already exists
        $stmt = $this->db->conn->prepare("SELECT COUNT(*) FROM " . $this->table_name . " WHERE department_id = ?");
        $stmt->bind_param("i", $departmentId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            return "Error: Department ID '$departmentId' already exists.";
        }
        $stmt = $this->db->conn->prepare("SELECT COUNT(*) FROM " . $this->table_name . " WHERE department_name = ?");
        $stmt->bind_param("s", $departmentName);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            return "Error: Department name '$departmentName' already exists.";
        }
        $stmt = $this->db->conn->prepare("INSERT INTO " . $this->table_name . " (department_id, department_name) VALUES (?, ?)");
        $stmt->bind_param("is", $departmentId, $departmentName); // "is" means integer and string

        // Execute the statement
        if ($stmt->execute()) {
            return "New department added successfully.";
        } else {
            return "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}


?>