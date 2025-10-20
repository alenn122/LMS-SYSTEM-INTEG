<?php
include 'session_auth.php';
include('header.php');
include('sidebar.php');
include('db.php');

// Enable mysqli error reporting (optional but helps debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Fetch activity logs (points per student per date)
$sql = "
SELECT 
    s.Student_ID_Number,
    s.Name,
    s.Course,
    s.Year_Level,
    COALESCE(MAX(sp.Date), NULL) AS Last_Activity_Date,
    COALESCE(
        (
            SELECT sp2.Points_Earned
            FROM Student_Points sp2
            WHERE sp2.Student_ID_Number = s.Student_ID_Number
            ORDER BY sp2.Date DESC
            LIMIT 1
        ), 
        0
    ) AS Points_Today,
    COALESCE(SUM(sp.Points_Earned), 0) AS Total_Points
FROM Students s
LEFT JOIN Student_Points sp 
    ON sp.Student_ID_Number = s.Student_ID_Number
GROUP BY s.Student_ID_Number, s.Name, s.Course, s.Year_Level
ORDER BY Last_Activity_Date DESC
";



    $result = $conn->query($sql);
} catch (Exception $e) {
    die("Database query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fb;
        }

        .main-header {
            margin-top: 0;
            background-color: #e9eef6;
            text-align: center;
            padding: 25px;
            font-weight: bold;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .table th {
            background-color: #EDF0F6;
            font-weight: 700;
            color: #232D3F;
            white-space: nowrap;
        }

        .table td {
            color: #232D3F;
            vertical-align: middle;
        }

        .filter-section {
            margin-bottom: 20px;
        }
        
    </style>
</head>

<body>

<main class="main-content p-4">
    <div class="main-header">STUDENT ACTIVITY LOG</div>

    <div class="container">
        <!-- Filter Section -->
        <div class="row filter-section align-items-center">
            <div class="col-md-4">
                <label for="search" class="form-label fw-semibold mb-1">Search Student</label>
                <input type="text" id="search" class="form-control" placeholder="Enter name or ID...">
            </div>
            <div class="col-md-3">
                <label for="dateFilter" class="form-label fw-semibold mb-1">Filter by Date</label>
                <input type="date" id="dateFilter" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="sortSelect" class="form-label fw-semibold mb-1">Sort By</label>
                <select id="sortSelect" class="form-select">
                    <option value="recent">Most Recent</option>
                    <option value="oldest">Oldest</option>
                    <option value="highest">Highest Points</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-borderless table-hover align-middle" id="activityTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Program / Strand</th>
                        <th>Year Level</th>
                        <th>Date</th>
                        <th>Points Earned (Today)</th>
                        <th>Total Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['Student_ID_Number']) ?></td>
                                <td><?= htmlspecialchars($row['Name']) ?></td>
                                <td><?= htmlspecialchars($row['Course']) ?></td>
                                <td><?= htmlspecialchars($row['Year_Level']) ?></td>
                                <td><?= htmlspecialchars($row['Last_Activity_Date']) ?></td>
                                <td><?= htmlspecialchars($row['Points_Today']) ?></td>
                                <td><strong><?= htmlspecialchars($row['Total_Points']) ?></strong></td>
                            </tr>
                        <?php endwhile; ?>


                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No activity logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
 </main>
    <script>
        const searchInput = document.getElementById('search');
        const dateFilter = document.getElementById('dateFilter');
        const sortSelect = document.getElementById('sortSelect');

        function filterTable() {
            const rows = Array.from(document.querySelectorAll('#activityTable tbody tr'));
            const query = searchInput.value.toLowerCase();
            const selectedDate = dateFilter.value;
            let filtered = rows;

            // Filter by name or ID
            filtered = filtered.filter(row => {
                const text = row.textContent.toLowerCase();
                return text.includes(query);
            });

            // Filter by date
            if (selectedDate) {
                filtered = filtered.filter(row => row.cells[4].textContent.includes(selectedDate));
            }

            // Sort
            const sortType = sortSelect.value;
            filtered.sort((a, b) => {
                if (sortType === 'recent') return new Date(b.cells[4].textContent) - new Date(a.cells[4].textContent);
                if (sortType === 'oldest') return new Date(a.cells[4].textContent) - new Date(b.cells[4].textContent);
                if (sortType === 'highest') return parseFloat(b.cells[6].textContent) - parseFloat(a.cells[6].textContent);
            });

            // Re-render
            const tbody = document.querySelector('#activityTable tbody');
            tbody.innerHTML = '';
            filtered.forEach(r => tbody.appendChild(r));
        }

        searchInput.addEventListener('input', filterTable);
        dateFilter.addEventListener('change', filterTable);
        sortSelect.addEventListener('change', filterTable);
    </script>

</body>

</html>