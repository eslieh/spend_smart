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

// create income
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $decodedToken['user_id'];
    $data = json_decode(file_get_contents("php://input"));
    $amount = (float)($data->amount ?? '');
    $source = $data->source ?? '';
    $date = date('Y-m-d', strtotime($data->date ?? ''));
    $note = $data->note ?? '';
    
    try {

        $query = "INSERT INTO income (user_id, amount, source, date, note) VALUES ('$user_id', '$amount', '$source', '$date', '$note')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "amount" => $amount,
                "message" => "Income created successfully"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to create income: " . mysqli_error($conn)
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to create income: " . $e->getMessage()
        ]);
    }
}elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $decodedToken['user_id'];
    $dashboard_income = mysqli_query($conn, "SELECT SUM(amount) as total_income FROM income WHERE user_id='$user_id'");
    $dashboard_income_data = mysqli_fetch_assoc($dashboard_income);
    $query = "SELECT * FROM income WHERE user_id='$user_id'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Income fetched successfully",
        "data" => $data,
        "dashboard_income" => $dashboard_income_data
    ]);
}elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $user_id = $decodedToken['user_id'];
    $data = json_decode(file_get_contents("php://input"));
    $amount = (float)($data->amount ?? '');
    $source = $data->source ?? '';
    $date = date('Y-m-d', strtotime($data->date ?? ''));
    $note = $data->note ?? '';
    $id = $data->id ?? '';

    $query = "UPDATE income SET amount='$amount', source='$source', date='$date', note='$note' WHERE id='$id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Income updated successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update income"
        ]);
    }
}elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $user_id = $decodedToken['user_id'];
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id ?? '';

    $query = "DELETE FROM income WHERE id='$id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Income deleted successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to delete income"
        ]);
    }
}else{
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}
