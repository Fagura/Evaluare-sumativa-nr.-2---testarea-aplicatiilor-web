<?php
require __DIR__ . '/db.php';
$ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DEFECT intenționat: serverul NU validează (validarea există doar în JavaScript)
    $st = $db->prepare('INSERT INTO inregistrari (nume, email, parola, varsta) VALUES (?, ?, ?, ?)');
    $st->execute([$_POST['nume'] ?? '', $_POST['email'] ?? '', $_POST['parola'] ?? '', $_POST['varsta'] ?? '']);
    // DEFECT intenționat: fără Post/Redirect/Get (F5 dublează înregistrarea) + nume afișat fără escapare (XSS)
    $ok = 'Înregistrare reușită! Bun venit, ' . ($_POST['nume'] ?? '');
}
?>
<!doctype html>
<html lang="ro"><head><meta charset="utf-8"><title>Înregistrare (vulnerabil)</title>
<link rel="stylesheet" href="assets/style.css"></head>
<body><div class="card">
<h2>Înregistrare utilizator</h2>
<div class="bad">Varianta VULNERABILĂ – doar pentru laborator, local.</div>
<?php if ($ok): ?><div class="ok"><?= $ok ?></div><?php endif; ?>
<form method="post" action="index.php" id="regForm" novalidate>
<label for="nume">Nume</label>
<input type="text" id="nume" name="nume" required minlength="2" maxlength="50"><div class="err" id="err-nume"></div>
<label for="email">E-mail</label>
<input type="email" id="email" name="email" required maxlength="100"><div class="err" id="err-email"></div>
<label for="parola">Parolă</label>
<input type="password" id="parola" name="parola" required minlength="8" maxlength="20"><div class="err" id="err-parola"></div>
<label for="confirm">Confirmare parolă</label>
<input type="password" id="confirm" name="confirm" required><div class="err" id="err-confirm"></div>
<label for="varsta">Vârsta</label>
<input type="number" id="varsta" name="varsta" min="16" max="99"><div class="err" id="err-varsta"></div>
<label><input type="checkbox" id="termeni" name="termeni"> Accept termenii și condițiile</label>
<div class="err" id="err-termeni"></div>
<button type="submit" class="p">Înregistrează</button>
<button type="reset" class="s">Resetează</button>
</form>
<p class="small"><a href="login.php">Autentificare</a></p>
</div>
<script>
// Validare DOAR în browser
var f = document.getElementById('regForm');
function err(n, m) { document.getElementById('err-' + n).textContent = m; }
f.addEventListener('submit', function (ev) {
  var bad = false;
  ['nume','email','parola','confirm','varsta','termeni'].forEach(function (n) { err(n, ''); });
  if (!/^[\p{L} \-]{2,50}$/u.test(f.nume.value)) { err('nume', 'Nume invalid (2-50 litere, spațiu, cratimă).'); bad = true; }
  if (!/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i.test(f.email.value) || f.email.value.length > 100) { err('email', 'E-mail invalid.'); bad = true; }
  var p = f.parola.value;
  if (p.length < 8 || p.length > 20 || !/[A-Z]/.test(p) || !/[0-9]/.test(p)) { err('parola', 'Parola: 8-20 caractere, o literă mare și o cifră.'); bad = true; }
  if (f.confirm.value !== p) { err('confirm', 'Parolele nu coincid.'); bad = true; }
  var a = parseInt(f.varsta.value, 10);
  if (isNaN(a) || a < 16 || a > 99) { err('varsta', 'Vârsta trebuie să fie între 16 și 99.'); bad = true; }
  if (!f.termeni.checked) { err('termeni', 'Trebuie să acceptați termenii.'); bad = true; }
  if (bad) ev.preventDefault();
});
</script>
</body></html>
