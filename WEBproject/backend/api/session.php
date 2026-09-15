<?php
require __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    respond(['user' => null]);
}

respond([
    'user' => [
        'id'   => $_SESSION['user_id'],
        'name' => $_SESSION['name'],
        'role' => $_SESSION['role'],
    ],
]);
