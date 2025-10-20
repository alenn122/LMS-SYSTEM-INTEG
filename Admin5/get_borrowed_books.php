<?php
include 'db.php';
header('Content-Type: application/json');

$student_id = $_GET['student_id'] ?? '';

if (empty($student_id)) {
    echo json_encode(["error" => "Missing student ID"]);
    exit;
}

$sql = "
    SELECT 
        br.borrow_id,
        br.book_id,
        b.title,
        br.borrow_date,
        br.due_date,
        br.return_date,
        br.status,
        br.fine
    FROM borrow_record br
    INNER JOIN book b ON br.book_id = b.book_id
    WHERE br.user_id = '$student_id' AND br.status != 'returned'
";

$result = $conn->query($sql);
$records = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

echo json_encode($records);
$conn->close();
?>
