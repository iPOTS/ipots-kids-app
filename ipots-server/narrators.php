<?php
include('include/config.php');
include('include/database.php');
include('include/functions.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

$requestUri = $_SERVER['REQUEST_URI'];
$uriSegments = explode('/', trim($requestUri, '/'));
$lastSegment = end($uriSegments);
$secondLastSegment = prev($uriSegments);

// Create narrators table
try {
    $createNarratorsTable = "
        CREATE TABLE IF NOT EXISTS narrators (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            social_link VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";

    // Return error if creation fails
    if (!$connect->query($createNarratorsTable)) {
        throw new Exception("Table creation failed for narrators: " . $connect->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Internal Server Error",
        "error" => $e->getMessage()
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // GET: /narrators.php/:id
    // Get a single narrator entry
    if(is_numeric($id)) {
        $stmt = $connect->prepare("SELECT * FROM narrators WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $narrator = $result->fetch_assoc();

        if (!$narrator) {
            http_response_code(404);
            echo json_encode(["error" => "Narrator not found"]);
            exit();
        }

        echo json_encode($narrator);
        exit();
    } else {

        // GET: /narrators.php
        // Get all narrators
        $stmt = $connect->prepare("SELECT * FROM narrators");
        $stmt->execute();
        $result = $stmt->get_result();
        $narrators = $result->fetch_all(MYSQLI_ASSOC);

        if (!$narrators) {
            http_response_code(404);
            echo json_encode(["error" => "No narrators found"]);
            exit();
        }

        echo json_encode($narrators);
        exit();
    }
   
}

// DELETE: /narrators.php/:id
// Delete a single narrator entry
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid id parameter"]);
        exit();
    }

    try {
        $stmt = $connect->prepare("DELETE FROM narrators WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode([
            "status" => "success",
            "message" => "Narrator deleted successfully"
        ]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode([
            "status" => "error",
            "message" => "Internal Server Error",
            "error" => $e->getMessage()
        ]);
    }
}

// POST: /narrators.php/update/:id
// Update an existing narrator
// JSON body:{ 
    // "firstName": "Tom",
    // "lastName": "Smith",
    // "socialLink": "www.sample.com" 
// }
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && $secondLastSegment === 'update') {

    $data = json_decode(file_get_contents("php://input"), true);
    $id = $lastSegment;

    if ($data === null) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON"]);
        exit();
    }

    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid id parameter"]);
        exit();
    }

    $firstName = $data['firstName'];
    $lastName = $data['lastName'];
    $socialLink = $data['socialLink'];

    try {
        $stmt = $connect->prepare("
            UPDATE narrators
            SET first_name = ?, last_name = ?, social_link = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $firstName, $lastName, $socialLink, $id);
        $stmt->execute();

        echo json_encode([
            "status" => "success",
            "message" => "Narrator updated successfully"
        ]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Internal Server Error",
            "error" => $e->getMessage()
        ]);
    }
    exit();
}

// POST: /narrators.php
// Add a new narrator
// JSON body: {
    // "firstName": "Tom", 
    // "lastName": "Smith", 
    // "socialLink": "www.sample.com"
// }
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON"]);
        exit();
    }

    $firstName = $data['firstName'];
    $lastName = $data['lastName'];
    $socialLink = $data['socialLink'];

    try {
        $stmt = $connect->prepare("
            INSERT INTO narrators (first_name, last_name, social_link, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("sss", $firstName, $lastName, $socialLink);
        $stmt->execute();
        $book_id = $stmt->insert_id;

        echo json_encode([
            "status" => "success",
            "message" => "Narrator added successfully"
        ]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode([
            "status" => "error",
            "message" => "Internal Server Error",
            "error" => $e->getMessage()
        ]);
    }

    $response = [
        "status" => "success",
        "message" => "Data received",
        "data" => $data
    ];
    echo json_encode($data);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit();
}
?>