# Lernobjekte-Filter (`block_lernobjektefilter`)

Moodle-Block von Moodle in Niedersachsen e. V. für den Materialien-Hub: Lehrkräfte finden einzelne **Lernobjekte** – SCORM-Pakete, H5P-Lerneinheiten, Arbeitsblätter (Dateien), Textseiten, Bücher, Links usw. – nach Kursbereich, Format, Schulform, Fach, Niveaustufe, Lernmaterialtyp und Suchbegriff und sehen sie über ein Pool-Konto in der Vorschau an. Gebaut nach dem Vorbild von `block_kursfilter`. Begriffe: [GLOSSARY.md](GLOSSARY.md).

## Voraussetzungen

- Moodle 5.1 oder neuer, PHP 8.2–8.4
- Datenbank: MariaDB, MySQL oder PostgreSQL
- Für die KI-Verschlagwortung: ein KI-Anbieter mit der Aktion „Text generieren“ (Website-Administration > Allgemein > KI)

## Was ist ein Lernobjekt?

Eine **sichtbare Kursaktivität** eines eingestellten Aktivitätstyps in einem **sichtbaren Kurs** eines eingestellten **Kursbereichs** (wahlweise mit Unterbereichen). Die Regel steht an einer Stelle: `classes/object_scope.php`. Ohne ausgewählte Kursbereiche findet die Suche nichts (sichere Voreinstellung).

## Funktionen und Endpunkte

| Funktion | Weg | Zugriff |
|---|---|---|
| Suche | Web-Service `block_lernobjektefilter_search_objects` | Moodle-Sitzung und Capability `block/lernobjektefilter:search` (Standard: Gast, angemeldete Nutzer); Rate-Limit 30 Anfragen/60 s je Konto bzw. je Gast-Sitzung |
| Vorschau | `preview.php`, nur POST mit Sesskey | öffentlich, nur für Lernobjekte im Suchbereich; meldet ein freies Pool-Konto an und leitet zur Aktivität |
| Prüfseite KI-Vorschläge | `review.php` | Capability `block/lernobjektefilter:reviewtags` (Standard: Manager), Aktionen nur POST mit Sesskey |

Jeder Filter im Block hat ein Hilfe-Symbol (blauer Kreis mit Fragezeichen) mit Erklärung.

## Einstellungen

| Abschnitt | Einstellung | Standard |
|---|---|---|
| Suchbereich | `categories` – Kursbereiche (Mehrfachauswahl) | keine |
| | `includesubcategories` – Unterbereiche einschließen | an |
| | `modtypes` – Aktivitätstypen | Buch, Verzeichnis, H5P, IMS-Content, Lektion, Textseite, Datei, SCORM, Link |
| | `resultlimit` – max. Ergebnisse | 100 (max. 200) |
| Schlagwörter | `schooltypes`, `subjects`, `levels`, `materialtypes` – je ein Wert pro Zeile | Vorbelegung |
| | `customlabel` + `customvalues` – zusätzliche, frei benannte Liste (z. B. „Thema“) | leer = ausgeblendet |
| Pool-Konten | `poolsize` | 10 (max. 50) |
| KI-Verschlagwortung | `ai_enabled`, `ai_autoapply`, `ai_batch_size` | aus, aus, 20 |

Ein Lernobjekt passt zu einem Schlagwort, wenn es ein **Tag der Aktivität** (Moodle-Tagbereich „Aktivitäten“) mit genau diesem Wert trägt; Groß- und Kleinschreibung spielt keine Rolle. Leere Listen erscheinen nicht im Block. Der Web-Service lehnt Schlagwörter, Kursbereiche und Aktivitätstypen ab, die nicht eingestellt sind.

## Vorschau und Pool-Konten

Wie bei Kursfilter und Fragenfilter: Besucher ohne eigenes Konto erhalten für die Vorschau ein freies Pool-Konto (`lernobjekte_pool01` …). Eine echte Anmeldung wird nie ersetzt. Die Pool-Konten haben zwei Rollen:

- **Kursrolle** `lernobjektefilter_pool` (Archetyp Trainer/in ohne Bearbeitungsrecht) – verboten: Teilnehmerliste, Nutzeridentität, alle Noten, verborgene Aktivitäten/Abschnitte, Forenbeiträge, Kommentare, Bewertungen.
- **Systemrolle** `lernobjektefilter_pooluser` – verboten: Profil, Kennwort, Mitteilungseinstellungen, private Dateien, Mitteilungen senden, Blog, Dashboard bearbeiten. Damit kann niemand ein Pool-Konto übernehmen (z. B. über eine geänderte E-Mail-Adresse) oder Inhalte für die nächste Person hinterlassen.

Hinweis: Ein Pool-Konto sieht in der Vorschau den **ganzen Kurs** des Lernobjekts. Kursbereiche für die Lernobjekt-Suche sollten deshalb nur Material enthalten, das öffentlich sein darf.

## Geplante Aufgaben

- 03:30 `setup_pool`: legt fehlende Pool-Konten an und schreibt sie in alle Kurse mit Lernobjekten ein.
- 04:30 `tag_objects` (standardmäßig aus, zusätzlich Einstellung `ai_enabled`): fragt den KI-Anbieter je Lernobjekt einmal nach passenden Schlagwörtern. Übermittelt werden nur Name, Aktivitätstyp, Kursname, Kursbereich und der Anfang der Beschreibung – keine Personendaten. Übernommen werden nur Werte aus den Schlagwortlisten; ohne „automatisch übernehmen“ warten sie auf der Prüfseite.

## Installation

ZIP über Website-Administration > Plugins > Plugin installieren hochladen (oder nach `blocks/lernobjektefilter` entpacken) und das Upgrade ausführen. Danach in den Block-Einstellungen Kursbereiche und Aktivitätstypen auswählen und den Block auf der Startseite des Materialien-Hubs hinzufügen. Tags an Aktivitäten müssen im Tagbereich „Aktivitäten“ aktiviert sein (Standard).

## Entwicklung

Coding Standards: [CODING_STANDARDS.md](CODING_STANDARDS.md). Tests und Build: [docs/agents/testing.md](docs/agents/testing.md). `amd/build/` wird auf dem Build-Server erzeugt (`npx grunt amd`).

## Lizenz

GNU GPL v3 oder später – Moodle in Niedersachsen e. V.
