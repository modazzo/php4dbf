# php4dbf

**php4dbf** ist eine moderne, modulare PHP-Bibliothek für das Lesen, Schreiben und Bearbeiten von DBF-Dateien (dBASE, Clipper, FoxPro, Xbase++), inklusive Locking, Memo-Support, Logging und Microservice-Anbindung – **ohne SQL-Zwang**.

---

## ✨ Highlights

- ✅ Kompatibel mit dBASE III+, Clipper, FoxPro, Xbase++
- ✅ Locking via `.lock` + `.prot` Datei (netzwerksicher)
- ✅ GET/PUT/UPDATE über `.req`/`.result`-Mikroprotokoll möglich
- ✅ Unterstützt UTF-8, Datumsfelder, Zahlen, Memo-Typen
- ✅ Kein SQL-Server nötig, keine Abhängigkeit von ODBC
- ✅ Zero-Trust-ready (Cloudflare Tunnel, 2FA)
- ✅ Optionale Flush-Funktion (`flush.exe`) für sichere Schreibzugriffe

---

## 🛠 Beispiel: DBF öffnen & lesen

```php
include 'php4dbf.php';

$db = php4dbf_DBUse('kunden.dbf');
echo "Datensätze: " . $db['recordCount'] . "\n";

$ds = php4dbf_getRecordByIndex($db['fileHandle'], 0, $db['header'], $db['fields']);
print_r($ds);

🔐 Sicherheit & Infrastruktur
Portlose Kommunikation mit ReadDirectoryChangesW()-Trigger

Absicherung über Cloudflare Access möglich

.req-basierte Architektur: keine offenen Ports, kein VPN notwendig

Perfekt für Microservices oder Legacy-Backend-API

📁 Struktur (Work in Progress)
php4dbf.php → Hauptbibliothek

examples/ → Anwendungsbeispiele (z. B. readdbf.php, update.php)

docs/ → technische Dokumentation (geplant)

tools/ → Helferprogramme wie flush.exe (geplant)

📦 Einsatzgebiete
Migration bestehender DBF-Anwendungen ins Web

Automatisierte Verarbeitung von .req-Dateien

Integration mit Web-Oberflächen (Vue.js, Bootstrap, Plain PHP)

Backoffice-Schnittstellen, Reporting, Prüfwerkzeuge

📅 Projektstatus
Aktive Entwicklung seit 2024.
Die Bibliothek wird produktiv in mehreren Anwendungen eingesetzt.
Ziel ist ein robustes, wartbares DBF-Framework für die Zukunft.

📄 Lizenz
MIT License
(c) 2025 Otto A. / modazzo
