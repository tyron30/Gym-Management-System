<?php
include "config/db.php";

session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Gym Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <meta http-equiv="refresh" content="3;url=index.php">
</head>
<body>
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center bg-primary">
        <div class="text-center text-white">
            <div class="mb-4">
                <i class="fas fa-sign-out-alt fa-5x text-white"></i>
            </div>
            <h1 class="h2 mb-3">You have been logged out successfully</h1>
            <p class="lead mb-4">Thank you for using Gym Management System</p>
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Redirecting to login page...</p>
            <div class="mt-4">
                <a href="index.php" class="btn btn-light btn-lg">
                    <i class="fas fa-home me-2"></i>Go to Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
