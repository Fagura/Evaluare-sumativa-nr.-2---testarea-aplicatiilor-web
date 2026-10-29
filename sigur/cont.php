<?php
require __DIR__ . '/lib.php';
if (empty($_SESSION['uid'])) { header('Location: login.php'); exit; }
?>
<!doctype html>
<html lang="ro">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contul meu</title><link rel="stylesheet" href="assets/style.css"></head>
<body>
<div class="card">
  <h2>Contul meu</h2>
  <div class="ok">Bun venit, <?= e($_SESSION['nume']) ?>!</div>
  <form method="post" action="logout.php">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <button type="submit" class="s">Deconectare</button>
  </form>
</div>
</body>
</html>
