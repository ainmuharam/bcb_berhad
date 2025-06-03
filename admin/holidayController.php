<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/add_public_holiday.php';

class HolidayController {
    private $db;
    private $holiday;

    public function __construct() {
        $this->initialize();
        $this->db = new Database();
        $this->holiday = new PublicHoliday($this->db->conn);
    }

    private function initialize() {
        header('Content-Type: application/json');
        ini_set('display_errors', 0);
        error_reporting(E_ALL);
        ini_set('log_errors', 1);
    }

    public function handleRequest() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Method not allowed", 405);
            }

            $this->validateInput();
            $this->processCreation();
            
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    private function validateInput() {
        if (empty($_POST['holiday_date']) || empty($_POST['holiday_name'])) {
            throw new Exception("Both date and name are required", 422);
        }
    }

    private function processCreation() {
        $this->holiday->holiday_date = $_POST['holiday_date'];
        $this->holiday->holiday_name = $_POST['holiday_name'];

        if (!$this->holiday->create()) {
            throw new Exception("Failed to create holiday", 500);
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'holiday_date' => $this->holiday->holiday_date,
                'holiday_name' => $this->holiday->holiday_name
            ]
        ]);
    }

    private function sendErrorResponse($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
}