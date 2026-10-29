# Testarea aplicațiilor web – Evaluare sumativă nr. 2

Proiect pentru fișa de evaluare (Varianta I): formular de înregistrare + autentificare.
Disciplina: Testarea aplicațiilor web · IP Colegiul „Iulia Hasdeu” din Cahul · Grupa W2341.

| Folder | Conținut |
|---|---|
| `sigur/` | Aplicația corectă, conform specificației din fișă (validare în browser **și** pe server, PDO cu interogări pregătite, `password_hash`/`password_verify`, `htmlspecialchars`, CSRF, limitarea încercărilor, cookie `HttpOnly`/`SameSite`, Post/Redirect/Get). |
| `vulnerabil/` | Varianta **intenționat vulnerabilă** (SQL Injection, XSS, parole în clar, fără validare pe server, fără limitarea încercărilor). Doar pentru laborator; refuză orice acces care nu vine de pe `127.0.0.1`. |

## Cerințe
- PHP 8.0+ cu extensia `pdo_sqlite` (pe Windows: în `php.ini` activați `extension=pdo_sqlite` și `extension=sqlite3`).
- Visual Studio Code (opțional: extensia „PHP Server” nu este necesară).

## Pornire (în terminalul din VS Code, din rădăcina proiectului)
```bash
# Aplicația corectă
php -S 127.0.0.1:8000 -t sigur

# Aplicația vulnerabilă (în alt terminal / alt port)
php -S 127.0.0.1:8001 -t vulnerabil
```
Deschideți `http://127.0.0.1:8000` (sigur) sau `http://127.0.0.1:8001` (vulnerabil).
Baza de date SQLite se creează automat în `data/` (ignorată de Git).

## Specificația formularului (din fișă)
| Câmp | Cerințe |
|---|---|
| Nume | obligatoriu; 2–50 caractere; doar litere, spațiu, cratimă |
| E-mail | obligatoriu; format valid; maximum 100 de caractere |
| Parolă | obligatoriu; 8–20 caractere; cel puțin o literă mare și o cifră |
| Confirmare parolă | obligatoriu; identică cu parola |
| Vârsta | număr întreg între 16 și 99 |
| Termeni și condiții | căsuță obligatorie |
| Butoane | „Înregistrează” trimite; „Resetează” golește toate câmpurile |
| Mesaje | eroarea apare lângă câmpul greșit; datele valide rămân completate (în afară de parole) |

## Reguli etice
Testați **doar local** (127.0.0.1), cu payload-uri inofensive (ex. `' OR '1'='1' -- `, `alert('XSS')`).
Nu publicați `vulnerabil/` pe un server accesibil din internet.
