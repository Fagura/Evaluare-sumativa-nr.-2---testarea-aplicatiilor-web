<?php
declare(strict_types=1);

header("Content-Security-Policy: default-src 'self'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Cookie de sesiune cu HttpOnly + SameSite (Secure se activează când folosiți HTTPS)
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']),
]);
session_start();

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $pdo = new PDO('sqlite:' . $dir . '/app.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE IF NOT EXISTS utilizatori (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nume TEXT NOT NULL, email TEXT NOT NULL UNIQUE,
        parola TEXT NOT NULL, varsta INTEGER NOT NULL,
        creat TEXT DEFAULT CURRENT_TIMESTAMP)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS incercari (
        id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, ip TEXT, ts INTEGER)');
    return $pdo;
}

/** Escapare la ieșire (anti-XSS) */
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

/** Validarea de pe SERVER, conform specificației din fișă. Returnează [câmp => mesaj]. */
function validate_registration(array $in): array {
    $err = [];
    $nume = $in['nume'] ?? '';
    if ($nume === '' || mb_strlen($nume) < 2 || mb_strlen($nume) > 50 || !preg_match('/^[\p{L} \-]+$/u', $nume))
        $err['nume'] = 'Nume invalid (2–50 caractere: litere, spațiu, cratimă).';

    $email = $in['email'] ?? '';
    if ($email === '' || mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $err['email'] = 'E-mail invalid (ex.: nume@domeniu.md, maximum 100 de caractere).';

    $p = $in['parola'] ?? '';
    if (strlen($p) < 8 || strlen($p) > 20 || !preg_match('/[A-Z]/', $p) || !preg_match('/\d/', $p))
        $err['parola'] = 'Parola: 8–20 de caractere, cel puțin o literă mare și o cifră.';

    if (($in['confirm'] ?? '') === '' || ($in['confirm'] ?? '') !== $p)
        $err['confirm'] = 'Parolele nu coincid.';

    $v = $in['varsta'] ?? '';
    if (!preg_match('/^\d+$/', $v) || (int)$v < 16 || (int)$v > 99)
        $err['varsta'] = 'Vârsta trebuie să fie un număr întreg între 16 și 99.';

    if (empty($in['termeni']))
        $err['termeni'] = 'Trebuie să acceptați termenii și condițiile.';
    return $err;
}

/** Limitarea încercărilor de autentificare: max 5 eșecuri / 15 min per e-mail+IP */
function prea_multe_incercari(string $email): bool {
    $st = db()->prepare('SELECT COUNT(*) FROM incercari WHERE email = :e AND ip = :ip AND ts > :t');
    $st->execute([':e' => $email, ':ip' => $_SERVER['REMOTE_ADDR'] ?? '', ':t' => time() - 900]);
    return (int)$st->fetchColumn() >= 5;
}
function noteaza_incercare(string $email): void {
    db()->prepare('INSERT INTO incercari (email, ip, ts) VALUES (:e, :ip, :t)')
        ->execute([':e' => $email, ':ip' => $_SERVER['REMOTE_ADDR'] ?? '', ':t' => time()]);
}
function sterge_incercari(string $email): void {
    db()->prepare('DELETE FROM incercari WHERE email = :e AND ip = :ip')
        ->execute([':e' => $email, ':ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
}
