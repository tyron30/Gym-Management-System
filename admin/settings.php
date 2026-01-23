<?php
include "../config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
if ($user['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch current gym settings
$settings = $conn->query("SELECT * FROM gym_settings WHERE id = 1")->fetch_assoc();
if (!$settings) {
    // Insert default settings if not exists
    $conn->query("INSERT INTO gym_settings (gym_name, logo_path, background_path, sidebar_theme) VALUES ('Gym Management System', 'gym logo.jpg', 'gym background.jpg', 'primary')");
    $settings = $conn->query("SELECT * FROM gym_settings WHERE id = 1")->fetch_assoc();
}

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_name'])) {
        // Update gym name
        $new_name = trim($_POST['gym_name']);
        if (!empty($new_name)) {
            $stmt = $conn->prepare("UPDATE gym_settings SET gym_name = ? WHERE id = 1");
            $stmt->bind_param("s", $new_name);
            if ($stmt->execute()) {
                $message = "Gym name updated successfully!";
                $message_type = "success";
                $settings['gym_name'] = $new_name;
            } else {
                $message = "Error updating gym name.";
                $message_type = "danger";
            }
        } else {
            $message = "Gym name cannot be empty.";
            $message_type = "danger";
        }
    } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        // Handle logo upload
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array($_FILES['logo']['type'], $allowed_types) && $_FILES['logo']['size'] <= $max_size) {
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $new_filename = 'gym_logo_' . time() . '.' . $file_extension;
            $upload_path = '../' . $new_filename;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("UPDATE gym_settings SET logo_path = ? WHERE id = 1");
                $stmt->bind_param("s", $new_filename);
                if ($stmt->execute()) {
                    $message = "Gym logo updated successfully!";
                    $message_type = "success";
                    $settings['logo_path'] = $new_filename;
                } else {
                    $message = "Error updating logo in database.";
                    $message_type = "danger";
                }
            } else {
                $message = "Error uploading logo file.";
                $message_type = "danger";
            }
        } else {
            $message = "Invalid file type or size. Please upload a JPEG, PNG, or GIF image under 5MB.";
            $message_type = "danger";
        }
    } elseif (isset($_FILES['background']) && $_FILES['background']['error'] == 0) {
        // Handle background upload
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array($_FILES['background']['type'], $allowed_types) && $_FILES['background']['size'] <= $max_size) {
            $file_extension = pathinfo($_FILES['background']['name'], PATHINFO_EXTENSION);
            $new_filename = 'gym_background_' . time() . '.' . $file_extension;
            $upload_path = '../' . $new_filename;

            if (move_uploaded_file($_FILES['background']['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("UPDATE gym_settings SET background_path = ? WHERE id = 1");
                $stmt->bind_param("s", $new_filename);
                if ($stmt->execute()) {
                    $message = "Gym background updated successfully!";
                    $message_type = "success";
                    $settings['background_path'] = $new_filename;
                } else {
                    $message = "Error updating background in database.";
                    $message_type = "danger";
                }
            } else {
                $message = "Error uploading background file.";
                $message_type = "danger";
            }
        } else {
            $message = "Invalid file type or size. Please upload a JPEG, PNG, or GIF image under 5MB.";
            $message_type = "danger";
        }
    } elseif (isset($_POST['update_sidebar_theme'])) {
        // Update sidebar theme
        $new_theme = trim($_POST['sidebar_theme']);
        $allowed_themes = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark', 'light'];
        if (in_array($new_theme, $allowed_themes)) {
            $stmt = $conn->prepare("UPDATE gym_settings SET sidebar_theme = ? WHERE id = 1");
            $stmt->bind_param("s", $new_theme);
            if ($stmt->execute()) {
                $message = "Sidebar theme updated successfully!";
                $message_type = "success";
                $settings['sidebar_theme'] = $new_theme;
            } else {
                $message = "Error updating sidebar theme.";
                $message_type = "danger";
            }
        } else {
            $message = "Invalid sidebar theme selected.";
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Gym Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-<?php echo htmlspecialchars($settings['sidebar_theme']); ?> <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?> vh-100" style="width: 250px;">
            <div class="p-3">
                <div class="text-center mb-4">
                    <img src="../<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Gym Logo" class="rounded-circle mb-2" style="width: 80px; height: 80px;">
                    <h5 class="fw-bold"><?php echo htmlspecialchars($settings['gym_name']); ?></h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="members.php">
                            <i class="fas fa-users me-2"></i>Members
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="attendance.php">
                            <i class="fas fa-calendar-check me-2"></i>Attendance
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="employees.php">
                            <i class="fas fa-user-tie me-2"></i>Employees
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?> active" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link <?php echo ($settings['sidebar_theme'] == 'light') ? 'text-dark' : 'text-white'; ?>" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Bar -->
            <nav class="navbar navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand mb-0 h1">Settings - <?php echo htmlspecialchars($user['fullname']); ?> (Admin)</span>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <h1 class="h3 mb-4">Gym Settings</h1>

                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i><?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Gym Name Settings -->
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-building me-2"></i>Gym Name</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="gym_name" class="form-label">Current Gym Name</label>
                                                <input type="text" class="form-control" id="gym_name" name="gym_name"
                                                       value="<?php echo htmlspecialchars($settings['gym_name']); ?>" required>
                                            </div>
                                            <button type="submit" name="update_name" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Name
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Logo Settings -->
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-image me-2"></i>Gym Logo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                            <img src="../<?php echo htmlspecialchars($settings['logo_path']); ?>"
                                                 alt="Current Logo" class="img-thumbnail mb-3" style="max-width: 150px; max-height: 150px;">
                                            <p class="text-muted small">Current: <?php echo htmlspecialchars($settings['logo_path']); ?></p>
                                        </div>
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="mb-3">
                                                <label for="logo" class="form-label">Upload New Logo</label>
                                                <input type="file" class="form-control" id="logo" name="logo" accept="image/*" required>
                                                <div class="form-text">Accepted formats: JPEG, PNG, GIF. Max size: 5MB</div>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-upload me-2"></i>Upload Logo
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Background Settings -->
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-image me-2"></i>Gym Background</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                            <img src="../<?php echo htmlspecialchars($settings['background_path']); ?>"
                                                 alt="Current Background" class="img-thumbnail mb-3" style="max-width: 300px; max-height: 200px;">
                                            <p class="text-muted small">Current: <?php echo htmlspecialchars($settings['background_path']); ?></p>
                                        </div>
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="mb-3">
                                                <label for="background" class="form-label">Upload New Background</label>
                                                <input type="file" class="form-control" id="background" name="background" accept="image/*" required>
                                                <div class="form-text">Accepted formats: JPEG, PNG, GIF. Max size: 5MB</div>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-upload me-2"></i>Upload Background
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar Theme Settings -->
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="fas fa-palette me-2"></i>Sidebar Theme</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="sidebar_theme" class="form-label">Select Sidebar Color</label>
                                                <select class="form-select" id="sidebar_theme" name="sidebar_theme" required>
                                                    <option value="primary" <?php echo ($settings['sidebar_theme'] == 'primary') ? 'selected' : ''; ?>>Blue (Primary)</option>
                                                    <option value="secondary" <?php echo ($settings['sidebar_theme'] == 'secondary') ? 'selected' : ''; ?>>Gray (Secondary)</option>
                                                    <option value="success" <?php echo ($settings['sidebar_theme'] == 'success') ? 'selected' : ''; ?>>Green (Success)</option>
                                                    <option value="danger" <?php echo ($settings['sidebar_theme'] == 'danger') ? 'selected' : ''; ?>>Red (Danger)</option>
                                                    <option value="warning" <?php echo ($settings['sidebar_theme'] == 'warning') ? 'selected' : ''; ?>>Yellow (Warning)</option>
                                                    <option value="info" <?php echo ($settings['sidebar_theme'] == 'info') ? 'selected' : ''; ?>>Cyan (Info)</option>
                                                    <option value="dark" <?php echo ($settings['sidebar_theme'] == 'dark') ? 'selected' : ''; ?>>Dark</option>
                                                    <option value="light" <?php echo ($settings['sidebar_theme'] == 'light') ? 'selected' : ''; ?>>Light</option>
                                                </select>
                                                <div class="form-text">This theme will apply to all users (admin and cashier).</div>
                                            </div>
                                            <button type="submit" name="update_sidebar_theme" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Update Sidebar Theme
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5 border-top">
        <div class="container">
            <small>Developed by Tyron Del Valle</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.flex-grow-1');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('main-expanded');
                // Update toggle icon
                const icon = sidebarToggle.querySelector('i');
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    icon.className = 'fas fa-times'; // Close icon when collapsed
                } else {
                    icon.className = 'fas fa-bars'; // Bars icon when expanded
                }
            });
        });
    </script>
</body>
</html>
