<?php
include 'db.php';

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: text/html; charset=utf-8');
    $action = $_GET['action'] ?? '';

    // helper to escape search term safely for LIKE
    function esc_like($conn, $s) {
        return $conn->real_escape_string('%' . $s . '%');
    }

    if ($action === 'borrowed') {
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? ''; // borrowed / returned etc. optional
        // Query borrowed records for today
        $where = "br.Borrow_Date = CURDATE()";
        if ($status !== '') {
            // map friendly values if necessary
            $s = $conn->real_escape_string($status);
            // Your borrow_record.Status values may be 'borrowed','returned','overdue'
            // The UI uses 'Returned'/'Not Returned' earlier; try to handle both
            if (strtolower($s) === 'returned') {
                $where .= " AND (br.Return_Date IS NOT NULL OR br.Status = 'returned')";
            } elseif (strtolower($s) === 'not returned' || strtolower($s) === 'borrowed') {
                $where .= " AND (br.Return_Date IS NULL OR br.Status = 'borrowed')";
            } else {
                // fallback exact match
                $where .= " AND br.Status = '{$s}'";
            }
        }
        if ($search !== '') {
            $s = esc_like($conn, $search);
            // search in student/faculty name or book title
            $where .= " AND (s.Name LIKE '{$s}' OR f.Name LIKE '{$s}' OR b.Title LIKE '{$s}' OR br.User_ID LIKE '{$s}')";
        }

        $sql = "SELECT br.Borrow_ID, br.User_Type, br.Student_ID_Number, br.Borrow_Date, br.Return_Date, br.Status,
                       b.Title AS BookTitle,
                       s.Student_ID AS StudentPK, s.School_ID_Number AS StudentSchoolID, s.Name AS StudentName,
                       f.Faculty_ID AS FacultyPK, f.School_ID_Number AS FacultySchoolID, f.Name AS FacultyName
                FROM borrow_record br
                LEFT JOIN book b ON b.Book_ID = br.Book_ID
                LEFT JOIN students s ON (br.User_Type = 'student' AND s.Student_ID = br.Student_ID_Number)
                LEFT JOIN faculty f ON (br.User_Type = 'faculty' AND f.Faculty_ID = br.Student_ID_Number)
                WHERE {$where}
                ORDER BY br.Borrow_Date DESC
                LIMIT 1000";
        $res = $conn->query($sql);
        if (!$res) { echo "<tr><td colspan='4'>Query error.</td></tr>"; exit; }
        if ($res->num_rows === 0) {
            echo "<tr><td colspan='4'>No records found.</td></tr>";
            exit;
        }
        while ($row = $res->fetch_assoc()) {
            // Determine display ID and name
            if ($row['User_Type'] === 'student') {
                $dispId = htmlspecialchars($row['StudentSchoolID'] !== null ? $row['StudentSchoolID'] : $row['User_ID']);
                $dispName = htmlspecialchars($row['StudentName'] ?? '');
            } else {
                $dispId = htmlspecialchars($row['FacultySchoolID'] !== null ? $row['FacultySchoolID'] : $row['User_ID']);
                $dispName = htmlspecialchars($row['FacultyName'] ?? '');
            }
            $borrowDate = htmlspecialchars($row['Borrow_Date']);
            $statusText = htmlspecialchars($row['Return_Date'] === null ? ($row['Status'] ?? 'Not returned') : 'Returned');

            echo "<tr>";
            echo "<td>{$dispId}</td>";
            echo "<td>{$dispName}</td>";
            echo "<td>{$borrowDate}</td>";
            echo "<td>{$statusText}</td>";
            echo "</tr>";
        }
        exit;
    }

    if ($action === 'due') {
        $search = $_GET['search'] ?? '';
        $where = "br.Due_Date = CURDATE() AND (br.Return_Date IS NULL OR br.Return_Date = '0000-00-00')";
        if ($search !== '') {
            $s = esc_like($conn, $search);
            $where .= " AND (s.Name LIKE '{$s}' OR f.Name LIKE '{$s}' OR b.Title LIKE '{$s}' OR br.User_ID LIKE '{$s}')";
        }
        $sql = "SELECT br.Borrow_ID, br.User_Type, br.User_ID, br.Due_Date, b.Title AS BookTitle,
                       s.Student_ID AS StudentPK, s.School_ID_Number AS StudentSchoolID, s.Name AS StudentName,
                       f.Faculty_ID AS FacultyPK, f.School_ID_Number AS FacultySchoolID, f.Name AS FacultyName
                FROM borrow_record br
                LEFT JOIN book b ON b.Book_ID = br.Book_ID
                LEFT JOIN students s ON (br.User_Type = 'student' AND s.Student_ID = br.User_ID)
                LEFT JOIN faculty f ON (br.User_Type = 'faculty' AND f.Faculty_ID = br.User_ID)
                WHERE {$where}
                ORDER BY br.Due_Date ASC
                LIMIT 1000";
        $res = $conn->query($sql);
        if (!$res) { echo "<tr><td colspan='5'>Query error.</td></tr>"; exit; }
        if ($res->num_rows === 0) {
            echo "<tr><td colspan='5'>No records found.</td></tr>";
            exit;
        }
        while ($row = $res->fetch_assoc()) {
            if ($row['User_Type'] === 'student') {
                $dispId = htmlspecialchars($row['StudentSchoolID'] !== null ? $row['StudentSchoolID'] : $row['User_ID']);
                $dispName = htmlspecialchars($row['StudentName'] ?? '');
            } else {
                $dispId = htmlspecialchars($row['FacultySchoolID'] !== null ? $row['FacultySchoolID'] : $row['User_ID']);
                $dispName = htmlspecialchars($row['FacultyName'] ?? '');
            }
            $bookTitle = htmlspecialchars($row['BookTitle'] ?? '');
            $dueDate = htmlspecialchars($row['Due_Date']);
            // Status: if Return_Date not null => Returned else Not returned
            // We didn't select Return_Date here; show blank or 'Not returned' per where clause
            $statusText = 'Not returned';

            echo "<tr>";
            echo "<td>{$dispId}</td>";
            echo "<td>{$dispName}</td>";
            echo "<td>{$bookTitle}</td>";
            echo "<td>{$dueDate}</td>";
            echo "<td>{$statusText}</td>";
            echo "</tr>";
        }
        exit;
    }

    if ($action === 'overdue') {
        $search = $_GET['search'] ?? '';
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';
        $where = "br.Due_Date < CURDATE() AND (br.Return_Date IS NULL OR br.Return_Date = '0000-00-00')";
        if (!empty($from)) {
            $df = $conn->real_escape_string($from);
            $where .= " AND br.Due_Date >= '{$df}'";
        }
        if (!empty($to)) {
            $dt = $conn->real_escape_string($to);
            $where .= " AND br.Due_Date <= '{$dt}'";
        }
        if ($search !== '') {
            $s = esc_like($conn, $search);
            $where .= " AND (s.Name LIKE '{$s}' OR f.Name LIKE '{$s}' OR b.Title LIKE '{$s}' OR br.User_ID LIKE '{$s}')";
        }

        $sql = "SELECT br.Borrow_ID, br.User_Type, br.User_ID, br.Due_Date, b.Title AS BookTitle,
                       DATEDIFF(CURDATE(), br.Due_Date) AS days_overdue,
                       s.Student_ID AS StudentPK, s.School_ID_Number AS StudentSchoolID, s.Name AS StudentName,
                       f.Faculty_ID AS FacultyPK, f.School_ID_Number AS FacultySchoolID, f.Name AS FacultyName
                FROM borrow_record br
                LEFT JOIN book b ON b.Book_ID = br.Book_ID
                LEFT JOIN students s ON (br.User_Type = 'student' AND s.Student_ID = br.User_ID)
                LEFT JOIN faculty f ON (br.User_Type = 'faculty' AND f.Faculty_ID = br.User_ID)
                WHERE {$where}
                ORDER BY br.Due_Date ASC
                LIMIT 2000";
        $res = $conn->query($sql);
        if (!$res) { echo "<tr><td colspan='5'>Query error.</td></tr>"; exit; }
        if ($res->num_rows === 0) {
            echo "<tr><td colspan='5'>No records found.</td></tr>";
            exit;
        }
        while ($row = $res->fetch_assoc()) {
            if ($row['User_Type'] === 'student') {
                $dispId = htmlspecialchars($row['StudentSchoolID'] !== null ? $row['StudentSchoolID'] : $row['User_ID']);
                $dispName = htmlspecialchars($row['StudentName'] ?? '');
            } else {
                $dispId = htmlspecialchars($row['FacultySchoolID'] !== null ? $row['FacultySchoolID'] : $row['User_ID']);
                $dispName = htmlspecialchars($row['FacultyName'] ?? '');
            }
            $bookTitle = htmlspecialchars($row['BookTitle'] ?? '');
            $dueDate = htmlspecialchars($row['Due_Date']);
            $days = htmlspecialchars($row['days_overdue']);

            echo "<tr>";
            echo "<td>{$dispId}</td>";
            echo "<td>{$dispName}</td>";
            echo "<td>{$bookTitle}</td>";
            echo "<td>{$dueDate}</td>";
            echo "<td>{$days}</td>";
            echo "</tr>";
        }
        exit;
    }

    if ($action === 'active') {
        // You said you don't have an active students table yet.
        // We'll return a placeholder row and include the sample query commented
        echo "<tr><td colspan='5'>Active students not set up yet. Add a `library_logs` or `student_logs` table to track time_in/time_out.</td></tr>";
        /*
        // Example query once you add `library_logs`:
        $sql = \"SELECT st.Student_ID, st.Name, st.Course, st.Year_Level, ll.time_in
                FROM library_logs ll
                JOIN students st ON st.Student_ID = ll.Student_ID
                WHERE ll.time_out IS NULL\";
        */
        exit;
    }

    // Unknown action
    echo "<tr><td colspan='5'>Unknown action.</td></tr>";
    exit;
}
// end AJAX handlers
// -----------------------------


// -----------------------------
// Non-AJAX: Render HTML page (dashboard)
// -----------------------------

// Get counts for cards (server-side so cards show immediately)
$today = date('Y-m-d');

// borrowed today
$q = "SELECT COUNT(*) AS c FROM borrow_record WHERE Borrow_Date = CURDATE()";
$borrowedCount = ($res = $conn->query($q)) ? (int)$res->fetch_assoc()['c'] : 0;

// due today (not returned)
$q = "SELECT COUNT(*) AS c FROM borrow_record WHERE Due_Date = CURDATE() AND (Return_Date IS NULL OR Return_Date = '0000-00-00')";
$dueCount = ($res = $conn->query($q)) ? (int)$res->fetch_assoc()['c'] : 0;

// overdue (not returned)
$q = "SELECT COUNT(*) AS c FROM borrow_record WHERE Due_Date < CURDATE() AND (Return_Date IS NULL OR Return_Date = '0000-00-00')";
$overdueCount = ($res = $conn->query($q)) ? (int)$res->fetch_assoc()['c'] : 0;

// active students - placeholder (commented since you don't have logs table yet)
//$q = "SELECT COUNT(DISTINCT Student_ID) AS c FROM library_logs WHERE time_out IS NULL";
//$activeCount = ($res = $conn->query($q)) ? (int)$res->fetch_assoc()['c'] : 0;
$activeCount = 0; // placeholder

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Library Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Load your dashboard.css externally (kept separate as requested) -->
  <link rel="stylesheet" href="dashboard.css">

  <style>
    /* modal width class (no !important) */
    .modal-dialog.modal-xl-custom {
      max-width: 1200px;
    }
    /* ensure modal content scrolls nicely */
    .modal-body { max-height: 70vh; overflow-y: auto; }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>
  <?php include 'sidebar.php'; ?>

  <main class="main-content">
    <div class="container py-4">
      <h2 class="mb-3">Library Dashboard</h2>

      <div class="dashboard-cards">
        <div class="card card-click dashboard-card text-center" data-bs-toggle="modal" data-bs-target="#modalBorrowed">
          <div class="card-body">
            <div>
              <div class="card-icon">📚</div>
              <h5 class="card-title">Borrowed Books Today</h5>
              <p class="h3" id="cnt-borrowed"><?= $borrowedCount ?></p>
              <p class="small-muted">Total borrowed today</p>
            </div>
          </div>
        </div>

        <div class="card card-click dashboard-card text-center" data-bs-toggle="modal" data-bs-target="#modalDue">
          <div class="card-body">
            <div>
              <div class="card-icon">⏰</div>
              <h5 class="card-title">Due Today</h5>
              <p class="h3" id="cnt-due"><?= $dueCount ?></p>
              <p class="small-muted">Books due today</p>
            </div>
          </div>
        </div>

        <div class="card card-click dashboard-card text-center" data-bs-toggle="modal" data-bs-target="#modalOverdue">
          <div class="card-body">
            <div>
              <div class="card-icon">⚠️</div>
              <h5 class="card-title">Overdue</h5>
              <p class="h3" id="cnt-overdue"><?= $overdueCount ?></p>
              <p class="small-muted">Books past due</p>
            </div>
          </div>
        </div>

        <div class="card card-click dashboard-card text-center" data-bs-toggle="modal" data-bs-target="#modalActive">
          <div class="card-body">
            <div>
              <div class="card-icon">👥</div>
              <h5 class="card-title">Active Students</h5>
              <p class="h3" id="cnt-active"><?= $activeCount ?></p>
              <p class="small-muted">Currently inside library</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Borrowed Modal -->
      <div class="modal fade" id="modalBorrowed" tabindex="-1">
        <div class="modal-dialog modal-xl-custom modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Borrowed Books Today</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="d-flex mb-3 gap-2">
                <input type="text" id="searchBorrowed" class="form-control" placeholder="Search student id or name or book">
                <button class="btn btn-primary" id="btnSearchBorrowed"><i class="fas fa-search"></i></button>
                <select id="filterBorrowedStatus" class="form-select w-auto">
                  <option value="">All</option>
                  <option value="returned">Returned</option>
                  <option value="borrowed">Not Returned</option>
                </select>
              </div>
              <div class="table-responsive">
                <table class="table table-striped" id="tbl-borrowed">
                  <thead>
                    <tr>
                      <th>Student ID</th><th>Name</th><th>Borrow Date</th><th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="borrowed-body">
                    <!-- loaded by AJAX -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Due Modal -->
      <div class="modal fade" id="modalDue" tabindex="-1">
        <div class="modal-dialog modal-xl-custom modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Due Today</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="d-flex mb-3 gap-2">
                <input type="text" id="searchDue" class="form-control" placeholder="Search student id, name or book">
                <button class="btn btn-primary" id="btnSearchDue"><i class="fas fa-search"></i></button>
              </div>
              <div class="table-responsive">
                <table class="table table-striped" id="tbl-due">
                  <thead>
                    <tr>
                      <th>Student ID</th><th>Name</th><th>Book Title</th><th>Due Date</th><th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="due-body">
                    <!-- loaded by AJAX -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Overdue Modal -->
      <div class="modal fade" id="modalOverdue" tabindex="-1">
        <div class="modal-dialog modal-xl-custom modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Overdue Books</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="d-flex mb-3 gap-2">
                <input type="text" id="searchOverdue" class="form-control" placeholder="Search student id, name or book">
                <input type="date" id="fromOverdue" class="form-control w-auto">
                <input type="date" id="toOverdue" class="form-control w-auto">
                <button class="btn btn-primary" id="btnFilterOverdue"><i class="fas fa-filter"></i> Filter</button>
              </div>
              <div class="table-responsive">
                <table class="table table-striped" id="tbl-overdue">
                  <thead>
                    <tr>
                      <th>Student ID</th><th>Name</th><th>Book Title</th><th>Due Date</th><th>Days Overdue</th>
                    </tr>
                  </thead>
                  <tbody id="overdue-body">
                    <!-- loaded by AJAX -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Modal (placeholder) -->
      <div class="modal fade" id="modalActive" tabindex="-1">
        <div class="modal-dialog modal-xl-custom modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Active Students (placeholder)</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="d-flex mb-3 gap-2">
                <input type="text" id="searchActive" class="form-control" placeholder="Search student id, name or book">
                <button class="btn btn-primary" id="btnSearchActive"><i class="fas fa-search"></i></button>
                <select id="filterProgram" class="form-select w-auto">
                  <option value="">All Programs</option>
                </select>
                <select id="filterYear" class="form-select w-auto">
                  <option value="">All Years</option>
                </select>
              </div>
              <div class="table-responsive">
                <table class="table table-striped" id="tbl-active">
                  <thead>
                    <tr>
                      <th>Student ID</th><th>Name</th><th>Program</th><th>Year</th><th>Time Entered</th>
                    </tr>
                  </thead>
                  <tbody id="active-body">
                    <tr><td colspan="5">Active students feature not set up yet.</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <!-- scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  $(function(){
    // When modal shown -> load corresponding data automatically
    $('#modalBorrowed').on('shown.bs.modal', function(){ loadBorrowed(); });
    $('#modalDue').on('shown.bs.modal', function(){ loadDue(); });
    $('#modalOverdue').on('shown.bs.modal', function(){ loadOverdue(); });
    $('#modalActive').on('shown.bs.modal', function(){ /* active placeholder */ });

    // Search triggers
    $('#btnSearchBorrowed').on('click', loadBorrowed);
    $('#searchBorrowed').on('keypress', function(e){ if(e.key === 'Enter') loadBorrowed(); });

    $('#btnSearchDue').on('click', loadDue);
    $('#searchDue').on('keypress', function(e){ if(e.key === 'Enter') loadDue(); });

    $('#btnFilterOverdue').on('click', loadOverdue);
    $('#searchOverdue').on('keypress', function(e){ if(e.key === 'Enter') loadOverdue(); });

    $('#btnSearchActive').on('click', function(){ /* placeholder */ });
    $('#searchActive').on('keypress', function(e){ if(e.key === 'Enter') {/* placeholder */} });

    function loadBorrowed(){
      const q = $('#searchBorrowed').val();
      const s = $('#filterBorrowedStatus').val();
      $('#borrowed-body').html('<tr><td colspan="4">Loading...</td></tr>');
      $.get('?ajax=1&action=borrowed', { search: q, status: s }, function(html){
        $('#borrowed-body').html(html);
      });
    }

    function loadDue(){
      const q = $('#searchDue').val();
      $('#due-body').html('<tr><td colspan="5">Loading...</td></tr>');
      $.get('?ajax=1&action=due', { search: q }, function(html){
        $('#due-body').html(html);
      });
    }

    function loadOverdue(){
      const q = $('#searchOverdue').val();
      const from = $('#fromOverdue').val();
      const to = $('#toOverdue').val();
      $('#overdue-body').html('<tr><td colspan="5">Loading...</td></tr>');
      $.get('?ajax=1&action=overdue', { search: q, from: from, to: to }, function(html){
        $('#overdue-body').html(html);
      });
    }

    // Optional: refresh counts every 60s
    setInterval(function(){
      $.get('?counts=1', function(json){
        try {
          const data = JSON.parse(json);
          $('#cnt-borrowed').text(data.borrowed);
          $('#cnt-due').text(data.due);
          $('#cnt-overdue').text(data.overdue);
          $('#cnt-active').text(data.active);
        } catch(e){ /* ignore parse errors */ }
      });
    }, 60*1000);
  });
  </script>

<?php
// Optional: counts endpoint used by JS refresh
if (isset($_GET['counts']) && $_GET['counts'] == '1') {
    header('Content-Type: application/json; charset=utf-8');
    $q = "SELECT COUNT(*) AS c FROM borrow_record WHERE Borrow_Date = CURDATE()";
    $borrowed = ($r=$conn->query($q)) ? (int)$r->fetch_assoc()['c'] : 0;
    $q = "SELECT COUNT(*) AS c FROM borrow_record WHERE Due_Date = CURDATE() AND (Return_Date IS NULL OR Return_Date = '0000-00-00')";
    $due = ($r=$conn->query($q)) ? (int)$r->fetch_assoc()['c'] : 0;
    $q = "SELECT COUNT(*) AS c FROM borrow_record WHERE Due_Date < CURDATE() AND (Return_Date IS NULL OR Return_Date = '0000-00-00')";
    $overdue = ($r=$conn->query($q)) ? (int)$r->fetch_assoc()['c'] : 0;
    // active placeholder
    $active = 0;
    echo json_encode(['borrowed'=>$borrowed,'due'=>$due,'overdue'=>$overdue,'active'=>$active]);
    exit;
}
?>

</body>
</html>
