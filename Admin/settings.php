<?php
include '../connection.php';
// include '../session_auth.php';

// --- GET SETTINGS ---
$settingsQuery = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settingsQuery->fetch_assoc();

// --- UPDATE SETTINGS ---
if (isset($_POST['update_settings'])) {
    $openHour = $_POST['open_hour'];
    $closeHour = $_POST['close_hour'];
    $maxBorrowLimit = $_POST['max_borrow_limit'];
    $borrowDuration = $_POST['borrow_duration'];
    $finePerDay = $_POST['fine_per_day'];

    $stmt = $conn->prepare("
        UPDATE settings SET 
            Open_Hour = ?, 
            Close_Hour = ?, 
            Max_Borrow_Limit = ?, 
            Borrow_Duration = ?, 
            Fine_Per_Day = ?
        WHERE Setting_ID = ?
    ");
    $stmt->bind_param(
        "ssiiid", 
        $openHour, 
        $closeHour, 
        $maxBorrowLimit, 
        $borrowDuration, 
        $finePerDay, 
        $settings['Setting_ID']
    );
    $stmt->execute();
    $stmt->close();

    header("Location: settings.php?success=updated");
    exit;
}

// --- LIBRARY NAME (STATIC / read-only) ---
$libraryName = $settings['Library_Name'] ?? 'Library Name';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Library Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: "Poppins", sans-serif; }
    .settings-card { background: white; border-radius: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 40px; }
    .form-label { font-weight: 600; text-transform: uppercase; font-size: 0.9rem; color: #333; }
    input.form-control { border-radius: 10px; padding: 10px 12px; font-size: 1rem; }
    .section-title { font-weight: 600; margin-top: 20px; margin-bottom: 10px; color: #555; text-transform: uppercase; }
    .btn-save { background-color: #0066cc; border: none; border-radius: 10px; padding: 10px 30px; font-weight: 600; }
    .btn-save:hover { background-color: #005bb5; }
  </style>
</head>
<body>

  <?php include '../Components/header.php'; ?>
  <?php include '../Components/sidebar.php'; ?>

  <main class="main-content p-4">
    <div class="container-fluid">
      <h2 class="mb-4 fw-semibold">Library Settings</h2>

      <div class="settings-card mx-auto col-lg-8">
        <form method="POST" class="row g-4">
          <!-- Library Name -->
          <div class="col-12">
            <label class="form-label">Library Name</label>
            <input type="text" class="form-control" value="<?= $libraryName ?>" readonly>
          </div>

          <!-- Operating Hours -->
          <div class="col-12">
            <p class="section-title">Operating Hours</p>
          </div>
          <div class="col-md-6">
            <label class="form-label">Open Hour</label>
            <input type="time" name="open_hour" class="form-control" value="<?= $settings['Open_Hour'] ?? '' ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Close Hour</label>
            <input type="time" name="close_hour" class="form-control" value="<?= $settings['Close_Hour'] ?? '' ?>" required>
          </div>

          <!-- Max Borrow Limit -->
          <div class="col-md-6">
            <label class="form-label">Maximum Borrow Limit</label>
            <input type="number" name="max_borrow_limit" class="form-control" min="1" value="<?= $settings['Max_Borrow_Limit'] ?? '' ?>" required>
          </div>

          <!-- Borrow Duration -->
          <div class="col-md-6">
            <label class="form-label">Borrow Duration (Days)</label>
            <input type="number" name="borrow_duration" class="form-control" min="1" value="<?= $settings['Borrow_Duration'] ?? '' ?>" required>
          </div>

          <!-- Fine per Day -->
          <div class="col-md-6">
            <label class="form-label">Fine per Day (₱)</label>
            <input type="number" step="0.01" name="fine_per_day" class="form-control" min="0" value="<?= $settings['Fine_Per_Day'] ?? '' ?>" required>
          </div>

          <div class="col-12 text-end mt-4">
            <button type="submit" name="update_settings" class="btn btn-primary btn-save">💾 Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <!-- SUCCESS MODAL -->
  <div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-body">
          <?php if(isset($_GET['success']) && $_GET['success']=="updated"): ?>
            <p class="mb-3 fw-bold text-success">Settings updated successfully!</p>
          <?php endif; ?>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    <?php if(isset($_GET['success']) && $_GET['success']=="updated"): ?>
      var successModal = new bootstrap.Modal(document.getElementById('successModal'));
      successModal.show();
    <?php endif; ?>
  </script>

</body>
</html>
