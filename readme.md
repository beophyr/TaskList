# Custom Task List
* Each task can be a combination of important/urgent/fast
* Sorting by [Eisenhower Matrix] (https://en.wikipedia.org/wiki/Time_management#The_Eisenhower_Method) (+ fast option)
* Multiple users (completely seperate, no sharing for now)
* Later: Extension with APIs

## Used libraries
* Material Design Lite: https://getmdl.io/started/
* jQuery: https://jquery.com/
* jQuery TableSorter: http://tablesorter.com/docs/
* Dialog polyfill: https://github.com/GoogleChrome/dialog-polyfill
* Google Fonts API: https://fonts.google.com/
* HTML5 Boilerplate v5.0 http://h5bp.com/ 

# ToDo 
## Roadmap
* ~~V0.1 sortierbare Tabelle (3 Spalten, 10 Zeilen) mittig auf der Seite mit MDLite und jquery darstellen~~
* ~~V0.2 10 Task Objekte mit zufallsdaten erstellen und in der Tabelle anzeigen. Vorläufig nur Attribute id, Name, wichtig, dringend~~
* ~~V0.3 add Button anzeigen, Klick öffnet Formular zum Erstellen eines weiteren Task Objektes. Absenden des Formulars fügt es der Tabelle hinzu.~~
* V0.4 Backend zum Speichern der Daten (REST Api über eine URL)
  * POST: 
    * Falls Attribut id leer: neues Objekt anlegen  
    * Falls Attribut id vorhanden: bestehendes Objekt editieren (falls gegebene Id nicht existiert, Fehler)  
    * Antwort: HTTP:200 + erstellte/editierte id oder HTTP:400 + message, dass Id nicht vorhanden ist   
 * GET:
   * Alle vorhandenen Tasks als JSON zur Verfügung stellen   
* V0.5 Klick auf Task objekt öffnet Detail Ansicht (selbe wie Formular, bloß read-only). Klick auf Bearbeiten Button erlaubt editieren. Klick auf senden schickt Daten zum Server, dieser editiert das vorhandene Task objekt. Tabelle aktualisiert sich mit neuem Server json.
* V0.6 weitere Attribute Unterstützen: string DetailText, bool kurz, bool erledigt, time erstellt, time erledigt, string kategorie. D.h. in Formular, auf Server und in Tabelle
* V0.7 Spalte Priorität hinzufügen, welche sich aus Spalten wichtig, dringend, kurz berechnet. Option erledigte Tasks auszublenden oder ans Ende zu sortieren.
* V0.8 sekundäre Sortierung immer nach Kategorie oder durch ntzer wählbar?
* V0.9 Icons hinzufügen
* ?V0.10 mehrere Benutzer durch verschiedene json Dateien unterstützen

# Problems
## Wichtig
* Add Button klappt nicht mobil (polyfill schlägt fehl?)
* Formular
 * checkboxen werden nicht korrekt ausgelesen (mdl setzt :checken nicht richtig?)
 * reset funktioniert manchmal nicht mit boxen
 * klick auf label statt box, klappt manchmal nicht

## Medium
* Breite an Mobil anpassen
 * Karte soll fullscreen, so dass kein platz am rand verschwendet wird
* Farben anpassen
* About Info erstellen
* ? Für mobile: add class "mdl-button--mini-fab" to add butto`?
* clicking add button, shows white shadow on blue background for ripple effect
* Resetting form, deletes MDL hint inside text form --> clear manually (or use mdl?)
