<?php
$api_url = "http://127.0.0.1:5001/face_recognition";

$options = array(
    "http" => array(
        "header"  => "Content-type: application/json",
        "method"  => "POST",
        "content" => json_encode([]) 
    )
);

$context  = stream_context_create($options);
$response = file_get_contents($api_url, false, $context);

if ($response === FALSE) {
    echo json_encode(["status" => "error", "message" => "Unable to reach Python API"]);
    exit();
}

echo $response;
?>
