<?php
include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? '';
    $student_id_no = $_POST['student_id_no'] ?? '';

    if (empty($book_id) || empty($student_id_no)) {
        echo "Missing book or student ID.";
        exit;
    }

    // 1️⃣ Fetch the current borrow record
    $query = $conn->prepare("SELECT Borrow_ID, Due_Date FROM borrow_record WHERE Book_ID = ? AND Student_ID_Number = ? AND Status = 'borrowed'");
    $query->bind_param("is", $book_id, $student_id_no);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 0) {
        echo "No borrowed record found for this book.";
        exit;
    }

    $record = $result->fetch_assoc();
    $borrow_id = $record['Borrow_ID'];
    $due_date = $record['Due_Date'];
    $return_date = date('Y-m-d');

    // 2️⃣ Calculate fine if overdue
    $fine = 0.00;
    if (strtotime($return_date) > strtotime($due_date)) {
        $days_late = (strtotime($return_date) - strtotime($due_date)) / (60 * 60 * 24);
        $fine = $days_late * 5; // 5 pesos per day
    }

    // 3️⃣ Update borrow record to returned
    $update = $conn->prepare("UPDATE borrow_record SET Return_Date = ?, Fine = ?, Status = 'returned' WHERE Borrow_ID = ?");
    $update->bind_param("sdi", $return_date, $fine, $borrow_id);
    $update->execute();

    // 4️⃣ Update book copies instead of 'Status'
    $updateBook = $conn->prepare("
        UPDATE book 
        SET Available_Copies = Available_Copies + 1,
            Borrowed_Copies = GREATEST(Borrowed_Copies - 1, 0)
        WHERE Book_ID = ?
    ");
    $updateBook->bind_param("i", $book_id);
    $updateBook->execute();

    // 5️⃣ 💡 Add points logic (max 3 returns/day, 0.5 points each)
    $dateToday = date('Y-m-d');
    $pointsPerReturn = 0.5;
    $maxDailyPoints = 1.5;


    // Ensure Total_Points column exists in students table
    $conn->query("ALTER TABLE students ADD COLUMN IF NOT EXISTS Total_Points DECIMAL(6,2) DEFAULT 0");

    // Check today's record
    $check = $conn->prepare("SELECT points_earned FROM student_points WHERE student_id_no = ? AND date = ?");
    $check->bind_param("ss", $student_id_no, $dateToday);
    $check->execute();
    $checkRes = $check->get_result();

    if ($checkRes->num_rows > 0) {
        $row = $checkRes->fetch_assoc();
        $currentPoints = $row['points_earned'];

        if ($currentPoints < $maxDailyPoints) {
            $newPoints = min($currentPoints + $pointsPerReturn, $maxDailyPoints);

            $updatePoints = $conn->prepare("UPDATE student_points SET points_earned = ? WHERE student_id_no = ? AND date = ?");
            $updatePoints->bind_param("dss", $newPoints, $student_id_no, $dateToday);
            $updatePoints->execute();

            $increment = $newPoints - $currentPoints;
            $updateTotal = $conn->prepare("UPDATE students SET Total_Points = Total_Points + ? WHERE Student_ID_Number = ?");
            $updateTotal->bind_param("ds", $increment, $student_id_no);
            $updateTotal->execute();
        }

    } else {
        // First return today → add new record
        $insertPoints = $conn->prepare("INSERT INTO student_points (student_id_no, date, points_earned) VALUES (?, ?, ?)");
        $insertPoints->bind_param("ssd", $student_id_no, $dateToday, $pointsPerReturn);
        $insertPoints->execute();

        $updateTotal = $conn->prepare("UPDATE students SET Total_Points = Total_Points + ? WHERE Student_ID_Number = ?");
        $updateTotal->bind_param("ds", $pointsPerReturn, $student_id_no);
        $updateTotal->execute();
    }

    echo "✅ Book returned successfully! Fine: ₱" . number_format($fine, 2);
}
?>
