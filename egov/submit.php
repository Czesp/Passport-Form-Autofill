<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// File upload config
$uploadDir = "uploads/";
$allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
$maxSize = 5 * 1024 * 1024;

// Create upload directory
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique reference number
function generateReference() {
    return 'REF-' . strtoupper(bin2hex(random_bytes(4)));
}

$referenceNumber = generateReference();

// Database config
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "passport_db";

// Handle file upload
$fileName = '';
if (isset($_FILES['citizenship_photo'])) {
    $file = $_FILES['citizenship_photo'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        if ($file['size'] > $maxSize) {
            http_response_code(413);
            die(json_encode(['success' => false, 'error' => 'File size exceeds 5MB']));
        }
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileType = $finfo->file($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            http_response_code(415);
            die(json_encode(['success' => false, 'error' => 'Invalid file type']));
        }

        $fileName = uniqid('doc_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => 'File upload failed']));
        }
    }
}

$requiredFields = ['nid', 'lastname', 'firstname', 'dob_ad'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        die(json_encode(['success' => false, 'error' => "$field is required"]));
    }
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("INSERT INTO applicants (
        reference, nid, lastname, firstname, gender, dob_ad, dob_bs, birth_place,
        father_last_name, father_first_name, mother_last_name, mother_first_name,
        province, district, municipality, ward, tole, mobile, email,
        citizenship_number, citizenship_issue_date, citizenship_issue_district,
        citizenship_photo, passport_type, pages, location
    ) VALUES (
        :ref, :nid, :lastname, :firstname, :gender, :dob_ad, :dob_bs, :birth_place,
        :father_last_name, :father_first_name, :mother_last_name, :mother_first_name,
        :province, :district, :municipality, :ward, :tole, :mobile, :email,
        :citizenship_number, :citizenship_issue_date, :citizenship_issue_district,
        :citizenship_photo, :passport_type, :pages, :location
    )");

    $params = [
        ':ref' => $referenceNumber,
        ':nid' => $_POST['nid'] ?? '',
        ':lastname' => $_POST['lastname'] ?? '',
        ':firstname' => $_POST['firstname'] ?? '',
        ':gender' => $_POST['gender'] ?? '',
        ':dob_ad' => $_POST['dob_ad'] ?? '',
        ':dob_bs' => $_POST['dob_bs'] ?? '',
        ':birth_place' => $_POST['birth_place'] ?? '',
        ':father_last_name' => $_POST['father_last_name'] ?? '',
        ':father_first_name' => $_POST['father_first_name'] ?? '',
        ':mother_last_name' => $_POST['mother_last_name'] ?? '',
        ':mother_first_name' => $_POST['mother_first_name'] ?? '',
        ':province' => $_POST['province'] ?? '',
        ':district' => $_POST['district'] ?? '',
        ':municipality' => $_POST['municipality'] ?? '',
        ':ward' => $_POST['ward'] ?? 0,
        ':tole' => $_POST['tole'] ?? '',
        ':mobile' => $_POST['mobile'] ?? '',
        ':email' => $_POST['email'] ?? '',
        ':citizenship_number' => $_POST['citizenship_number'] ?? '',
        ':citizenship_issue_date' => $_POST['citizenship_issue_date'] ?? '',
        ':citizenship_issue_district' => $_POST['citizenship_issue_district'] ?? '',
        ':citizenship_photo' => $fileName,
        ':passport_type' => $_POST['passport_type'] ?? '',
        ':pages' => $_POST['pages'] ?? 0,
        ':location' => $_POST['location'] ?? ''
    ];

    $stmt->execute($params);
    
    echo json_encode([
        'success' => true,
        'reference' => $referenceNumber
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>