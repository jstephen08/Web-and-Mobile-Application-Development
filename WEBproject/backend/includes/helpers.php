<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

/** Send a JSON response and stop execution. */
function respond($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/** Read and decode a JSON request body sent via fetch(). */
function readJsonBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Return the logged-in user's id, or null if not logged in. */
function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/** Return the logged-in user's role, or null if not logged in. */
function currentUserRole(): ?string {
    return $_SESSION['role'] ?? null;
}

/** Stop the request with 401 unless a user is logged in. */
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        respond(['error' => 'You must be logged in.'], 401);
    }
}

/** Stop the request with 403 unless the logged-in user is an admin. */
function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        respond(['error' => 'Admin access required.'], 403);
    }
}
