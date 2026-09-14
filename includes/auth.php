<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';

function isUserLoggedIn()
{
    return isset($_SESSION['user_id']);
}
 
function requireLogin()
{
    if (!isUserLoggedIn()) {
        redirect('login.php');
    }
}
 
function redirectIfLoggedIn()
{
    if (isUserLoggedIn()) {
        redirect('index.php');
    }
}

function requireApiLogin(): void
{
    if (!isUserLoggedIn()) {
        send_json(['error' => 'Unauthorized'], 401);
    }
}

function registerUser(string $username, string $email, string $password): array
{
    $username = trim($username);
    $email = trim($email);

    if ($username === '' || $email === '' || $password === '') {
        return ['success' => false, 'error' => 'Please complete all required fields.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Please enter a valid email address.'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters.'];
    }

    try {
        $pdo = get_db();

        $stmt = $pdo->prepare('SELECT id FROM Users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Username or email is already taken.'];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, $passwordHash]);

        return ['success' => true, 'error' => null];
    } catch (PDOException $e) {
        error_log('Registration processing anomaly: ' . $e->getMessage());
        return ['success' => false, 'error' => 'An infrastructure resource issue occurred. Please retry.'];
    }
}

function loginUser(string $username, string $password): array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return ['success' => false, 'error' => 'Please enter your username and password.'];
    }

    try {
        $pdo = get_db();

        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM Users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        return ['success' => true, 'error' => null];
    } catch (PDOException $e) {
        error_log('Login processing anomaly: ' . $e->getMessage());
        return ['success' => false, 'error' => 'An infrastructure resource issue occurred. Please retry.'];
    }
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}