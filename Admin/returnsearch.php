<?php
include 'db.php'; // connect to your lms_db

$q = $_GET['q'] ?? '';

if (empty($q)) {
  echo json_encode([]);
  exit;
}

// 🔍 Search students by Student_ID_Number or Name
$sql = "
  SELECT 
    s.Student_ID AS student_id,
    s.Name AS name,
    s.Student_ID_Number AS student_id_no,
    s.Course AS course,
    s.Year_Level AS year_level
  FROM Students s
  WHERE s.Student_ID_Number LIKE '%$q%' OR s.Name LIKE '%$q%'
  LIMIT 10
";

$result = $conn->query($sql);
$students = [];

while ($row = $result->fetch_assoc()) {

  // 📚 Get borrowed books using Student_ID_Number
  $borrow_sql = "
    SELECT 
      b.Book_ID AS book_id,
      b.Title AS title,
      br.Borrow_Date AS borrow_date,
      br.Due_Date AS due_date,
      br.Status AS status
    FROM Borrow_Record br
    JOIN Book b ON br.Book_ID = b.Book_ID
    WHERE br.User_Type = 'student'
      AND br.Student_ID_Number = '{$row['student_id_no']}'
      AND br.Status = 'borrowed'
  ";

  $borrow_result = $conn->query($borrow_sql);
  $borrowed_books = [];

  while ($borrow_row = $borrow_result->fetch_assoc()) {
    $borrowed_books[] = $borrow_row;
  }

  $row['borrowed_books'] = $borrowed_books;
  $students[] = $row;
}

// 🧾 Return JSON data for JS
echo json_encode($students);
$conn->close();
?>
