# Security Policy

## Reporting a Vulnerability

Wenn du ein Sicherheitsproblem in FkFaq gefunden hast, melde es bitte **nicht öffentlich** über die Issues.

**Stattdessen schreib direkt an:** fabian.kuenzel@gmail.com

Bitte füge folgende Informationen bei:
- Beschreibung des Sicherheitsproblems
- Reproduzierbare Schritte (wenn möglich)
- Betroffene Versionen
- Mögliche Auswirkungen

Wir werden dich so schnell wie möglich kontaktieren und ein Fix-Plan erstellt.

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 1.0.x   | ✅ Ja              |
| < 1.0   | ❌ Nein            |

## Security Measures

FkFaq implementiert folgende Sicherheitsmaßnahmen:

- ✅ HTML-Sanitization gegen XSS
- ✅ CSRF-Protection (Shopware Standard)
- ✅ SQL-Injection Protection (Doctrine ORM)
- ✅ Input Validation auf allen API-Endpoints
- ✅ Regelmäßige Dependency Updates

Danke für dein Vertrauen in die Sicherheit des Plugins!
