<?php
include 'db.php';
include 'session_auth.php';
include 'header.php';
include 'sidebar.php';

$school_id = trim($_GET['school_id'] ?? '');
$type = $_GET['type'] ?? 'all';
$sort = $_GET['sort'] ?? '';

// Build base SQL for transactions
$sql = "SELECT 
            br.Borrow_ID,
            s.Student_ID_Number AS School_ID,
            b.Title AS Book_Title,
            br.Status AS status_raw,
            CASE
  WHEN br.Status = 'borrowed' AND br.Due_Date < CURDATE() THEN 'BORROWED/OVERDUE'
  WHEN br.Status = 'returned' AND br.Return_Date > br.Due_Date THEN 'OVERDUE/RETURNED'
  WHEN br.Status = 'borrowed' THEN 'BORROWED'
  WHEN br.Status = 'returned' THEN 'RETURNED'
  ELSE UPPER(br.Status)
END AS Transaction_Type
,
            COALESCE(br.Return_Date, br.Borrow_Date) AS Transaction_Date,
            br.Borrow_Date AS Borrow_Date,
            br.Due_Date AS Due_Date,
            br.Return_Date AS Return_Date,
            COALESCE(br.Fine, 0) AS Fine
        FROM borrow_record br
        JOIN book b ON br.Book_ID = b.Book_ID
        LEFT JOIN students s ON br.Student_ID_Number = s.Student_ID_Number
        WHERE 1=1";

// Dynamic parameters
$params = [];
$types = "";

// Filter by school ID
if ($school_id !== '') {
  $sql .= " AND s.Student_ID_Number LIKE ?";
  $params[] = "%{$school_id}%";
  $types .= "s";
}

// Filter by type
if ($type !== 'all') {
    if ($type === 'overdue') {
        // Only show borrowed books that are past due
        $sql .= " AND br.Status = 'borrowed' AND br.Due_Date < CURDATE()";
    } else {
        $sql .= " AND br.Status = ?";
        $params[] = $type;
        $types .= "s";
    }
}



// Filter by specific date (borrow or return date)
$filter_date = $_GET['filter_date'] ?? '';
if (!empty($filter_date)) {
  $sql .= " AND DATE(COALESCE(br.Return_Date, br.Borrow_Date)) = ?";
  $params[] = $filter_date;
  $types .= "s";
}

// Sorting
if ($sort === 'date') {
  $sql .= " ORDER BY COALESCE(br.Return_Date, br.Borrow_Date) DESC";
} else {
  $sql .= " ORDER BY br.Borrow_Date DESC, br.Borrow_ID DESC";
}

// Prepare statement
$stmt = $conn->prepare($sql);
if ($stmt !== false) {
  if (!empty($params)) {
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
      $bind_names[] = &$params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
  }
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  die("Query prepare failed: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Book Transaction</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
  <style>
    body {
      background: #F4F7FB;
    }

    .main-header {
      margin-top: 0;
      background: #e9eef6;
      text-align: center;
      padding: 30px;
      font-weight: 700;
      margin-bottom: 15px;
      font-size: 1.5rem;
    }

    .table th,
    .table td {
      vertical-align: middle;
      color: #232D3F;
    }

    .table thead {
      background: #EDF0F6 !important;
    }

    .table th {
      font-weight: 700;
      white-space: nowrap;
    }

    .table-responsive {
      min-height: 350px;
      overflow-x: auto;
    }

    .btn-outline-secondary,
    .form-select {
      border-radius: 8px;
    }

    input[disabled] {
      background: #EDF0F6 !important;
      color: #232D3F;
      font-weight: 500;
    }


  </style>
</head>

<body>
  <main class="main-content p-4">
  <div class="main-header">BOOK TRANSACTION</div>

  <div class="container m-auto">
    <div class="row">
      <div class="col-md-12">
        <div class="table-responsive bg-white rounded-3 p-4 shadow-sm">

          <!-- Filter Form -->
          <form method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-12 col-sm-6 col-md-4">
              <label for="schoolId" class="form-label fw-semibold mb-1">SCHOOL ID NO.</label>
              <div class="position-relative">
  <input type="text" class="form-control w-100 pe-5" id="schoolId" name="school_id" placeholder="2022-1234-56" value="<?= htmlspecialchars($_GET['school_id'] ?? '') ?>">
  <button type="submit" class="btn position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent" style="right: 10px;">
    <i class="fa-solid fa-magnifying-glass text-secondary"></i>
  </button>
</div>

            </div>

<div class="col-12 col-sm-6 col-md-3">
  <label for="filterDate" class="form-label fw-semibold mb-1">TRANSACTION DATE</label>
  <div class="input-group">
    <input type="date" class="form-control" id="filterDate" name="filter_date" 
           value="<?= htmlspecialchars($_GET['filter_date'] ?? '') ?>">
    
  </div>
</div>


            <div class="col-12 col-sm-6 col-md-3">
              <label for="requestType" class="form-label fw-semibold mb-1">REQUEST TYPE</label>
              <select class="form-select w-100" id="requestType" name="type">
                <option value="all" <?= ($type === 'all') ? 'selected' : '' ?>>All</option>
                <option value="borrowed" <?= ($type === 'borrowed') ? 'selected' : '' ?>>Borrow</option>
                <option value="returned" <?= ($type === 'returned') ? 'selected' : '' ?>>Return</option>
                <option value="overdue" <?= ($type === 'overdue') ? 'selected' : '' ?>>Overdue</option>
              </select>
            </div>
          </form>

          <!-- Table -->
          <table class="table table-borderless align-middle mb-0" id="transactionTable">
            <thead>
              <tr class="fw-bold" style="color:#232D3F;">
                <th>SCHOOL ID</th>
                <th>BOOK TITLE</th>
                <th>TRANSACTION</th>
                <th>DATE OF TRANSACTION</th>
                <th>FINE FOR OVERDUE BOOKS</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                  $displayDate = $row['Transaction_Date'] ? date("m/d/Y", strtotime($row['Transaction_Date'])) : '-';
                  // Calculate fine
// Calculate fine
$ratePerDay = 5; // PHP per day
$daysLate = 0;


$dueDate = new DateTime($row['Due_Date']);
$returnDate = !empty($row['Return_Date']) ? new DateTime($row['Return_Date']) : new DateTime(); // current date if not returned

// Only count fine if returned AFTER due date
if ($returnDate > $dueDate) {
    $daysLate = $dueDate->diff($returnDate)->days;
    $fineAmount = $daysLate * $ratePerDay;
} else {
    $fineAmount = 0; // No fine if on time or early
}


// Update database if Fine is different
if ($fineAmount != $row['Fine']) {
    $stmtUpdate = $conn->prepare("UPDATE Borrow_Record SET Fine = ? WHERE Borrow_ID = ?");
    $stmtUpdate->bind_param("di", $fineAmount, $row['Borrow_ID']);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}


$fineFormatted = number_format($daysLate * $ratePerDay, 2) . " php";


                  $txType = strtoupper($row['Transaction_Type']);
                  echo "<tr>
                      <td>" . htmlspecialchars($row['School_ID'] ?? '-') . "</td>
                      <td>" . htmlspecialchars($row['Book_Title'] ?? '-') . "</td>
                      <td class='text-uppercase'>{$txType}</td>
                      <td>{$displayDate}</td>
                      <td>{$fineFormatted}</td>
                    </tr>";
                }
              } else {
                echo "<tr><td colspan='5' class='text-center text-muted'>No transaction records found.</td></tr>";
              }
              $stmt->close();
              ?>
            </tbody>
          </table>

        </div>
      </div>
    </div>
  </div>
</main>
  <script>
  const searchInput = document.getElementById('schoolId');

  // When search bar becomes empty
  searchInput.addEventListener('input', function() {
    if (this.value.trim() === '') {
      // Reload the page to show the default table
      window.location.href = window.location.pathname;
    }
  });
</script>


  <script>
  document.getElementById('filterDate').addEventListener('change', function() {
    this.form.submit();
  });
</script>


  <script>
  document.getElementById('requestType').addEventListener('change', function() {
    this.form.submit();
  });
</script>

</body>

</html>