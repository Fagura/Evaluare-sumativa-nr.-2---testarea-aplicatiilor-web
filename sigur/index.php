<?php
require __DIR__ . '/lib.php';

$errors = [];
$old = ['nume' => '', 'email' => '', 'varsta' => '', 'termeni' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { http_response_code(400); exit('Cerere invalidă (CSRF).'); }

    $in = [
        'nume'    => trim((string)($_POST['nume'] ?? '')),
        'email'   => trim((string)($_POST['email'] ?? '')),
        'parola'  => (string)($_POST['parola'] ?? ''),
        'confirm' => (string)($_POST['confirm'] ?? ''),
        'varsta'  => trim((string)($_POST['varsta'] ?? '')),
        'termeni' => isset($_POST['termeni']),
    ];
    // La eroare datele valide rămân completate, parolele NU se re-afișează
    $old = ['nume' => $in['nume'], 'email' => $in['email'], 'varsta' => $in['varsta'], 'termeni' => $in['termeni']];
    $errors = validate_registration($in);

    if (!$errors) {
        try {
            $st = db()->prepare('INSERT INTO utilizatori (nume, email, parola, varsta) VALUES (:n, :e, :p, :v)');
            $st->execute([
                ':n' => $in['nume'],
                ':e' => mb_strtolower($in['email']),
                ':p' => password_hash($in['parola'], PASSWORD_DEFAULT),
                ':v' => (int)$in['varsta'],
            ]);
            $_SESSION['flash'] = 'Înregistrare reușită! Bun venit, ' . $in['nume'] . '.';
            header('Location: index.php');   // Post/Redirect/Get: F5 nu mai retrimite formularul
            exit;
        } catch (PDOException $ex) {
            if (str_contains($ex->getMessage(), 'UNIQUE')) {
                $errors['email'] = 'Acest e-mail este deja înregistrat.';
            } else {
                error_log($ex->getMessage());
                $errors['general'] = 'Eroare internă. Încercați din nou.';
            }
        }
    }
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
function err(array $errors, string $k): string { return isset($errors[$k]) ? e($errors[$k]) : ''; }
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Înregistrare</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card">
  <h2>Înregistrare utilizator</h2>
  <?php if ($flash): ?><div class="ok"><?= e($flash) ?></div><?php endif; ?>
  <div class="err" id="err-general"><?= err($errors, 'general') ?></div>

  <form method="post" action="index.php" id="regForm" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <label for="nume">Nume</label>
    <input type="text" id="nume" name="nume" required minlength="2" maxlength="50" value="<?= e($old['nume']) ?>">
    <div class="err" id="err-nume"><?= err($errors, 'nume') ?></div>

    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" required maxlength="100" value="<?= e($old['email']) ?>">
    <div class="err" id="err-email"><?= err($errors, 'email') ?></div>

    <label for="parola">Parolă</label>
    <input type="password" id="parola" name="parola" required minlength="8" maxlength="20" autocomplete="new-password">
    <div class="err" id="err-parola"><?= err($errors, 'parola') ?></div>

    <label for="confirm">Confirmare parolă</label>
    <input type="password" id="confirm" name="confirm" required autocomplete="new-password">
    <div class="err" id="err-confirm"><?= err($errors, 'confirm') ?></div>

    <label for="varsta">Vârsta</label>
    <input type="number" id="varsta" name="varsta" required min="16" max="99" step="1" value="<?= e($old['varsta']) ?>">
    <div class="err" id="err-varsta"><?= err($errors, 'varsta') ?></div>

    <label><input type="checkbox" id="termeni" name="termeni" <?= $old['termeni'] ? 'checked' : '' ?>> Accept termenii și condițiile</label>
    <div class="err" id="err-termeni"><?= err($errors, 'termeni') ?></div>

    <button type="submit" class="p">Înregistrează</button>
    <button type="button" class="s" id="btnReset">Resetează</button>
  </form>
  <p class="small"><a href="login.php">Aveți deja cont? Autentificare</a></p>
</div>
<script src="assets/app.js"></script>
</body>
</html>
