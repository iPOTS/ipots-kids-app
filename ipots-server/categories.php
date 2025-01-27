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

// Create categories table
try {
    $createCategoriesTable = "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    // Return error if creation fails
    if (!$connect->query($createCategoriesTable)) {
        throw new Exception("Table creation failed for categories: " . $connect->error);
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

    // GET: /categories.php/:id
    // Get a single category entry
    if (is_numeric($id)) {
        $stmt = $connect->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();

        if (!$category) {
            http_response_code(404);
            echo json_encode(["error" => "Category not found"]);
            exit();
        }

        echo json_encode($category);
        exit();
    }
    else {

        // GET: /categories.php
        // Get all categories
        $stmt = $connect->prepare("SELECT * FROM categories");
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);

        if (!$categories) {
            http_response_code(404);
            echo json_encode(["error" => "No categories found"]);
            exit();
        }

        echo json_encode($categories);
        exit();
    }
}

// DELETE: /categories.php/:id
// Delete a category
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid id parameter"]);
        exit();
    }

    try {
        $stmt = $connect->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode([
            "status" => "success",
            "message" => "Category deleted successfully"
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

// POST: /categories.php/update/:id
// Update an existing category
// JSON body:{ 
    // "name": "Category name" 
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

    $name = $data['name'];

    try {
        $stmt = $connect->prepare("
            UPDATE categories
            SET name = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();

        echo json_encode([
            "status" => "success",
            "message" => "Category updated successfully"
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


// POST: /categories.php
// Add a new category
// JSON body: {
    // "name": "Category Name"
// }
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON"]);
        exit();
    }

    $name = $data['name'];

    try {
        $stmt = $connect->prepare("
            INSERT INTO categories (name, created_at, updated_at)
            VALUES (?, NOW(), NOW())
        ");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $book_id = $stmt->insert_id;

        echo json_encode([
            "status" => "success",
            "message" => "Category added successfully"
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