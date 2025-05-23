<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all required fields are present
    $required_fields = ['type', 'borrowedBy', 'date', 'category', 'itemName', 'quantity', 'status'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        die("Error: Missing required fields: " . implode(', ', $missing_fields));
    }

    // Get form data
    $type = $_POST['type'];
    $borrowed_by = $_POST['borrowedBy'];
    $date = $_POST['date'];
    $category = $_POST['category'];
    $item_name = $_POST['itemName'];
    $quantity = $_POST['quantity'];
    $sn = isset($_POST['sn']) ? $_POST['sn'] : null;
    $status = $_POST['status'];
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : null;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Prepare and execute the SQL statement for history
        $stmt = $conn->prepare("INSERT INTO history (type, borrowed_by, date, category, item_name, quantity, sn, status, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssisss", $type, $borrowed_by, $date, $category, $item_name, $quantity, $sn, $status, $remarks);
        $stmt->execute();
        $stmt->close();

        // If this is a borrow operation, update the inventory
        if ($type === 'BORROW') {
            // Determine which table to update based on category
            $table = '';
            switch(strtolower($category)) {
                case 'instruments':
                    $table = 'instruments';
                    $name_field = 'instrument_name';
                    break;
                case 'clothing':
                    $table = 'clothing';
                    $name_field = 'clothing_name';
                    break;
                case 'accessories':
                    $table = 'accessories';
                    $name_field = 'accessory_name';
                    break;
                default:
                    throw new Exception("Invalid category");
            }

            // Update the quantity in the respective table
            $update_stmt = $conn->prepare("UPDATE $table SET quantity = quantity - ? WHERE $name_field = ?");
            $update_stmt->bind_param("is", $quantity, $item_name);
            $update_stmt->execute();
            $update_stmt->close();
        }

        // Commit transaction
        $conn->commit();

        // Redirect to history page on success
        header("Location: history.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid request method");
}

$conn->close();
?> 