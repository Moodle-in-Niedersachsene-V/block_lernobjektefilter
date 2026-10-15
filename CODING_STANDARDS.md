# Coding Standards

Es gelten die mit den Vereinsentwicklern abgestimmten Coding Standards von Moodle in Niedersachsen e. V.:
[`security-checks/coding-standards/CODING_STANDARDS.md`](https://github.com/Moodle-in-Niedersachsene-V/security-checks/blob/main/coding-standards/CODING_STANDARDS.md)
(Quellenprüfung daneben in `quellen.md`). Das Repo ist privat; Zugriff über die Vereinsorganisation.

Reviews zitieren die Regel mit Nummer (`S2`, `N3`, `E4`).

## Geltungsbereich

- **Scopes:** `all`, `Moodle` und `AI`: die KI-Verschlagwortung schickt Inhalte von Lernobjekten an den KI-Anbieter von Moodle (`core_ai`). K-Regeln gelten für Prompt und Auswertung der Antwort (`classes/tag_suggester.php`).
- **Glossar für N1:** [`GLOSSARY.md`](GLOSSARY.md).
- **Datenbanken:** MariaDB, MySQL und PostgreSQL; nur DML-API mit Platzhaltern. Tabellennamen der Aktivitäten stammen ausschließlich aus der Positivliste `object_scope::modtypes()` (installierte Aktivitätsmodule).
- **Gate:** Die CI (`.github/workflows/moodle-ci.yml`) prüft phpcs, phpdoc, validate, savepoints, mustache, grunt und PHPUnit.

## Abweichungen

| Regel | Abweichung | Begründung |
|---|---|---|
| N3 (nur `lang/en/` ausliefern) | `lang/de/` wird mit ausgeliefert | Plugin für den Materialien-Hub des Vereins, wie Kursfilter (dort ADR 0001) |
