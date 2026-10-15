# Lernobjekte-Filter

Ein Block, mit dem Lehrkräfte im Materialien-Hub einzelne Lernobjekte finden und in der Vorschau ansehen. Ganze Kurse findet der Kursfilter, Testfragen der Fragenfilter.

## Materialien-Hub

**Materialien-Hub**:
Das Moodle des Vereins Moodle in Niedersachsen e. V., in dem Lehrkräfte Kurse, Lernobjekte und Testfragen zur Nachnutzung finden.
_Avoid_: Aufgabenpool, Vereins-Moodle

**Lernobjekt**:
Eine einzelne Kursaktivität zur Nachnutzung, z. B. ein SCORM-Paket, eine H5P-Lerneinheit oder ein Arbeitsblatt als Datei; Gegenstand des Lernobjekte-Filters.
_Avoid_: Lerneinheit (mehrdeutig), Material, Ressource
Code: `object` (`cmid` für die ID)

**Öffentliches Lernobjekt**:
Eine sichtbare Kursaktivität eines eingestellten Aktivitätstyps in einem sichtbaren Kurs eines eingestellten Kursbereichs. Nur öffentliche Lernobjekte erscheinen in der Suche und können angesehen werden.
Code: `public object` (`object_scope`)

## Suche

**Suchbereich**:
Die eingestellten Kursbereiche (wahlweise mit Unterbereichen) und Aktivitätstypen.
Code: `scope`

**Kursbereich**:
Ein Moodle-Kursbereich als Filter; nur eingestellte Kursbereiche sind wählbar.
_Avoid_: Kategorie
Code: `category`

**Format**:
Der Moodle-Aktivitätstyp eines Lernobjekts (SCORM-Paket, H5P, Datei …).
_Avoid_: Typ (mehrdeutig mit Lernmaterialtyp)
Code: `modtype`

**Schlagwort**:
Ein Wert aus einer Schlagwortliste der Einstellungen (Schulform, Fach, Niveaustufe, Lernmaterialtyp, zusätzliche Liste); am Lernobjekt als Tag der Aktivität gespeichert.
_Avoid_: Präfix-Tag
Code: `tag`

**Schlagwortliste**:
Eine Liste erlaubter Schlagwörter, je ein Wert pro Zeile; jede nicht leere Liste ist eine Chip-Reihe im Block.
Code: `filter group` (`filter_groups`)

**Lernmaterialtyp**:
Art des Materials, z. B. Arbeitsblatt, interaktive Übung, Erklärvideo.
Code: `material type` (`materialtype`)

**Suchbegriff**:
Freier Text, der in Name und Beschreibung des Lernobjekts gesucht wird.
Code: `search term` (`searchterm`)

## Besuch

**Besucher**:
Eine Person, die den Lernobjekte-Filter nutzt, mit oder ohne eigenes Moodle-Konto.
_Avoid_: Gast, Nutzer

**Vorschau**:
Ansehen eines Lernobjekts über ein Pool-Konto.
Code: `preview`

**Pool-Konto**:
Ein Moodle-Konto des Plugins, das genau ein Besucher für die Dauer einer Vorschau nutzt (belegt); danach wieder frei.
Code: `pool account`

**Pool-Rolle / Pool-Systemrolle**:
Kursrolle bzw. Systemrolle der Pool-Konten mit den gesperrten Capabilities.
Code: `ROLE_SHORTNAME` / `SYSTEM_ROLE_SHORTNAME`

## KI

**Schlagwortvorschlag**:
Die Schlagwörter, die die KI für ein Lernobjekt vorschlägt, mit Status `pending`, `applied`, `discarded` oder `empty`.
Code: `suggestion` (`tag_review`)
