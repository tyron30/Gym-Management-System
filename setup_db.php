<?php
include "config/db.php";

// Add cashier user
$password = password_hash('cashier', PASSWORD_DEFAULT);
$conn->query("INSERT INTO users (fullname, username, password, role) VALUES ('Cashier User', 'cashier', '$password', 'cashier')");

// Add some sample members
$conn->query("INSERT INTO members (member_id, fullname, plan, start_date, end_date, status) VALUES
('M001', 'John Doe', 'Monthly', '2024-01-01', '2024-02-01', 'ACTIVE'),
('M002', 'Jane Smith', 'Annual', '2024-01-01', '2025-01-01', 'ACTIVE'),
('M003', 'Bob Johnson', 'Monthly', '2023-12-01', '2024-01-01', 'EXPIRED')");

// Add some sample payments
$conn->query("INSERT INTO payments (member_id, amount, receipt_no, payment_date) VALUES
(1, 500.00, 'R001', '2024-01-01 10:00:00'),
(2, 5500.00, 'R002', '2024-01-01 11:00:00'),
(1, 500.00, 'R003', '2024-01-15 09:00:00')");

// Add some sample attendance
$conn->query("INSERT INTO attendance (member_id, checkin_time) VALUES
(1, '2024-01-02 08:00:00'),
(2, '2024-01-02 09:00:00'),
(1, '2024-01-03 08:30:00')");

echo "Database setup completed successfully!";
echo "<br>Admin credentials: username: admin, password: admin";
echo "<br>Cashier credentials: username: cashier, password: cashier";
?>
