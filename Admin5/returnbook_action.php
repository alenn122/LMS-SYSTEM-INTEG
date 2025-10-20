<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? '';
    $student_id_no = $_POST['student_id_no'] ?? '';

    if (empty($book_id) || empty($student_id_no)) {
        echo "Missing data. Please try again.";
        exit;
    }

    // Check if this record exists and is currently borrowed
    $check_sql = "
        SELECT Borrow_ID FROM Borrow_Record
        WHERE Book_ID = '$book_id'
        AND Student_ID_Number = '$student_id_no'
        AND Status = 'borrowed'
        LIMIT 1
    ";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows === 0) {
        echo "No active borrowed record found.";
        exit;
    }

    $borrow = $check_result->fetch_assoc();
    $borrow_id = $borrow['Borrow_ID'];

    // Update Borrow_Record to mark as returned
    $update_sql = "
        UPDATE Borrow_Record 
        SET 
            Status = 'returned',
            Return_Date = NOW()
        WHERE Borrow_ID = '$borrow_id'
    ";

    if ($conn->query($update_sql)) {
        // Update Book copies: add 1 to Available, subtract 1 from Borrowed
        $book_update_sql = "
            UPDATE Book 
            SET 
                Available_Copies = Available_Copies + 1,
                Borrowed_Copies = Borrowed_Copies - 1
            WHERE Book_ID = '$book_id'
        ";
        $conn->query($book_update_sql);

        // Award points if returned on or before the due date
// Check if returned on or before due date
// Get the due date for this borrow record
$check_due_sql = "
    SELECT Due_Date 
    FROM Borrow_Record 
    WHERE Borrow_ID = '$borrow_id'
    LIMIT 1
";
$due_result = $conn->query($check_due_sql);
$due_row = $due_result->fetch_assoc();
$due_date = $due_row['Due_Date'];

$current_date = date('Y-m-d');
$points_earned = 0;

// Determine if on time or late
if ($current_date <= $due_date) {
    $points_earned = 5; // returned on time
}

// Insert into Student_Points even if 0
$insert_points_sql = "
    INSERT INTO Student_Points (Student_ID_Number, Date, Points_Earned)
    VALUES ('$student_id_no', NOW(), '$points_earned')
";
$conn->query($insert_points_sql);

// Recalculate
$total_sql = "
    SELECT SUM(Points_Earned) AS Total
    FROM Student_Points
    WHERE Student_ID_Number = '$student_id_no'
";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_points = $total_row['Total'] ?? 0;

// Update Table
$update_points_sql = "
    UPDATE Students
    SET Total_Points = '$total_points'
    WHERE Student_ID_Number = '$student_id_no'
";
$conn->query($update_points_sql);


if ($points_earned > 0) {
    echo "Book returned on time! +$points_earned points earned.";
} else {
    echo "Book returned late. No points awarded.";
}




        echo " Book successfully returned!";
    } else {
        echo " Error updating record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
