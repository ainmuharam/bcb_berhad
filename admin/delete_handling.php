<?php
include 'depart_handling.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['department_id'])) {
    $departmentId = $_POST['department_id'];
    $department = new Department();
    
    // Delete the department
    $department->deleteDepartment($departmentId);
    
    // Get the updated total count of departments
    $departmentData = $department->getDepartments();
    $totalDepartments = $departmentData['total'];

    // Return the total count as a JSON response
    echo json_encode(['total' => $totalDepartments]);
}
?>