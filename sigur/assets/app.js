// Validare în browser (confort pentru utilizator). Validarea reală se face pe server (lib.php).
(function () {
  var f = document.getElementById('regForm');
  if (!f) return;
  var campuri = ['nume', 'email', 'parola', 'confirm', 'varsta', 'termeni'];
  function err(n, m) { var el = document.getElementById('err-' + n); if (el) el.textContent = m || ''; }

  function valideaza() {
    var ok = true;
    campuri.forEach(function (n) { err(n, ''); });
    function bad(n, m) { err(n, m); ok = false; }

    var nume = f.nume.value.trim();
    if (nume.length < 2 || nume.length > 50 || !/^[\p{L} \-]+$/u.test(nume))
      bad('nume', 'Nume invalid (2–50 caractere: litere, spațiu, cratimă).');

    var email = f.email.value.trim();
    if (!/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/.test(email) || email.length > 100)
      bad('email', 'E-mail invalid (ex.: nume@domeniu.md, maximum 100 de caractere).');

    var p = f.parola.value;
    if (p.length < 8 || p.length > 20 || !/[A-Z]/.test(p) || !/[0-9]/.test(p))
      bad('parola', 'Parola: 8–20 de caractere, cel puțin o literă mare și o cifră.');

    if (f.confirm.value !== p || f.confirm.value === '')
      bad('confirm', 'Parolele nu coincid.');

    var v = f.varsta.value.trim();
    if (!/^\d+$/.test(v) || parseInt(v, 10) < 16 || parseInt(v, 10) > 99)
      bad('varsta', 'Vârsta trebuie să fie un număr întreg între 16 și 99.');

    if (!f.termeni.checked) bad('termeni', 'Trebuie să acceptați termenii și condițiile.');
    return ok;
  }

  f.addEventListener('submit', function (ev) { if (!valideaza()) ev.preventDefault(); });

  // „Resetează” golește TOATE câmpurile și mesajele (type="reset" ar reveni la valorile re-afișate de server)
  var r = document.getElementById('btnReset');
  if (r) r.addEventListener('click', function () {
    ['nume', 'email', 'parola', 'confirm', 'varsta'].forEach(function (n) { f[n].value = ''; });
    f.termeni.checked = false;
    campuri.forEach(function (n) { err(n, ''); });
    var g = document.getElementById('err-general'); if (g) g.textContent = '';
  });
})();
