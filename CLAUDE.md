# Lernobjekte-Filter – CLAUDE.md

Moodle-Block `block_lernobjektefilter` von Moodle in Niedersachsen e. V.: Suche nach einzelnen Lernobjekten (Kursaktivitäten) in ausgewählten Kursbereichen nach Format, Schlagwörtern und Suchbegriff, dazu Vorschau über Pool-Konten und KI-Verschlagwortung direkt über Ollama oder die Claude API (`ai_connector`, wie im Fragenfilter). Schwester-Plugin von `block_kursfilter`.

- **Stack:** PHP-Plugin für Moodle 5.1+ (`version.php`), AMD-Modul in `amd/src/`, Mustache-Templates.
- **GitHub:** `Moodle-in-Niedersachsene-V/block_lernobjektefilter`
- **Ziel:** Materialien-Hub des Vereins.

## Wichtige Dateien

| Datei/Ordner | Zweck |
|---|---|
| `classes/object_scope.php` | einzige Regel „öffentliches Lernobjekt“ (Suche, Vorschau, Pool, KI) |
| `classes/filter_groups.php` | Schlagwortlisten = Positivliste für Suche und KI-Antwort |
| `GLOSSARY.md` | Domain-Glossar mit Code-Schreibweise |
| `docs/adr/` | Architekturentscheidungen |
| `CODING_STANDARDS.md` | Verweis auf die Vereinsstandards |

## Aufgabenhandling

- Vor dem Schreiben oder Reviewen von Code [`CODING_STANDARDS.md`](CODING_STANDARDS.md) lesen.
- Vor Funktionsänderungen alle Aufrufer suchen.
- Testen: siehe [`docs/agents/testing.md`](docs/agents/testing.md).

## Git/gh-Workflow

`main` = veröffentlichter Stand. Änderungen über Branch und PR. Force-Push, History-Rewrite und Branch-Löschung nur nach Rückfrage.
