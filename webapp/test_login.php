<?php
require_once 'includes/Database.php';

$username = 'admin';
$password = 'admin123';

// Neuen Hash erstellen
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "Neuer Hash: {$new_hash}\n";

// In DB eintragen
$db = new Database();
$db->query("UPDATE admin_users SET password_hash = ? WHERE username = ?", [$new_hash, $username]);
echo "Hash in DB aktualisiert\n";

// Sofort testen
$admin = $db->fetchOne("SELECT * FROM admin_users WHERE username = ?", [$username]);
echo "Passwort korrekt: " . (password_verify($password, $admin['password_hash']) ? "JA" : "NEIN") . "\n";
