# Lernobjekte sind Kursaktivitäten in freigegebenen Kursbereichen

Lernobjekte (SCORM-Pakete, H5P-Lerneinheiten, Arbeitsblätter …) werden nicht in einer eigenen Datenbank wie dem Bildpool abgelegt, sondern als normale **Kursaktivitäten** in Kursen des Materialien-Hubs. Der Block durchsucht nur die in den Einstellungen ausgewählten **Kursbereiche** und **Aktivitätstypen**.

Gründe: Moodle spielt SCORM und H5P selbst ab, verwaltet Dateien, Versionen und Rechte; die Vorschau zeigt das Lernobjekt genau so, wie es später in der Schul-Instanz läuft. Schlagwörter sind Moodle-Tags der Aktivität, damit Lehrkräfte sie auch im normalen Bearbeitungsformular pflegen können.

- Die Regel „öffentliches Lernobjekt“ steht nur in `classes/object_scope.php`.
- Ohne ausgewählte Kursbereiche findet die Suche nichts.
- Name und Beschreibung liegen in der Tabelle des jeweiligen Aktivitätsmoduls; die Suche fragt je Aktivitätstyp ab. Nur Module mit den Feldern `name`, `intro`, `introformat` sind wählbar.

## Consequences

Ein Lernobjekt ist an einen Kurs gebunden. Ein Pool-Konto sieht in der Vorschau den ganzen Kurs; freigegebene Kursbereiche dürfen nur öffentliches Material enthalten.
