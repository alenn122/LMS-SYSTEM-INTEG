<?php
include 'db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

// Validate input
if (!isset($input['student_id'], $input['borrow_date'], $input['due_date'], $input['books']) || !is_array($input['books']) || count($input['books']) === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

// ✅ 1. Remove intval() to keep full ID (with dashes)
$studentId = $conn->real_escape_string($input['student_id']);
$borrowDate = $conn->real_escape_string($input['borrow_date']);
$dueDate = $conn->real_escape_string($input['due_date']);
$books = $input['books'];

// Insert borrow records
$success = true;
$conn->begin_transaction();

try {
    foreach ($books as $bookId) {
        $bookId = intval($bookId);

        // ✅ 2. Use "siss" (string, int, string, string)
        $stmt = $conn->prepare("INSERT INTO Borrow_Record (User_Type, Student_ID_Number, Book_ID, Borrow_Date, Due_Date) VALUES ('student', ?, ?, ?, ?)");
        $stmt->bind_param("siss", $studentId, $bookId, $borrowDate, $dueDate);
        $stmt->execute();
        $stmt->close();

        // Update Book table
        $stmt2 = $conn->prepare("UPDATE Book SET Borrowed_Copies = Borrowed_Copies + 1, Available_Copies = Available_Copies - 1 WHERE Book_ID = ?");
        $stmt2->bind_param("i", $bookId);
        $stmt2->execute();
        $stmt2->close();
    }
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $success = false;
    $message = $e->getMessage();
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $message ?? 'Failed to save records.']);
}
?>
