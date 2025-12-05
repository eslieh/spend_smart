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

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents("php://input"));
    $period = $data->period ?? '';
    $limit_amount = (float)($data->limit_amount ?? '');
    $start_date = $data->start_date ?? '';
    $end_date = $data->end_date ?? '';
    $query = "INSERT INTO budgets (user_id, period, limit_amount, start_date, end_date) VALUES ('$user_id', '$period', '$limit_amount', '$start_date', '$end_date')";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Budget created successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to create budget, " . mysqli_error($conn)
        ]);
    }
    
}elseif($_SERVER['REQUEST_METHOD'] === 'GET'){     
    $query = "SELECT * FROM budgets WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    http_response_code(200);
    echo json_encode($data);
}elseif($_SERVER['REQUEST_METHOD'] === 'PUT'){
    $data = json_decode(file_get_contents("php://input"));
    $period = $data->period ?? '';
    $limit_amount = (float)($data->limit_amount ?? '');
    $start_date = $data->start_date ?? '';
    $end_date = $data->end_date ?? '';
    $id = $data->id ?? '';
    $query = "UPDATE budgets SET period='$period', limit_amount='$limit_amount', start_date='$start_date', end_date='$end_date' WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Budget updated successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update budget, " . mysqli_error($conn)
        ]);
    }
}elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id ?? '';
    $query = "DELETE FROM budgets WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Budget deleted successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to delete budget, " . mysqli_error($conn)
        ]);
    }
}else{
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}
