# Beiträge zu FkFaq

Danke für dein Interesse, zu FkFaq beizutragen! Dieses Dokument erklärt den Prozess.

## Wie kann ich beitragen?

### Bug-Reports
Wenn du einen Bug gefunden hast:
1. Prüfe die [existierenden Issues](https://github.com/fkuenzel/FkFaq/issues)
2. Öffne ein neues Issue mit:
   - Aussagekräftige Überschrift
   - Detaillierte Beschreibung des Problems
   - Reproduzierbare Schritte
   - Erwartetes vs. aktuelles Verhalten
   - Shopware & PHP Version

### Feature-Requests
1. Öffne ein Issue mit Label `enhancement`
2. Beschreibe den Use-Case und Mehrwert
3. Skizziere mögliche Implementierung (optional)

### Pull Requests
Wir begrüßen PRs! Beachte folgende Richtlinien:

## Entwicklungs-Setup

```bash
# Clone & Setup
git clone https://github.com/fkuenzel/FkFaq.git
cd FkFaq

# Dependencies installieren
composer install

# Tests & Linting
vendor/bin/phpunit
vendor/bin/phpstan analyse src --level 8
```

## Code-Standards

- **PHP:** PSR-12 Coding Style
- **Namespacing:** `fKuenzel\Faq\...`
- **Testing:** 75%+ Coverage required
- **PHPStan:** Level 8, 0 Fehler

Vor jedem PR:
```bash
vendor/bin/phpcs --standard=PSR12 src/
vendor/bin/phpstan analyse src --level 8
vendor/bin/phpunit
```

## PR-Prozess

1. **Fork & Branch**
   ```bash
   git checkout -b feature/beschreibung
   ```

2. **Code schreiben**
   - Atomare, logische Commits
   - Aussagekräftige Commit-Messages
   - Tests schreiben für neue Features

3. **Testen**
   ```bash
   vendor/bin/phpunit
   vendor/bin/phpstan analyse src --level 8
   composer audit
   ```

4. **Push & Pull Request**
   - Beschreibe was, warum und wie
   - Referenziere relevante Issues
   - Screenshot/GIF für UI-Änderungen

5. **Code Review**
   - Feedback wird konstruktiv gegeben
   - Änderungen werden gemeinsam durchdiskutiert
   - Merging bei Approval

## Commit Message Format

```
Prädikat: Kurzbeschreibung (max 50 Zeichen)

Längere Erklärung bei Bedarf (max 72 Zeichen pro Zeile)
- Punkt 1
- Punkt 2

Fixes #123
```

**Prädikate:**
- `feat:` – Neue Features
- `fix:` – Bugfixes
- `docs:` – Dokumentation
- `refactor:` – Code-Umstrukturierung
- `test:` – Tests hinzufügen/ändern
- `chore:` – Abhängigkeiten, Config, etc.

## Licensing

Mit jedem PR akzeptierst du, dass dein Code unter der MIT-Lizenz verfügbar wird.

## Verhalten

- Respektvolle, konstruktive Kommunikation
- Keine Diskriminierung oder Belästigung
- Unterschiedliche Meinungen sind ok – wir lernen voneinander

## Support & Kontakt

- **Issues:** [GitHub Issues](https://github.com/fkuenzel/FkFaq/issues)
- **Email:** fabian.kuenzel@gmail.com
- **Discussions:** [GitHub Discussions](https://github.com/fkuenzel/FkFaq/discussions)

Danke für deinen Beitrag!
