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

    $date = $_GET['date'] ?? '';
    $date = date('Y-m-d', strtotime($date));
    $date_query = $_GET['date_query'] ?? 'date'; //can be month, year, week, day
    $search = $_GET['search'] ?? '';
    $search = "%$search%";
    
    if($date_query === 'date'){
        $date_query = "DATE(date) = '$date'";
    }elseif($date_query === 'month'){
        $date_query = "MONTH(date) = MONTH('$date')";
    }elseif($date_query === 'year'){
        $date_query = "YEAR(date) = YEAR('$date')";
    }elseif($date_query === 'week'){
        $date_query = "WEEK(date) = WEEK('$date')";
    }
    
    $query = "SELECT 
        id,
        amount,
        category AS type,
        date,
        note,
        'expense' AS record_type
    FROM expenses
    WHERE user_id = '$user_id'

    UNION ALL

    SELECT
        id,
        amount,
        source AS type,
        date,
        note,
        'income' AS record_type
    FROM income
    WHERE user_id = '$user_id'
    AND $date_query
    AND note LIKE '$search'
    ORDER BY date DESC;";
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