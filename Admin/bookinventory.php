<?php
include '../connection.php';
// include '../session_auth.php';

// --- GET CATEGORIES & LOCATIONS ---
$categories = $conn->query("SELECT * FROM Category");
$locations = $conn->query("SELECT * FROM Location");

// --- ADD BOOK ---
if (isset($_POST['add_book'])) {
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $pub_date = $_POST['pub_date'];
    $location = $_POST['location'];
    $total = $_POST['total'];

    // UPLOAD IMAGE
    $cover_img = "";
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $cover_img = $targetDir . time() . "_" . basename($_FILES["cover_image"]["name"]);
        move_uploaded_file($_FILES["cover_image"]["tmp_name"], $cover_img);
    }

    $sql = "INSERT INTO Book (ISBN, Title, Cover_Image, Description, Author, Category, Publication_Date, Location, Total_Copies, Available_Copies, Borrowed_Copies) 
            VALUES ('$isbn','$title','$cover_img','$description','$author','$category','$pub_date','$location','$total','$total','0')";
    $conn->query($sql);
    header("Location: bookinventory.php?success=added");
    exit;
}

// --- UPDATE BOOK ---
if (isset($_POST['update_book'])) {
    $id = $_POST['book_id'];
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $pub_date = $_POST['pub_date'];
    $location = $_POST['location'];
    $total = $_POST['total'];

    $cover_img = $_POST['old_cover'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $cover_img = $targetDir . time() . "_" . basename($_FILES["cover_image"]["name"]);
        move_uploaded_file($_FILES["cover_image"]["tmp_name"], $cover_img);
    }

    $sql = "UPDATE Book SET ISBN='$isbn', Title='$title', Cover_Image='$cover_img', Description='$description',
            Author='$author', Category='$category', Publication_Date='$pub_date', Location='$location',
            Total_Copies='$total'
            WHERE Book_ID=$id";
    $conn->query($sql);
    header("Location: bookinventory.php?success=updated");
    exit;
}

// --- DELETE BOOK ---
if (isset($_POST['delete_book'])) {
    $id = $_POST['book_id'];
    $conn->query("DELETE FROM Book WHERE Book_ID=$id");
    header("Location: bookinventory.php?success=deleted");
    exit;
}

// --- GET BOOKS ---
$result = $conn->query("SELECT * FROM Book");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Inventory</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <link rel="stylesheet" href="bookinventory.css">
</head>
<body class="bg-light">

  <?php include '../Components/header.php'; ?>
  <?php include '../Components/sidebar.php'; ?>


  <!-- CONTENT -->
  <main class="main-content p-4">

    <div class="container-fluid">
      <h2 class="mb-4">Book Inventory</h2>

      <!-- ADD BOOK FORM -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h5 class="card-title">Add a New Book</h5>
          <form id="addBookForm" class="row g-3" method="POST" enctype="multipart/form-data">

            <div class="col-md-3"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Author</label><input type="text" name="author" class="form-control"></div>
            <div class="col-md-3">
  <label class="form-label">Category</label>
  <div class="category-wrapper" style="position: relative;">
    <select name="category" id="categorySelect" class="form-select" required>
      <option value="">-- Select Category --</option>
      <?php while($cat = $categories->fetch_assoc()): ?>
        <option value="<?= $cat['Category_Name'] ?>"><?= $cat['Category_Name'] ?></option>
      <?php endwhile; ?>
      <option value="add_new">➕ Add New Category</option>
    </select>

    <input type="text"
      id="newCategoryInput"
      name="new_category"
      class="form-control"
      placeholder="Enter new category"
      style="display:none; position:absolute; top:0; left:0; width:100%;">
  </div>
</div>


            <div class="col-md-3"><label class="form-label">Publication Date</label><input type="date" name="pub_date" class="form-control"></div>
            <div class="col-md-3">
  <label class="form-label">Location</label>
  <div class="location-wrapper" style="position: relative;">
    <select name="location" id="locationSelect" class="form-select" required>
      <option value="">-- Select Location --</option>
      <?php while($loc = $locations->fetch_assoc()): ?>
        <option value="<?= $loc['Location_Name'] ?>"><?= $loc['Location_Name'] ?></option>
      <?php endwhile; ?>
      <option value="add_new">➕ Add New Location</option>
    </select>

    <input type="text"
      id="newLocationInput"
      name="new_location"
      class="form-control"
      placeholder="Enter new location"
      style="display:none; position:absolute; top:0; left:0; width:100%;">
  </div>
</div>

            <div class="col-md-2"><label class="form-label">Total Copies</label><input type="number" name="total" class="form-control" min="1" required></div>
            <div class="col-md-4"><label class="form-label">Cover Image</label><input type="file" name="cover_image" class="form-control"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <div class="col-12"><button type="submit" name="add_book" class="btn btn-primary">Add Book</button></div>
          </form>
        </div>
      </div>

      <!-- BOOK TABLE -->
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Current Inventory</h5>
          <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>ISBN</th>
                  <th>Title</th>
                  <th>Cover</th>
                  <th>Description</th>
                  <th>Author</th>
                  <th>Category</th>
                  <th>Publication</th>
                  <th>Location</th>
                  <th>Total</th>
                  <th>Available</th>
                  <th>Borrowed</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $categories = $conn->query("SELECT * FROM Category"); // REFILL FOR MODALS
                $locations = $conn->query("SELECT * FROM Location");
                while($row = $result->fetch_assoc()):
                ?>
                <tr>
                  <td><?= $row['Book_ID'] ?></td>
                  <td><?= $row['ISBN'] ?></td>
                  <td><?= $row['Title'] ?></td>
                  <td><img src="<?= $row['Cover_Image'] ?>" class="cover-thumb"></td>
                  <td><?= $row['Description'] ?></td>
                  <td><?= $row['Author'] ?></td>
                  <td><?= $row['Category'] ?></td>
                  <td><?= $row['Publication_Date'] ?></td>
                  <td><?= $row['Location'] ?></td>
                  <td><?= $row['Total_Copies'] ?></td>
                  <td><?= $row['Available_Copies'] ?></td>
                  <td><?= $row['Borrowed_Copies'] ?></td>
                  <td>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['Book_ID'] ?>">Edit</button>
                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['Book_ID'] ?>">Delete</button>
                  </td>
                </tr>

                <!-- EDIT MODAL -->
                <div class="modal fade" id="editModal<?= $row['Book_ID'] ?>" tabindex="-1">
                  <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                      <form method="POST" enctype="multipart/form-data">
                        <div class="modal-header"><h5 class="modal-title">Edit Book</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body row g-3">
                          <input type="hidden" name="book_id" value="<?= $row['Book_ID'] ?>">
                          <input type="hidden" name="old_cover" value="<?= $row['Cover_Image'] ?>">
                          <div class="col-md-4"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control" value="<?= $row['ISBN'] ?>"></div>
                          <div class="col-md-4"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= $row['Title'] ?>"></div>
                          <div class="col-md-4"><label class="form-label">Author</label><input type="text" name="author" class="form-control" value="<?= $row['Author'] ?>"></div>
                          <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                              <?php
                              $cats2 = $conn->query("SELECT * FROM Category");
                              while($cat = $cats2->fetch_assoc()):
                              ?>
                                <option value="<?= $cat['Category_Name'] ?>" <?= ($cat['Category_Name']==$row['Category'])?'selected':'' ?>>
                                  <?= $cat['Category_Name'] ?>
                                </option>
                              <?php endwhile; ?>
                            </select>
                          </div>
                          <div class="col-md-4"><label class="form-label">Publication Date</label><input type="date" name="pub_date" class="form-control" value="<?= $row['Publication_Date'] ?>"></div>
                          <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <select name="location" class="form-select">
                              <?php
                              $locs2 = $conn->query("SELECT * FROM Location");
                              while($loc = $locs2->fetch_assoc()):
                              ?>
                                <option value="<?= $loc['Location_Name'] ?>" <?= ($loc['Location_Name']==$row['Location'])?'selected':'' ?>>
                                  <?= $loc['Location_Name'] ?>
                                </option>
                              <?php endwhile; ?>
                            </select>
                          </div>
                          <div class="col-md-4"><label class="form-label">Total</label><input type="number" name="total" class="form-control" value="<?= $row['Total_Copies'] ?>"></div>
                          <div class="col-md-4"><label class="form-label">Available</label><input type="number" class="form-control bg-light text-muted" value="<?= $row['Available_Copies'] ?>" readonly disabled></div>
                          <div class="col-md-4"><label class="form-label">Borrowed</label><input type="number" class="form-control bg-light text-muted" value="<?= $row['Borrowed_Copies'] ?>" readonly disabled></div>
                          <div class="col-md-6"><label class="form-label">Cover Image</label><input type="file" name="cover_image" class="form-control"></div>
                          <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control"><?= $row['Description'] ?></textarea></div>
                        </div>
                        <div class="modal-footer"><button type="submit" name="update_book" class="btn btn-primary">Save</button></div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- DELETE MODAL -->
                <div class="modal fade" id="deleteModal<?= $row['Book_ID'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header"><h5 class="modal-title">Confirm Delete</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">Are you sure you want to delete <strong><?= $row['Title'] ?></strong>?</div>
                        <div class="modal-footer">
                          <input type="hidden" name="book_id" value="<?= $row['Book_ID'] ?>">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="delete_book" class="btn btn-danger">Yes, Delete</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- SUCCESS MODAL -->
  <div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-body">
          <?php if(isset($_GET['success'])): ?>
            <p class="mb-3 fw-bold text-success">
              <?php if($_GET['success']=="added") echo "Book successfully added!";
                    elseif($_GET['success']=="updated") echo "Book updated successfully!";
                    elseif($_GET['success']=="deleted") echo "Book deleted successfully!"; ?>
            </p>
          <?php endif; ?>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    <?php if(isset($_GET['success'])): ?>
      var successModal = new bootstrap.Modal(document.getElementById('successModal'));
      successModal.show();
    <?php endif; ?>
  </script>


<script>
document.addEventListener("DOMContentLoaded", () => {
  const categorySelect = document.getElementById("categorySelect");
  const newCategoryInput = document.getElementById("newCategoryInput");
  const locationSelect = document.getElementById("locationSelect");
  const newLocationInput = document.getElementById("newLocationInput");

  categorySelect.addEventListener("change", () => {
    if (categorySelect.value === "add_new") {
      categorySelect.style.display = "none";
      newCategoryInput.style.display = "block";
      newCategoryInput.focus();
    }
  });

  locationSelect.addEventListener("change", () => {
    if (locationSelect.value === "add_new") {
      locationSelect.style.display = "none";
      newLocationInput.style.display = "block";
      newLocationInput.focus();
    }
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".category-wrapper")) {
      if (newCategoryInput.style.display === "block" && newCategoryInput.value === "") {
        newCategoryInput.style.display = "none";
        categorySelect.style.display = "block";
        categorySelect.value = "";
      }
    }

    if (!e.target.closest(".location-wrapper")) {
      if (newLocationInput.style.display === "block" && newLocationInput.value === "") {
        newLocationInput.style.display = "none";
        locationSelect.style.display = "block";
        locationSelect.value = "";
      }
    }
  });

  newCategoryInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const newCategory = newCategoryInput.value.trim();
      if (newCategory) {
        fetch("save_category_location.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "newCategory=" + encodeURIComponent(newCategory)
        })
        .then(res => res.text())
        .then(response => {
          alert(response);
          location.reload();
        });
      }
    }
  });

  newLocationInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      const newLocation = newLocationInput.value.trim();
      if (newLocation) {
        fetch("add_option.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "newLocation=" + encodeURIComponent(newLocation)
        })
        .then(res => res.text())
        .then(response => {
          alert(response);
          location.reload();
        });
      }
    }
  });
});
</script>




</body>
</html>
