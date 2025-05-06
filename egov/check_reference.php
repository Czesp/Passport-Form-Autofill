<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "passport_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $reference = trim($_GET['ref'] ?? '');
    
    if(empty($reference)) {
        throw new Exception("Reference number is required");
    }

    $stmt = $conn->prepare("SELECT * FROM applicants WHERE reference = :ref");
    $stmt->bindParam(':ref', $reference);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['exists' => true, 'data' => $application]);
    } else {
        echo json_encode(['exists' => false]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>