<?php
include "config/db.php";

echo "Starting database migration...<br>";

// Add student columns to members table
try {
    $conn->query("ALTER TABLE members ADD COLUMN is_student BOOLEAN DEFAULT FALSE AFTER status");
    echo "✓ Added is_student column to members table<br>";
} catch (Exception $e) {
    echo "Note: is_student column might already exist in members table<br>";
}

try {
    $conn->query("ALTER TABLE members ADD COLUMN student_id VARCHAR(50) AFTER is_student");
    echo "✓ Added student_id column to members table<br>";
} catch (Exception $e) {
    echo "Note: student_id column might already exist in members table<br>";
}

// Add discount columns to payments table
try {
    $conn->query("ALTER TABLE payments ADD COLUMN is_student_discount BOOLEAN DEFAULT FALSE AFTER notes");
    echo "✓ Added is_student_discount column to payments table<br>";
} catch (Exception $e) {
    echo "Note: is_student_discount column might already exist in payments table<br>";
}

try {
    $conn->query("ALTER TABLE payments ADD COLUMN student_id VARCHAR(50) AFTER is_student_discount");
    echo "✓ Added student_id column to payments table<br>";
} catch (Exception $e) {
    echo "Note: student_id column might already exist in payments table<br>";
}

try {
    $conn->query("ALTER TABLE payments ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER student_id");
    echo "✓ Added discount_amount column to payments table<br>";
} catch (Exception $e) {
    echo "Note: discount_amount column might already exist in payments table<br>";
}

// Update member_id column to id in setup_db.php sample data (fixing the sample data)
try {
    // Check if we need to update the sample data
    $result = $conn->query("SELECT COUNT(*) as count FROM members WHERE member_id IS NOT NULL");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        // Update the sample data to use id instead of member_id
        $conn->query("UPDATE members SET member_code = CONCAT('MEM', LPAD(id, 4, '0')) WHERE member_code IS NULL OR member_code = ''");
        echo "✓ Updated member codes for existing members<br>";
    }
} catch (Exception $e) {
    echo "Note: Member codes already updated<br>";
}

try {
    $conn->query("CREATE TABLE IF NOT EXISTS gym_settings (
        id INT(11) NOT NULL AUTO_INCREMENT,
        gym_name VARCHAR(255) NOT NULL DEFAULT 'Gym Management System',
        logo_path VARCHAR(255) NOT NULL DEFAULT 'gym logo.jpg',
        background_path VARCHAR(255) NOT NULL DEFAULT 'gym background.jpg',
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "✓ Created gym_settings table<br>";
} catch (Exception $e) {
    echo "Note: gym_settings table might already exist<br>";
}

// Insert default gym settings if not exists
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM gym_settings");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $conn->query("INSERT INTO gym_settings (gym_name, logo_path, background_path) VALUES ('Gym Management System', 'gym logo.jpg', 'gym background.jpg')");
        echo "✓ Inserted default gym settings<br>";
    }
} catch (Exception $e) {
    echo "Note: Default gym settings might already exist<br>";
}

// Add sidebar_theme column to gym_settings table
try {
    $conn->query("ALTER TABLE gym_settings ADD COLUMN sidebar_theme VARCHAR(50) NOT NULL DEFAULT 'primary' AFTER background_path");
    echo "✓ Added sidebar_theme column to gym_settings table<br>";
} catch (Exception $e) {
    echo "Note: sidebar_theme column might already exist in gym_settings table<br>";
}

echo "<br>Database migration completed successfully!<br>";
echo "You can now test the student discount feature, gym settings feature, and sidebar theme customization.<br>";
?>
