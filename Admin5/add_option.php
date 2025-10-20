<?php
include('db.php');

if (isset($_POST['newCategory'])) {
    $newCategory = trim($_POST['newCategory']);

    if (!empty($newCategory)) {
        $check = $conn->prepare("SELECT * FROM Category WHERE Category_Name = ?");
        $check->bind_param("s", $newCategory);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "Category already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO Category (Category_Name) VALUES (?)");
            $stmt->bind_param("s", $newCategory);
            if ($stmt->execute()) {
                echo "New category added successfully!";
            } else {
                echo "Failed to add category.";
            }
            $stmt->close();
        }
    }
}

if (isset($_POST['newLocation'])) {
    $newLocation = trim($_POST['newLocation']);

    if (!empty($newLocation)) {
        $check = $conn->prepare("SELECT * FROM Location WHERE Location_Name = ?");
        $check->bind_param("s", $newLocation);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "Location already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO Location (Location_Name) VALUES (?)");
            $stmt->bind_param("s", $newLocation);
            if ($stmt->execute()) {
                echo "New location added successfully!";
            } else {
                echo "Failed to add location.";
            }
            $stmt->close();
        }
    }
}
?>
