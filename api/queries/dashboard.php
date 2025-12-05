<?php
header('Content-Type: application/json; charset=utf-8');

include '../../helper.php';
require alias('@/config/conn.php');
require alias('@/authorization.php');

$get_authorization = getallheaders()['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $get_authorization);
$decodedToken = verifyAccessToken($token);

if (!$decodedToken) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access. Invalid or missing token."
    ]);
    exit();
}

$user_id = $decodedToken['user_id'];  

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $query = "SELECT SUM(amount) as total_income FROM income WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    http_response_code(200);
    echo json_encode($data);
}else{
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}