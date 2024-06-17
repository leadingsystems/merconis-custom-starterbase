<?php
/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['title'] = array('Title');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['description'] = array('Description');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['active'] = array('Active');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['cronExpression'] = array('Cron Expression (Zeitplan)', 'The schedule for executing the job must be specified here in cron expression syntax. See https://crontab.guru/');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['scriptToExecute'] = array('Script to be executed', 'All classes available in this project that use the "SchedulableTrait" trait are available for selection. The scheduler will instantiate the selected class at execution time and call its "run" method.');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['currentlyRunning'] = array('Job is currently running', 'Manual adjustment is only necessary if a job was canceled manually or if it could not be completed due to a PHP timeout.');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['tstampLastRun'] = array('Last executed');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['lastExecutionResult'] = array('Result of the last execution');

/*
 * Legends
 */
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['title_legend'] = 'Title';
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['description_legend'] = 'Description';
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['execution_legend'] = 'Execution';

/*
 * Misc
 */
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['lastRunLabel'] = 'last executed on';
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['notYetRun'] = 'never executed before';
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['misc']['invalidCronExpressionErrorMessage'] = 'The cron expression is invalid. See https://crontab.guru/';

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['new'] = array('New record', 'Add a new record');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['edit'] = array('Edit record', 'Edit record with ID %s');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['delete'] = array('Delete record', 'Delete record with ID %s');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['copy'] = array('Copy record', 'Copy record with ID %s');
$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['show'] = array('Show details', 'Show details of record with ID %s');
