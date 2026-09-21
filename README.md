# FkFaq – Shopware FAQ Plugin

FAQs zentral verwalten, Produkten zuordnen und über ein Erlebniswelten-Element
auf beliebigen Seiten ausgeben. Für Shopware 6.7.

## Funktionsumfang

- FAQ-Verwaltung mit Mehrsprachigkeit, Rich-Text-Antworten und Aktiv-Schalter
- FAQ-Kategorien und FAQ-Tags als eigene Taxonomien
- Übersichtsliste im Shopware-Standard: Suche über Frage und Antwort,
  Inline-Bearbeitung, Filter nach Kategorie, Tags und Status
- Zuordnung am Produkt in drei Varianten, beliebig kombinierbar:
  - manuell, einzelne FAQs in frei wählbarer Reihenfolge
  - dynamisch über eine oder mehrere FAQ-Kategorien
  - dynamisch über einen oder mehrere FAQ-Tags
- Varianten erben die Zuordnung des Hauptprodukts, solange an der Variante
  selbst nichts hinterlegt ist
- Ausgabe auf der Produktseite als eigener Reiter oder unter der
  Produktbeschreibung, umschaltbar je Verkaufskanal
- Erlebniswelten-Element mit denselben drei Zuordnungsvarianten, dadurch
  FAQs auch auf Kategorie- und Landingpages
- Akkordeon ohne eigenes JavaScript, auf Basis der Bootstrap-Komponente des
  Themes – kein Storefront-Build nötig
- FAQPage-JSON-LD, abschaltbar, standardmäßig aus

## Geplant, noch nicht enthalten

Diese Punkte stehen in der Planung unter Phase 4 und sind bewusst noch nicht
umgesetzt:

- **MCP-Unterstützung**, damit FAQs als Ressourcen für KI-Werkzeuge und
  Automatisierungen bereitstehen
- **Domain-Events** (`FaqCreatedEvent` und Geschwister) für Drittentwickler

Store-API (lesend: Suche, Einzel-FAQ, Produkt-/Kategorie-/Tag-Zuordnung) und
ACL-Privilegien für die Produkt-Tab-Zuordnungsentities sind vorhanden;
schreibender Store-API-Zugriff ist bewusst nicht vorgesehen, die Verwaltung
läuft über die Admin-API. Die kompilierten Admin-Assets liegen im Repo, ein
`administration:build` beim Betreiber ist nicht mehr nötig (siehe
„Entwicklung" unten zur Pflicht, sie vor jedem Commit neu zu bauen).

Zur MCP-Unterstützung: Shopware 6.7 bringt die Infrastruktur bereits mit
(`shopware.store_api_mcp.tool` und Geschwister), sie ist dort aber als
experimentell markiert und laut Core erst ab 6.8 stabil. Ein Aufsatz darauf
wäre heute noch Bewegungsziel.

## Anforderungen

- Shopware 6.7.x mit Storefront-Bundle
- PHP 8.2 oder neuer

Das Plugin erweitert die Produktseite und die Erlebniswelten der Storefront.
Auf einer Installation ohne Storefront-Bundle ist es nicht lauffähig.

## Installation

```bash
bin/console plugin:refresh
bin/console plugin:install --activate FkFaq
bin/console assets:install
bin/console cache:clear
bin/console theme:compile
```

Die kompilierten Admin-Assets liegen bereits im Plugin (`src/Resources/public/administration/`),
ein eigener Build-Schritt entfällt beim Betreiber. `assets:install` verlinkt
bzw. kopiert sie nach `public/bundles/fkfaq/` – ohne diesen Schritt bleibt die
Administration bei 404 auf die Admin-JS-Dateien stehen, obwohl sie im Plugin
vorhanden sind.

## Konfiguration

Erweiterungen → FkFaq → Konfiguration, je Verkaufskanal einstellbar:

| Feld | Bedeutung | Standard |
|---|---|---|
| FAQ automatisch an Produktseiten anhängen | Schaltet die Ausgabe auf Produktseiten insgesamt | an |
| Position der FAQ-Sektion | Eigener Reiter oder unter der Produktbeschreibung | eigener Reiter |
| FAQPage-Schema generieren | JSON-LD nach schema.org/FAQPage | aus |
| Standard-Sortierung | Reihenfolge der dynamisch ermittelten FAQs | alphabetisch A–Z |

Zur Schema-Einstellung: Google zeigt FAQ-Rich-Results seit August 2023 nur
noch für Behörden- und Gesundheitsseiten. Für einen Shop bringt das Markup
derzeit keine Darstellung in der Google-Suche, andere Suchmaschinen und
maschinelle Leser werten es weiterhin aus. Deshalb vorhanden, aber aus.

## Zuordnung im Admin

**Am Produkt.** Reiter „FAQ" auf der Produktdetailseite. Einzelne FAQs,
FAQ-Kategorien und FAQ-Tags lassen sich kombinieren; eine Vorschau zeigt das
Ergebnis. Gespeichert wird über den normalen Produkt-Speichern-Button.

**In Erlebniswelten.** Element „FAQ" mit denselben drei Quellen, dazu eine
frei setzbare Überschrift. Die Auswahl gehört zum Layout: Kategorien, die
sich ein Layout teilen, zeigen dieselben FAQs.

**Reihenfolge der Ausgabe.** Manuell zugeordnete FAQs zuerst, in der
gepflegten Reihenfolge. Danach die über Kategorie oder Tag ermittelten,
nach der konfigurierten Sortierung. Doppelte werden entfernt.

## Templates anpassen

Die Ausgabe liegt in
`Resources/views/storefront/component/fk-faq/faq-accordion.html.twig` und ist
durchgehend in Twig-Blöcke gegliedert (`fk_faq_accordion`,
`fk_faq_accordion_item`, `fk_faq_accordion_item_question`,
`fk_faq_accordion_item_answer`). Ein Theme überschreibt einzelne Blöcke per
`sw_extends`, ohne das Plugin anzufassen.

Das Styling in `Resources/app/storefront/src/scss/base.scss` bleibt bewusst
dünn und überlässt Farben, Abstände und Typografie dem Theme.

## Entwicklung

```bash
composer install
vendor/bin/phpunit
vendor/bin/phpstan analyse src --level 8
```

`shopware/storefront` steht in `require-dev`, damit die statische Analyse die
Storefront-Klassen auflösen kann. Im laufenden Shop bringt die Installation
das Bundle mit.

**Admin-Assets vor jedem Commit neu bauen.** `src/Resources/public/administration/`
ist Teil des Repos (nicht ignoriert) und wird mit ausgeliefert, damit
`administration:build` beim Betreiber entfällt. Nach jeder Änderung an
`src/Resources/app/administration/src/` deshalb aus dem Shop-Root:

```bash
bin/build-administration.sh
```

Die erzeugten Dateien sind content-hashed (`fk-faq-<hash>.js`) und ändern
sich bei jedem Build. Vor dem Commit alte, nicht mehr referenzierte
Hash-Dateien aus `src/Resources/public/administration/assets/` entfernen,
damit sich dort nichts ansammelt, und `git status` prüfen, ob wirklich nur
die neuen Dateien hinzukommen. Wird dieser Schritt vergessen, bleiben Admin-
Modul und Produkt-Tab-Erweiterung serverseitig unverändert nutzbar, aber der
Betreiber sieht im Browser ein veraltetes oder fehlendes Bundle.

## Lizenz

MIT
