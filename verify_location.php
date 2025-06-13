<?php
$data = json_decode(file_get_contents('php://input'), true);
$lat = $data['lat'];
$lon = $data['lon'];

$allowedLat = 2.0317;
$allowedLon = 103.3215;
$radiusKm = 0.5;

function getDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

$distance = getDistance($lat, $lon, $allowedLat, $allowedLon);
echo json_encode(["allowed" => $distance <= $radiusKm]);
?>
