<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * German language strings for block_lernobjektefilter.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @author    Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['ai_backend_claude']                     = 'Anthropic Claude API';
$string['ai_backend_ollama']                     = 'Ollama (eigener Server)';
$string['back_to_search']                        = 'Zurück zur Suche';
$string['btn_apply']                             = 'Übernehmen';
$string['btn_discard']                           = 'Verwerfen';
$string['btn_preview']                           = 'Ansehen';
$string['btn_reset']                             = 'Zurücksetzen';
$string['error_ai_answer_invalid']               = 'Die Antwort der KI ist nicht das erwartete JSON: {$a}';
$string['error_ai_request_failed']               = 'Die KI-Anfrage ist fehlgeschlagen: {$a}';
$string['error_ai_tagging_failed']               = 'Die KI-Verschlagwortung ist für {$a->failed} von {$a->total} Lernobjekten fehlgeschlagen; die Ursachen stehen im Task-Protokoll.';
$string['error_ai_unavailable']                  = 'Die KI-Verschlagwortung ist aktiviert, aber das KI-Backend ist nicht eingerichtet: Bitte in den Einstellungen des Blocks den Claude-API-Schlüssel oder die Ollama-URL eintragen.';
$string['error_invalidcategory']                 = 'Dieser Kursbereich gehört nicht zur Lernobjekt-Suche.';
$string['error_invalidmodtype']                  = 'Dieser Aktivitätstyp gehört nicht zur Lernobjekt-Suche.';
$string['error_invalidtag']                      = 'Mindestens ein Schlagwort steht nicht in den eingestellten Schlagwortlisten.';
$string['error_preview_needs_post']              = 'Eine Vorschau lässt sich nur über die Schaltfläche „Ansehen“ des Lernobjekte-Filters starten.';
$string['filter_all']                            = '– Alle –';
$string['hint_notconfigured']                    = 'Die Lernobjekt-Suche ist noch nicht eingerichtet: Bitte in den Einstellungen des Blocks Kursbereiche und Aktivitätstypen auswählen.';
$string['hint_setfilter']                        = 'Filter setzen, um Lernobjekte zu suchen.';
$string['label_category']                        = 'Kursbereich';
$string['label_category_help']                   = 'Sucht nur in diesem Kursbereich und seinen Unterbereichen. Die Liste zeigt nur die Kursbereiche, die für die Lernobjekt-Suche freigegeben sind.';
$string['label_custom']                          = 'Weitere Schlagwörter';
$string['label_custom_help']                     = 'Schlagwörter der zusätzlichen Liste, die die Administration eingerichtet hat. Ein Lernobjekt passt, wenn es ein Tag mit genau diesem Wert trägt.';
$string['label_level']                           = 'Niveaustufe';
$string['label_level_help']                      = 'Jahrgang oder Stufe, für die das Lernobjekt gedacht ist. Ein Lernobjekt passt, wenn es ein Tag mit genau diesem Wert trägt.';
$string['label_materialtype']                    = 'Lernmaterialtyp';
$string['label_materialtype_help']               = 'Art des Materials, z. B. Arbeitsblatt, interaktive Übung oder Erklärvideo. Ein Lernobjekt passt, wenn es ein Tag mit genau diesem Wert trägt.';
$string['label_modtype']                         = 'Format';
$string['label_modtype_help']                    = 'Moodle-Aktivitätstyp des Lernobjekts, z. B. SCORM-Paket, H5P oder Datei. Angezeigt werden nur die für die Lernobjekt-Suche freigegebenen Typen.';
$string['label_schooltype']                      = 'Schulform';
$string['label_schooltype_help']                 = 'Schulform, für die das Lernobjekt gedacht ist. Ein Lernobjekt passt, wenn es ein Tag mit genau diesem Wert trägt.';
$string['label_searchterm']                      = 'Suchbegriff';
$string['label_searchterm_help']                 = 'Freier Text, der im Namen und in der Beschreibung der Lernobjekte gesucht wird; Groß- und Kleinschreibung spielt keine Rolle.';
$string['label_subject']                         = 'Fach';
$string['label_subject_help']                    = 'Unterrichtsfach des Lernobjekts. Ein Lernobjekt passt, wenn es ein Tag mit genau diesem Wert trägt.';
$string['lernobjektefilter:addinstance']         = 'Block „Lernobjekte-Filter“ hinzufügen';
$string['lernobjektefilter:myaddinstance']       = 'Block „Lernobjekte-Filter“ zum Dashboard hinzufügen';
$string['lernobjektefilter:reviewtags']          = 'KI-Schlagwortvorschläge für Lernobjekte prüfen';
$string['lernobjektefilter:search']              = 'Lernobjekte mit dem Lernobjekte-Filter suchen';
$string['object_not_found']                      = 'Dieses Lernobjekt wurde nicht gefunden oder ist nicht öffentlich zugänglich.';
$string['placeholder_searchterm']                = 'Name oder Beschreibung suchen …';
$string['pluginname']                            = 'Lernobjekte-Filter';
$string['pool_account_description']              = 'Pool-Konto des Blocks Lernobjekte-Filter für Vorschauen.';
$string['pool_account_firstname']                = 'Pool-Konto';
$string['pool_full']                             = 'Aktuell sind alle Pool-Konten belegt. Bitte versuchen Sie es in einigen Minuten erneut.';
$string['preview_title']                         = 'Lernobjekt wie eine Lehrkraft ohne Bearbeitungsrecht ansehen';
$string['privacy:metadata']                      = 'Der Lernobjekte-Filter speichert Schlagwortvorschläge je Lernobjekt, aber keine personenbezogenen Daten von Nutzer/innen. Für die KI-Verschlagwortung sendet er Unterrichtsinhalte der Lernobjekte (Name, Typ, Beschreibung, Inhaltsauszug, Kurs, Abschnitt, Kursbereich), keine personenbezogenen Daten, an das eingestellte KI-Backend (Ollama-Server oder Anthropic Claude API).';
$string['ratelimitexceeded']                     = 'Zu viele Suchanfragen. Bitte {$a->seconds} Sekunden warten.';
$string['results_count']                         = 'Gefundene Lernobjekte: {$a}';
$string['results_none']                          = 'Keine Lernobjekte gefunden.';
$string['review_applied']                        = 'Die Schlagwörter wurden als Tags übernommen.';
$string['review_col_action']                     = 'Aktion';
$string['review_col_course']                     = 'Kurs';
$string['review_col_object']                     = 'Lernobjekt';
$string['review_col_tags']                       = 'Vorgeschlagene Schlagwörter';
$string['review_discarded']                      = 'Der Vorschlag wurde verworfen.';
$string['review_empty']                          = 'Es warten keine Schlagwortvorschläge auf eine Prüfung.';
$string['review_heading']                        = 'Schlagwortvorschläge für Lernobjekte prüfen';
$string['review_link']                           = 'Offene Schlagwortvorschläge prüfen';
$string['role_pool_description']                 = 'Kursrolle der Pool-Konten des Lernobjekte-Filters: wie Trainer/in ohne Bearbeitungsrecht, aber ohne Teilnehmerliste, Nutzeridentität, Noten und verborgene Inhalte und ohne Beiträge, Kommentare oder Bewertungen.';
$string['role_pool_name']                        = 'Lernobjekte-Filter-Pool-Rolle';
$string['role_pooluser_description']             = 'Systemrolle der Pool-Konten des Lernobjekte-Filters: verbietet das Ändern von Profil, Kennwort, Mitteilungseinstellungen, privaten Dateien, Blog, Badges, Kalendereinträgen und Dashboard, das Senden von Mitteilungen und alle Funktionen des Mail-Plugins local_mail. Pool-Sitzungen werden außerdem von persönlichen Seiten weggeleitet.';
$string['role_pooluser_name']                    = 'Lernobjekte-Filter-Pool-Konto';
$string['settings_ai_autoapply']                 = 'Schlagwörter automatisch übernehmen';
$string['settings_ai_autoapply_help']            = 'Wenn aktiviert, werden passende Werte sofort zu Tags des Lernobjekts. Wenn deaktiviert, warten sie als Vorschläge auf der Prüfseite.';
$string['settings_ai_backend']                   = 'KI-Backend';
$string['settings_ai_backend_desc']              = 'Wählen Sie, ob die Schlagwortvorschläge von einem Ollama-Server oder von der Anthropic Claude API kommen. Das Plugin spricht das Backend direkt an (wie der Fragenfilter); das KI-System von Moodle wird nicht genutzt. Die Anfragen laufen über das curl von Moodle mit den Sicherheitseinstellungen der Website: Ein Ollama-Server auf localhost oder einer internen Adresse bzw. auf Port 11434 muss unter Website-Administration > Allgemein > Sicherheit > HTTP-Sicherheit freigegeben sein.';
$string['settings_ai_backend_heading']           = 'KI-Backend';
$string['settings_ai_backend_help']              = 'Ollama läuft auf einem eigenen Server. Die Claude API erfordert einen Anthropic-API-Schlüssel.';
$string['settings_ai_batch_size']                = 'Lernobjekte pro Lauf';
$string['settings_ai_batch_size_help']           = 'Anzahl der Lernobjekte, die pro Lauf der geplanten Aufgabe verschlagwortet werden (Standard: 20, max. 200).';
$string['settings_ai_claude_apikey']             = 'Claude API-Schlüssel';
$string['settings_ai_claude_apikey_help']        = 'Ihr Anthropic-API-Schlüssel (beginnt mit „sk-ant-“). Er wird maskiert angezeigt und nur für die KI-Anfragen dieses Plugins verwendet.';
$string['settings_ai_claude_desc']               = 'API-Schlüssel und Modell für die Anthropic Claude API. Übermittelt werden nur Inhalte der Lernobjekte, keine personenbezogenen Daten.';
$string['settings_ai_claude_heading']            = 'Claude API-Einstellungen';
$string['settings_ai_claude_model']              = 'Claude-Modell';
$string['settings_ai_claude_model_help']         = 'Modellbezeichnung, z. B. claude-haiku-4-5-20251001. Haiku ist schnell und kostengünstig für die Verschlagwortung.';
$string['settings_ai_desc']                      = 'Die geplante Aufgabe fragt das eingestellte KI-Backend (Ollama oder Claude API) einmal je Lernobjekt, welche Schlagwörter passen. Übermittelt werden Name, Aktivitätstyp, Beschreibung, ein Auszug des Inhalts (z. B. Glossarbegriffe, Seitentext, Kapitelüberschriften, Dateinamen), Kursname und -beschreibung, Abschnitt und Kursbereich, nie personenbezogene Daten. Übernommen werden nur Werte aus den Schlagwortlisten.';
$string['settings_ai_enabled']                   = 'KI-Verschlagwortung aktivieren';
$string['settings_ai_enabled_help']              = 'Erlaubt der geplanten Aufgabe „KI-Verschlagwortung der Lernobjekte“ zu laufen; die Aufgabe muss zusätzlich in den geplanten Aufgaben aktiviert sein.';
$string['settings_ai_heading']                   = 'KI-Verschlagwortung';
$string['settings_ai_ollama_heading']            = 'Ollama-Einstellungen';
$string['settings_ai_ollama_model']              = 'Ollama-Modell';
$string['settings_ai_ollama_model_help']         = 'Name des Ollama-Modells, z. B. gemma3:4b oder llama3.2. Das Modell muss auf dem Ollama-Server verfügbar sein.';
$string['settings_ai_ollama_url']                = 'Ollama-URL';
$string['settings_ai_ollama_url_help']           = 'Basis-URL des Ollama-Servers, z. B. http://localhost:11434, ohne abschließenden Schrägstrich.';
$string['settings_categories']                   = 'Kursbereiche';
$string['settings_categories_help']              = 'Lernobjekte werden nur in diesen Kursbereichen gesucht. Ohne Auswahl findet die Suche nichts.';
$string['settings_customlabel']                  = 'Name der zusätzlichen Schlagwortliste';
$string['settings_customlabel_help']             = 'Überschrift der zusätzlichen Schlagwortliste im Block, z. B. Thema oder Kompetenz.';
$string['settings_customvalues']                 = 'Zusätzliche Schlagwortliste';
$string['settings_customvalues_help']            = 'Ein Schlagwort pro Zeile. Leer lassen, um die Liste auszublenden.';
$string['settings_includesubcategories']         = 'Unterbereiche einschließen';
$string['settings_includesubcategories_help']    = 'Wenn aktiviert, werden auch die Unterbereiche der ausgewählten Kursbereiche durchsucht.';
$string['settings_levels']                       = 'Niveaustufen';
$string['settings_levels_help']                  = 'Eine Niveaustufe pro Zeile, z. B. Klasse 5-6. Leer lassen, um die Liste auszublenden.';
$string['settings_materialtypes']                = 'Lernmaterialtypen';
$string['settings_materialtypes_help']           = 'Ein Lernmaterialtyp pro Zeile, z. B. Arbeitsblatt. Leer lassen, um die Liste auszublenden.';
$string['settings_modtypes']                     = 'Aktivitätstypen';
$string['settings_modtypes_help']                = 'Kursaktivitäten dieser Typen gelten als Lernobjekte, z. B. SCORM-Paket, H5P, Datei oder Textseite.';
$string['settings_pool_desc']                    = 'Besucher sehen Lernobjekte in der Vorschau über ein Pool-Konto an. Fehlende Pool-Konten legt die geplante Aufgabe an und schreibt sie ein (täglich um 03:30 Uhr).';
$string['settings_pool_heading']                 = 'Pool-Konten';
$string['settings_poolsize']                     = 'Anzahl der Pool-Konten';
$string['settings_poolsize_help']                = 'Anzahl der Pool-Konten und damit gleichzeitiger Vorschauen (Standard: 10, max. 50).';
$string['settings_resultlimit']                  = 'Max. Ergebnisse';
$string['settings_resultlimit_help']             = 'Maximale Anzahl Lernobjekte pro Suchanfrage (Standard: 100, max. 200).';
$string['settings_schooltypes']                  = 'Schulformen';
$string['settings_schooltypes_help']             = 'Eine Schulform pro Zeile, z. B. Gymnasium. Leer lassen, um die Liste auszublenden.';
$string['settings_scope_desc']                   = 'Welche Kursaktivitäten der Block findet: sichtbare Aktivitäten der ausgewählten Typen in sichtbaren Kursen der ausgewählten Kursbereiche.';
$string['settings_scope_heading']                = 'Suchbereich';
$string['settings_subjects']                     = 'Fächer';
$string['settings_subjects_help']                = 'Ein Fach pro Zeile, z. B. Mathematik. Leer lassen, um die Liste auszublenden.';
$string['settings_tags_desc']                    = 'Jede nicht leere Liste erscheint als Reihe von Chips im Block. Ein Lernobjekt passt zu einem Wert, wenn es ein Tag mit genau diesem Wert trägt (Tags der Aktivität); Groß- und Kleinschreibung spielt keine Rolle.';
$string['settings_tags_heading']                 = 'Schlagwörter';
$string['task_setup_pool']                       = 'Pool-Konten des Lernobjekte-Filters anlegen und in die Kurse mit Lernobjekten einschreiben';
$string['task_tag_objects']                      = 'KI-Verschlagwortung der Lernobjekte für den Lernobjekte-Filter';
