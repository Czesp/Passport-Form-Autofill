<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nid_db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    $nid = $conn->real_escape_string($_GET['nid'] ?? '');
    
    if(empty($nid)) {
        throw new Exception("NID parameter is required");
    }

    $stmt = $conn->prepare("SELECT * FROM citizens WHERE nid = ?");
    $stmt->bind_param("s", $nid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'NID not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>