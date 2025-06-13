<?php
class RegisterHandler {
    public static function handle($database, $postData) {
        $employeeId = preg_replace("/[^a-zA-Z0-9_-]/", '', trim($postData['employeeId']));
        $name = htmlspecialchars(trim($postData['name']));
        $department = htmlspecialchars(trim($postData['department']));
        $email = filter_var(trim($postData['email']), FILTER_VALIDATE_EMAIL);
        $password = $postData['password'];
        $role_id = intval($postData['role_id']);
        $profile_picture = $postData['profile_picture'] ?? null;

        if (!$email) {
            throw new Exception("Invalid email address.");
        }

        // Handle and save profile picture
        if (preg_match('/^data:image\/(\w+);base64,/', $profile_picture, $type)) {
            $data = substr($profile_picture, strpos($profile_picture, ',') + 1);
            $data = base64_decode($data);
            if ($data === false) throw new Exception('Base64 decode failed');

            $fileName = 'employee_picture/' . $employeeId . '_' . uniqid() . '.png';
            if (!is_dir('employee_picture')) {
                mkdir('employee_picture', 0755, true);
            }
            if (file_put_contents($fileName, $data) === false) {
                throw new Exception('Failed to save image');
            }
        } else {
            throw new Exception('Please capture user image for register!');
        }

        // Create user
        $user = new User($database);
        $result = $user->create($employeeId, $name, $department, $email, $password, $fileName, $role_id);
        return $result;
    }
}
?>