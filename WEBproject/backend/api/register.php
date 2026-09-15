<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['error' => 'Method not allowed'], 405);
}

$body = readJsonBody();
$studentId = trim($body['studentId'] ?? '');
$name      = trim($body['name'] ?? '');
$email     = trim($body['email'] ?? '');
$password  = $body['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    respond(['error' => 'Name, email and password are required.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['error' => 'Please enter a valid email address.'], 422);
}
if (strlen($password) < 6) {
    respond(['error' => 'Password must be at least 6 characters.'], 422);
}

$check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    respond(['error' => 'An account with that email already exists.'], 409);
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    'INSERT INTO users (student_id, name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$studentId ?: null, $name, $email, $hash, 'student']);

$userId = (int) $pdo->lastInsertId();

// Log the new user straight in
$_SESSION['user_id'] = $userId;
$_SESSION['role']    = 'student';
$_SESSION['name']    = $name;

respond([
    'user' => ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'student'],
]);
