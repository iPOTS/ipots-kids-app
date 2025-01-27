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

// Create books and pages tables
try {
    $createBooksTable = "
        CREATE TABLE IF NOT EXISTS books (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            audio_url VARCHAR(255) NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            category_id INT NOT NULL,
            narrator_id INT NOT NULL,
            illustrator_id INT NOT NULL,
            page_count INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )
    ";

    $createPagesTable = "
        CREATE TABLE IF NOT EXISTS pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            text VARCHAR(255) NOT NULL,
            book_id INT NOT NULL,
            start_time VARCHAR(255) NOT NULL,
            end_time VARCHAR(255) NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            page_number INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books(id)
        )
    ";

    // Return error if creation fails

    if (!$connect->query($createBooksTable)) {
        throw new Exception("Table creation failed for books: " . $connect->error);
    }
    if (!$connect->query($createPagesTable)) {
        throw new Exception("Table creation failed for pages: " . $connect->error);
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

    // GET: /audiobooks.php/:id
    // Get a single book entry
    if (is_numeric($id)) {
        $stmt = $connect->prepare("SELECT * FROM books WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();

        if (!$book) {
            http_response_code(404);
            echo json_encode(["error" => "Book not found"]);
            exit();
        }

        $stmt = $connect->prepare("SELECT * FROM pages WHERE book_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pages = $result->fetch_all(MYSQLI_ASSOC);

        $book['pages'] = $pages;
        echo json_encode($book);
        exit();

        // GET: /audiobooks.php
        // Get all audiobooks
    } else {
        $stmt = $connect->prepare("SELECT * FROM books");
        $stmt->execute();
        $result = $stmt->get_result();
        $books = $result->fetch_all(MYSQLI_ASSOC);

        if (!$books) {
            http_response_code(404);
            echo json_encode(["error" => "No books found"]);
            exit();
        }

        echo json_encode($books);
        exit();
    }
}

// DELETE: /audiobooks.php/:id
// Delete a book entry
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid id parameter"]);
        exit();
    }

    try {
        // Start transaction
        $connect->begin_transaction();

        // Delete related pages
        $stmt = $connect->prepare("DELETE FROM pages WHERE book_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Delete book
        $stmt = $connect->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Commit transaction
        $connect->commit();

        echo json_encode([
            "status" => "success",
            "message" => "Book and related pages deleted successfully"
        ]);
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $connect->rollback();
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Internal Server Error",
            "error" => $e->getMessage()
        ]);
    }
}

// POST: /audiobooks.php/update/:id
// Update an existing book
// JSON body: { 
    // "title": "Book title", 
    // "audioUrl": "Audio URL", 
    // "imageUrl": "Image URL", 
    // "category": 1, 
    // "narrator": 1,
    // "illustrator": 1, 
    // "animal: 1,
    // "pageCount": 1
//}
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

    $title = $data['title'];
    $audioUrl = $data['audioUrl'];
    $imageUrl = $data['imageUrl'];
    $categoryId = $data['category'];
    $narratorId = $data['narrator'];
    $illustratorId = $data['illustrator'];
    $pageCount = $data['pageCount'];

    try {
        $stmt = $connect->prepare("
            UPDATE books
            SET title = ?, audio_url = ?, image_url = ?, category_id = ?, narrator_id = ?, illustrator_id = ?, page_count = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("sssiiiii", $title, $audioUrl, $imageUrl, $categoryId, $narratorId, $illustratorId, $pageCount, $id);
        $stmt->execute();

        echo json_encode([
            "status" => "success",
            "message" => "Book updated successfully"
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

// POST: /audiobooks.php
// Add a new book entry
// JSON body: { 
    // "title": "Book title", 
    // "audioUrl": "Audio URL", 
    // "imageUrl": "Image URL", 
    // "category": 1, 
    // "narrator": 1,
    // "illustrator": 1, 
    // "animal: 1,
    // "pageCount": 1,
    // "pages": [ 
    //      { "text": "Page text", 
    //        "start_time": "00:00:00", 
    //        "end_time": "00:00:10", 
    //        "image_url": "Image URL", 
    //        "page_number": 1 } 
    //      ] 
//}
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON"]);
        exit();
    }

    $bookTitle = $data['title'];
    $bookNarrator = $data['narrator'];
    $bookIllustrator = $data['illustrator']; 
    $bookCategory = $data['category'];
    $bookAudioUrl = $data['audioUrl'];
    $bookImageUrl = $data['imageUrl'];
    $bookPages = $data['pages'];
    $bookPageCount = 1;

    try {
        // Insert book data
        $stmt = $connect->prepare("
            INSERT INTO books (title, narrator_id, illustrator_id, category_id,  audio_url, image_url, page_count, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("siiissi", $bookTitle, $bookNarrator, $bookIllustrator, $bookCategory, $bookAudioUrl, $bookImageUrl, $bookPageCount);
        $stmt->execute();
        $book_id = $stmt->insert_id;

        // Insert pages data
        $stmt = $connect->prepare("
            INSERT INTO pages (book_id, text, page_number, image_url, start_time, end_time, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        foreach ($bookPages as $page) {
            
            $stmt->bind_param("isisii", $book_id, $page['text'], $page['pageNumber'], $page['imageUrl'], $page['startTime'], $page['endTime']);
            $stmt->execute();
        }

        echo json_encode([
            "status" => "success",
            "message" => "Book and pages added successfully"
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