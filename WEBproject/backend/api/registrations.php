<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

// ---------------------------------------------------------------
// GET /api/registrations.php
//   - Admin: every registration, with student + event details.
//   - Student: just their own registered event ids.
// ---------------------------------------------------------------
if ($method === 'GET') {
    requireLogin();

    if (currentUserRole() === 'admin') {
        $stmt = $pdo->query(
            'SELECT u.student_id, u.name, u.email, e.title AS event, DATE(r.registered_at) AS date
             FROM registrations r
             JOIN users u  ON u.id = r.user_id
             JOIN events e ON e.id = r.event_id
             ORDER BY r.registered_at DESC'
        );
        respond(['registrations' => $stmt->fetchAll()]);
    }

    $stmt = $pdo->prepare('SELECT event_id FROM registrations WHERE user_id = ?');
    $stmt->execute([currentUserId()]);
    respond(['eventIds' => array_column($stmt->fetchAll(), 'event_id')]);
}

// ---------------------------------------------------------------
// POST /api/registrations.php  { eventId }
// Toggles registration for the logged-in student on that event.
// ---------------------------------------------------------------
if ($method === 'POST') {
    requireLogin();
    $body = readJsonBody();
    $eventId = (int) ($body['eventId'] ?? 0);

    if ($eventId <= 0) {
        respond(['error' => 'A valid eventId is required.'], 422);
    }

    $uid = currentUserId();
    $check = $pdo->prepare('SELECT id FROM registrations WHERE user_id = ? AND event_id = ?');
    $check->execute([$uid, $eventId]);
    $existing = $check->fetch();

    if ($existing) {
        $pdo->prepare('DELETE FROM registrations WHERE id = ?')->execute([$existing['id']]);
        respond(['registered' => false]);
    }

    $pdo->prepare('INSERT INTO registrations (user_id, event_id) VALUES (?, ?)')->execute([$uid, $eventId]);
    respond(['registered' => true]);
}

respond(['error' => 'Method not allowed'], 405);
