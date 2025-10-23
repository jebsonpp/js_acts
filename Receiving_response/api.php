<?php
// api.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? null;

/**
 * Helper: JSON response
 */
function json($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Helper: get current user (from session)
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * ACTIONS:
 * - register        (POST)  {username, firstname, lastname, password}
 * - login           (POST)  {username, password}
 * - logout          (POST)
 * - check_username  (GET)   ?username=...
 * - get_users       (GET)   returns list (admin only)
 * - add_user        (POST)  (admin only) {username, firstname, lastname, password, is_admin}
 */

if ($action === 'register' && $method === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $firstname === '' || $lastname === '' || $password === '') {
        json(['success' => false, 'message' => 'All fields are required'], 400);
        return;
    }

    if (strlen($password) < 8) {
        json(['success' => false, 'message' => 'Password must be at least 8 characters long'], 400);
        return;
    }

    // check username uniqueness
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        json(['success' => false, 'message' => 'Username already taken'], 409);
        return;
    }

    // insert
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (username, firstname, lastname, password) VALUES (?, ?, ?, ?)');
    $insert->execute([$username, $firstname, $lastname, $hash]);

    json(['success' => true, 'message' => 'Registered successfully']);
    return;
}

if ($action === 'login' && $method === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        json(['success' => false, 'message' => 'Both fields required'], 400);
    }

    $stmt = $pdo->prepare('SELECT id, username, firstname, lastname, password, is_admin, date_added FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        json(['success' => false, 'message' => 'Invalid credentials'], 401);
    }

    // store user in session (avoid password)
    unset($user['password']);
    $_SESSION['user'] = $user;

    json(['success' => true, 'message' => 'Logged in', 'user' => $user]);
    return;
}

if ($action === 'logout' && $method === 'POST') {
    session_unset();
    session_destroy();
    json(['success' => true, 'message' => 'Logged out']);
    return;
}

if ($action === 'check_username' && $method === 'GET') {
    $username = trim($_GET['username'] ?? '');
    if ($username === '') {
        json(['exists' => false]);
        return;
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    json(['exists' => (bool)$stmt->fetch()]);
    return;
}

if ($action === 'get_users' && $method === 'GET') {
    $user = current_user();
    if (!$user || !$user['is_admin']) {
        json(['success' => false, 'message' => 'Unauthorized'], 403);
        return;
    }

    try {
        $q = $pdo->query('SELECT id, username, firstname, lastname, is_admin, date_added FROM users ORDER BY date_added DESC');
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        json(['success' => true, 'users' => $rows]);
        return;
    } catch (PDOException $e) {
        json(['success' => false, 'message' => 'Database error'], 500);
        return;
    }
}

if ($action === 'add_user' && $method === 'POST') {
    // Check if current user is admin
    $current = current_user();
    if (!$current || !$current['is_admin']) {
        json(['success' => false, 'message' => 'Unauthorized'], 403);
        return;
    }

    $username = trim($_POST['username'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $password = $_POST['password'] ?? '';
    $is_admin = !empty($_POST['is_admin']);

    if ($username === '' || $firstname === '' || $lastname === '' || $password === '') {
        json(['success' => false, 'message' => 'All fields are required'], 400);
        return;
    }

    if (strlen($password) < 8) {
        json(['success' => false, 'message' => 'Password must be at least 8 characters'], 400);
        return;
    }

    try {
        // Check username uniqueness
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            json(['success' => false, 'message' => 'Username already taken'], 409);
            return;
        }

        // Insert new user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (username, firstname, lastname, password, is_admin) VALUES (?, ?, ?, ?, ?)');
        $insert->execute([$username, $firstname, $lastname, $hash, $is_admin ? 1 : 0]);

        json(['success' => true, 'message' => 'User added successfully']);
        return;
    } catch (PDOException $e) {
        json(['success' => false, 'message' => 'Database error'], 500);
        return;
    }
}

// unknown action
json(['success' => false, 'message' => 'Action not found'], 400);
