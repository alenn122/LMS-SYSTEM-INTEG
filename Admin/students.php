<?php
include '../connection.php';
// include '../session_auth.php';
include '../Components/header.php';
include '../Components/sidebar.php';


$message = "";

// --- ADD STUDENT ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $student_id_number = mysqli_real_escape_string($conn, $_POST['student_id_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);

    // Picture upload (optional)
    $picture = "default.png";
    if (!empty($_FILES['picture']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $file_name = time() . "_" . basename($_FILES["picture"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            $picture = $file_name;
        }
    }

    $status = "Active";
    $total_points = 0;

    $sql = "INSERT INTO students (Name, Student_ID_Number, Email, Course, Year_Level, Picture, Status, Total_Points)
            VALUES ('$name', '$student_id_number', '$email', '$course', '$year_level', '$picture', '$status', '$total_points')";

    if (mysqli_query($conn, $sql)) {
        $message = "<div class='alert alert-success'>Student added successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}

// --- DELETE STUDENT ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM students WHERE Student_ID = $id");
    $message = "<div class='alert alert-warning'>Student deleted successfully.</div>";
}

// --- FETCH STUDENTS ---
$result = mysqli_query($conn, "SELECT * FROM students ORDER BY Student_ID DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Students</title>
    <style>
        .container {
            margin-left: 300px !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h4 class="mb-4">Add a New Student</h4>
                <?= $message; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Student ID Number</label>
                            <input type="text" name="student_id_number" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course</label>
                            <input type="text" name="course" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Year Level</label>
                            <select name="year_level" class="form-select" required>
                                <option value="">-- Select Year Level --</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="picture" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </form>
            </div>
        </div>

        <!-- STUDENT LIST TABLE -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="mb-4">Student List</h4>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Picture</th>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Year Level</th>
                                <th>Status</th>
                                <th>Total Points</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $row['Student_ID']; ?></td>
                                    <td><img src="uploads/<?= htmlspecialchars($row['Picture']); ?>" width="40" height="40" class="rounded-circle" alt="pic"></td>
                                    <td><?= htmlspecialchars($row['Name']); ?></td>
                                    <td><?= htmlspecialchars($row['Student_ID_Number']); ?></td>
                                    <td><?= htmlspecialchars($row['Email']); ?></td>
                                    <td><?= htmlspecialchars($row['Course']); ?></td>
                                    <td><?= htmlspecialchars($row['Year_Level']); ?></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($row['Status']); ?></span></td>
                                    <td><?= htmlspecialchars($row['Total_Points']); ?></td>
                                    <td>
                                        <a href="edit-student.php?id=<?= $row['Student_ID']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="?delete=<?= $row['Student_ID']; ?>" onclick="return confirm('Are you sure you want to delete this student?')" class="btn btn-sm btn-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</html>