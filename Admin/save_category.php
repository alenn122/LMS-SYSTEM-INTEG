<?php
include '../connection.php';
if(isset($_POST['category'])){
  $cat = trim($_POST['category']);
  if($cat != ''){
    $stmt = $conn->prepare("INSERT IGNORE INTO Category (Category_Name) VALUES (?)");
    $stmt->bind_param("s", $cat);
    $stmt->execute();
  }
}
?>
