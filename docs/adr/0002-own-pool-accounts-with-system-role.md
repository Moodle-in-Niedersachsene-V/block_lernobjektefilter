# Eigene Pool-Konten mit zusätzlicher Systemrolle

Die Vorschau nutzt denselben Mechanismus wie Kursfilter und Fragenfilter (ADR 0002 im Kursfilter): eine feste Zahl Pool-Konten, von denen ein Besucher für die Dauer der Vorschau genau eines belegt. Der Lernobjekte-Filter hat **eigene** Pool-Konten (`lernobjekte_poolNN`) und Rollen, damit er ohne den Kursfilter installiert und deinstalliert werden kann.

Zusätzlich zur Kursrolle erhalten die Pool-Konten eine **Systemrolle**, die Profil-, Kennwort-, Mitteilungs-, Datei-, Blog- und Dashboard-Änderungen verbietet. Eine Kursrolle allein wirkt nur im Kurskontext; Profil und Kennwort werden im System- bzw. Nutzerkontext geprüft. Ohne Systemrolle könnte ein Besucher die E-Mail-Adresse eines Pool-Kontos ändern und es über „Kennwort vergessen“ übernehmen.

## Consequences

Auf dem Materialien-Hub gibt es je Such-Block einen eigenen Pool. Neue Moodle-Capabilities mit Personenbezug oder Schreibwirkung müssen in beiden Rollen geprüft und gegebenenfalls gesperrt werden.
