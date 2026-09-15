<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

// ---------------------------------------------------------------
// GET /api/events.php[?category=Computing&search=hack]
// Returns every event, plus whether the current user is registered.
// ---------------------------------------------------------------
if ($method === 'GET') {
    $category = trim($_GET['category'] ?? 'All');
    $search   = trim($_GET['search'] ?? '');
    $uid      = currentUserId() ?? 0;

    $sql = 'SELECT e.id, e.title, e.category, e.event_date AS date, e.event_time AS time,
                   e.venue, e.image, e.description,
                   IF(r.id IS NULL, 0, 1) AS registered
            FROM events e
            LEFT JOIN registrations r ON r.event_id = e.id AND r.user_id = :uid
            WHERE 1=1';
    $params = ['uid' => $uid];

    if ($category !== '' && $category !== 'All') {
        $sql .= ' AND e.category = :category';
        $params['category'] = $category;
    }
    if ($search !== '') {
        $sql .= ' AND (e.title LIKE :search OR e.description LIKE :search)';
        $params['search'] = "%{$search}%";
    }
    $sql .= ' ORDER BY e.event_date ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

    // Normalize registered to a real boolean for the frontend
    foreach ($events as &$ev) {
        $ev['registered'] = (bool) $ev['registered'];
    }

    respond(['events' => $events]);
}

// ---------------------------------------------------------------
// POST /api/events.php  (admin only) — create a new event
// ---------------------------------------------------------------
if ($method === 'POST') {
    requireAdmin();
    $body = readJsonBody();

    $title       = trim($body['title'] ?? '');
    $category    = trim($body['category'] ?? '');
    $date        = trim($body['date'] ?? '');
    $time        = trim($body['time'] ?? '');
    $venue       = trim($body['venue'] ?? '');
    $image       = trim($body['image'] ?? '') ?: 'default-event.png';
    $description = trim($body['description'] ?? '');

    $validCategories = ['Computing', 'Business', 'Sports', 'Cultural'];
    if ($title === '' || $date === '' || $time === '' || $venue === '' || $description === '') {
        respond(['error' => 'All event fields except image are required.'], 422);
    }
    if (!in_array($category, $validCategories, true)) {
        respond(['error' => 'Invalid category.'], 422);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO events (title, category, event_date, event_time, venue, image, description, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$title, $category, $date, $time, $venue, $image, $description, currentUserId()]);

    respond(['success' => true, 'id' => (int) $pdo->lastInsertId()], 201);
}

// ---------------------------------------------------------------
// DELETE /api/events.php?id=5  (admin only)
// ---------------------------------------------------------------
if ($method === 'DELETE') {
    requireAdmin();
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        respond(['error' => 'A valid event id is required.'], 422);
    }

    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
    $stmt->execute([$id]);

    respond(['success' => true]);
}

respond(['error' => 'Method not allowed'], 405);
