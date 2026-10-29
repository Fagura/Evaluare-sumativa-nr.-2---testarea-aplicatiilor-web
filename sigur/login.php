<?php
require __DIR__ . '/lib.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Cerere invalidă (CSRF).'); }
    $email  = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    $parola = (string)($_POST['parola'] ?? '');

    if (prea_multe_incercari($email)) {
        $msg = 'Prea multe încercări eșuate. Reîncercați peste 15 minute.';
    } else {
        // Interogare pregătită: intrarea utilizatorului nu ajunge niciodată în textul SQL
        $st = db()->prepare('SELECT id, nume, parola FROM utilizatori WHERE email = :e');
        $st->execute([':e' => $email]);
        $u = $st->fetch(PDO::FETCH_ASSOC);

        if ($u && password_verify($parola, $u['parola'])) {
            sterge_incercari($email);
            session_regenerate_id(true);          // anti session-fixation
            $_SESSION['uid'] = (int)$u['id'];
            $_SESSION['nume'] = $u['nume'];
            header('Location: cont.php');
            exit;
        }
        noteaza_incercare($email);
        $msg = 'E-mail sau parolă incorecte.';     // mesaj generic, fără a reflecta datele introduse
    }
}
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Autentificare</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card">
  <h2>Autentificare</h2>
  <?php if ($msg): ?><div class="bad"><?= e($msg) ?></div><?php endif; ?>
  <form method="post" action="login.php">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" required maxlength="100">
    <label for="parola">Parola</label>
    <input type="password" id="parola" name="parola" required maxlength="20" autocomplete="current-password">
    <button type="submit" class="p">Autentificare</button>
  </form>
  <p class="small"><a href="index.php">Creează cont</a></p>
</div>
</body>
</html>
