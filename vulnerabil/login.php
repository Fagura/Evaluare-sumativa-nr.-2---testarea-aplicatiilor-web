<?php
require __DIR__ . '/db.php';
$msg = ''; $cls = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['utilizator'] ?? '';
    $p = $_POST['parola'] ?? '';
    // Fragmentul din fișă (vulnerabil): concatenare în SQL, parolă în clar
    $q = "SELECT * FROM utilizatori WHERE utilizator = '$u' AND parola = '$p'";
    try {
        $r = $db->query($q)->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            setcookie('sesiune', 'user=' . $r['utilizator']);   // fără HttpOnly / Secure
            $msg = "Bun venit, " . $_POST['utilizator'];          // fără htmlspecialchars (XSS)
            $cls = 'ok';
        } else {
            $msg = "Parola gresita pentru utilizatorul " . $_POST['utilizator'];
            $cls = 'bad';
        }
    } catch (PDOException $e) {
        $msg = 'Eroare SQL: ' . $e->getMessage() . '<br>Interogare: ' . $q;   // dezvăluire de informații
        $cls = 'bad';
    }
    // fără limitarea încercărilor de autentificare
}
?>
<!doctype html>
<html lang="ro"><head><meta charset="utf-8"><title>Autentificare (vulnerabil)</title>
<link rel="stylesheet" href="assets/style.css"></head>
<body><div class="card">
<h2>Autentificare</h2>
<div class="bad">Varianta VULNERABILĂ – doar pentru laborator, local.</div>
<?php if ($msg): ?><div class="<?= $cls ?>"><?= $msg ?></div><?php endif; ?>
<form method="post" action="login.php">
<label for="utilizator">Utilizator</label><input type="text" id="utilizator" name="utilizator">
<label for="parola">Parola</label><input type="password" id="parola" name="parola">
<button type="submit" class="p">Autentificare</button>
</form>
<p class="small">Cont de test: admin / Parola123 · <a href="index.php">Înregistrare</a></p>
</div></body></html>
