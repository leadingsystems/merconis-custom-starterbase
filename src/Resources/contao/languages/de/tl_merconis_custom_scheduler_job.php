<?php
/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['title'] = array('Bezeichnung');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['description'] = array('Beschreibung');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['active'] = array('Aktiv');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['cronExpression'] = array('Cron Expression (Zeitplan)', 'Hier muss der Zeitplan zur Ausführung des Jobs in der Cron-Expression-Syntax angegeben werden. Siehe hierzu https://crontab.guru/');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['scriptToExecute'] = array('Auszuführendes Script', 'Zur Auswahl stehen alle in diesem Projekt verfügbaren Klassen, welche den Trait "SchedulableTrait" verwenden. Der Scheduler wird zum Ausführungszeitpunkt die gewählte Klasse instanziieren und ihre "run"-Methode aufrufen.');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['currentlyRunning'] = array('Job wird zurzeit ausgeführt', 'manuelle Anpassung ist nur notwendig, wenn ein Job manuell abgebrochen wird oder er aufgrund eines PHP-Timeouts nicht beendet werden konnte.');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['tstampLastRun'] = array('Zuletzt ausgeführt');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['lastExecutionResult'] = array('Ergebnis der letzten Ausführung');

/*
 * Legends
 */
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['title_legend'] = 'Bezeichnung';
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['description_legend'] = 'Beschreibung';
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['execution_legend'] = 'Ausführung';

/*
 * Misc
 */
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['lastRunLabel'] = 'zuletzt ausgeführt am';
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['notYetRun'] = 'noch nie ausgeführt';
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['invalidCronExpressionErrorMessage'] = 'Die Cron Expression ist fehlerhaft. Siehe hierzu https://crontab.guru/';

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['new'] = array('Neuer Datensatz', 'Einen neuen Datensatz anlegen');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['edit'] = array('Datensatz bearbeiten', 'Datensatz mit ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['delete'] = array('Datensatz löschen', 'Datensatz mit ID %s löschen');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['copy'] = array('Datensatz kopieren', 'Datensatz mit ID %s kopieren');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['show'] = array('Datensatz anzeigen', 'Details des Datensatzes mit ID %s anzeigen');