<?php

header('Content-Type: text/plain');

$accounts = [
    'admin@nsbm.ac.lk'          => 'admin123',
    'kamal@student.nsbm.ac.lk'  => 'student123',
    'nimali@student.nsbm.ac.lk' => 'student123',
];

echo "Run these in phpMyAdmin (SQL tab) to set working passwords:\n\n";
foreach ($accounts as $email => $plainPassword) {
    $hash = password_hash($plainPassword, PASSWORD_BCRYPT);
    $safeEmail = str_replace("'", "\\'", $email);
    echo "UPDATE users SET password_hash = '{$hash}' WHERE email = '{$safeEmail}';\n";
}

echo "\nLogin credentials (unchanged):\n";
foreach ($accounts as $email => $plainPassword) {
    echo "  {$email} / {$plainPassword}\n";
}
