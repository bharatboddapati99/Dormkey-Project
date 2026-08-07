<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $surname    = trim($_POST['surname'] ?? '');
    $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password   = $_POST['password'] ?? '';
    $college    = trim($_POST['college'] ?? '');

    if (!$first_name || !$surname || !$email || !$password || !$college) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: auth.php");
        exit();
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email is already registered. Please log in.";
        header("Location: auth.php");
        exit();
    }

    // Hash password securely
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (first_name, surname, email, password, college) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$first_name, $surname, $email, $hashedPassword, $college])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $first_name;
        $_SESSION['success'] = "Account created successfully! Welcome to DormKey.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to register. Please try again.";
        header("Location: auth.php");
        exit();
    }
}
?>