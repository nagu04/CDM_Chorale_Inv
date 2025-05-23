<?php
include 'db_connect.php';

// SQL to create history table
$sql = "CREATE TABLE IF NOT EXISTS history (
    history_id int(11) NOT NULL AUTO_INCREMENT,
    type enum('BORROW','REPORT') NOT NULL,
    borrowed_by varchar(100) NOT NULL,
    date date NOT NULL,
    category varchar(50) NOT NULL,
    item_name varchar(100) NOT NULL,
    quantity int(11) NOT NULL,
    sn varchar(50) DEFAULT NULL,
    status varchar(50) NOT NULL,
    remarks text,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (history_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql) === TRUE) {
    echo "History table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 