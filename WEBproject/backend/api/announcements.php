<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET /api/announcements.php
if ($method === 'GET') {
    $stmt = $pdo->query(
        'SELECT id, title, target, message, DATE(created_at) AS date
         FROM announcements ORDER BY created_at DESC'
    );
    respond(['announcements' => $stmt->fetchAll()]);
}

// POST /api/announcements.php  (admin only)
if ($method === 'POST') {
    requireAdmin();
    $body = readJsonBody();

    $title   = trim($body['title'] ?? '');
    $target  = trim($body['target'] ?? '') ?: 'All';
    $message = trim($body['message'] ?? '');

    if ($title === '' || $message === '') {
        respond(['error' => 'Title and message are required.'], 422);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO announcements (title, target, message, created_by) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$title, $target, $message, currentUserId()]);

    respond(['success' => true, 'id' => (int) $pdo->lastInsertId()], 201);
}

respond(['error' => 'Method not allowed'], 405);
