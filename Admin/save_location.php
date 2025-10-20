<?php
include '../connection.php';
if(isset($_POST['location'])){
  $loc = trim($_POST['location']);
  if($loc != ''){
    $stmt = $conn->prepare("INSERT IGNORE INTO Location (Location_Name) VALUES (?)");
    $stmt->bind_param("s", $loc);
    $stmt->execute();
  }
}
?>
