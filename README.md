# php4dbf

**Lightweight PHP library for reading and writing DBF files**, with optional memo support, locking, and file-based microservice architecture.

## 🔧 Features

- Native DBF read/write access (FoxPro, Clipper, dBase III+)
- Memo support via external files (not FPT)
- Record locking mechanism (multi-user safe)
- `.req/.result` microservice protocol
- Designed for fast server-side access (Apache, PHP-FPM)
- No SQL server required

## 💡 Use case

Designed to power legacy systems and modern microservices where DBF files still play a role.

## 📁 Example usage

```php
$db = new DbfReader("data/kunden.dbf");
$records = $db->getAll(["KNAME", "STRASSE"]);
