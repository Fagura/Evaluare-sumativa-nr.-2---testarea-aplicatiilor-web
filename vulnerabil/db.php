<?php
// ATENȚIE: aplicație VULNERABILĂ intenționat, doar pentru laborator. Rulează DOAR local.
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) { http_response_code(403); exit('Doar localhost.'); }

$dir = __DIR__ . '/data';
if (!is_dir($dir)) mkdir($dir, 0775, true);
$db = new PDO('sqlite:' . $dir . '/demo.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS utilizatori (id INTEGER PRIMARY KEY, utilizator TEXT, parola TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS inregistrari (id INTEGER PRIMARY KEY, nume TEXT, email TEXT, parola TEXT, varsta TEXT)");
if ((int)$db->query("SELECT COUNT(*) FROM utilizatori")->fetchColumn() === 0) {
    $db->exec("INSERT INTO utilizatori (utilizator, parola) VALUES ('admin', 'Parola123')");
}
