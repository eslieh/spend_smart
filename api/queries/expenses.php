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
    $amount = (float)($data->amount ?? '');
    $category = $data->category ?? '';
    $date = date('Y-m-d', strtotime($data->date ?? ''));
    $note = $data->note ?? '';

    $query = "INSERT INTO expenses (user_id, amount, category, date, note) VALUES ('$user_id', '$amount', '$category', '$date', '$note')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Expense created successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to create expense"
        ]);
    }
    
}elseif($_SERVER['REQUEST_METHOD'] === 'GET'){
    $query = "SELECT * FROM expenses WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    http_response_code(200);
    echo json_encode($data);
}elseif($_SERVER['REQUEST_METHOD'] === 'PUT'){
    $data = json_decode(file_get_contents("php://input"));
    $amount = (float)($data->amount ?? '');
    $category = $data->category ?? '';
    $date = date('Y-m-d', strtotime($data->date ?? ''));
    $note = $data->note ?? '';
    $id = $data->id ?? '';
    $query = "UPDATE expenses SET amount='$amount', category='$category', date='$date', note='$note' WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Expense updated successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update expense"
        ]);
    }
}elseif($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id ?? '';
    $query = "DELETE FROM expenses WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Expense deleted successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to delete expense"
        ]);
    }
}else{
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}   