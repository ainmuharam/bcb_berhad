<?php
// process.php
header('Content-Type: application/json');

$output = shell_exec('/var/www/html/bcb_berhad/venv/bin/python /var/www/html/bcb_berhad/process_face.py 2>&1');
echo $output ?: json_encode(['error' => 'No output from script']);
?>