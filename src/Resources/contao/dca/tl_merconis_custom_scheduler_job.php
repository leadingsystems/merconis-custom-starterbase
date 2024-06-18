<?php

namespace Merconis\CustomStarterbase;

use Contao\Backend;
use Contao\BackendUser;
use Contao\System;

$GLOBALS['TL_DCA']['tl_merconis_custom_scheduler_job'] = array(
	'config' => array(
		'dataContainer' => 'Table',
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),
	
	'list' => array(
		'sorting' => array(
			'mode' => 1,
			'flag' => 1,
			'fields' => array('title'),
			'disableGrouping' => false,
			'panelLayout' => 'filter;sort,search,limit'
		),
		
		'label' => array(
			'fields' => array('title'),
			'format' => '%s'
		),
		
		'global_operations' => array(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		
		'operations' => array(
			'edit' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
            'toggle' => array (
                'href'                => 'act=toggle&amp;field=active',
                'icon'                => 'visible.svg'
            ),
			'show' => array(
				'label'               => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		
		)	
	),
	
	'palettes' => array(
		'default' => '{title_legend},title;{description_legend},description;{execution_legend},active,cronExpression,tstampLastRun,scriptToExecute,currentlyRunning,lastExecutionResult'
	),
	
	'fields' => array(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'title' => array
		(
			'label' => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['title'],
			'exclude' => true,
			'search' => true,
			'sorting' => true,
			'flag' => 1,
			'inputType' => 'text',
			'eval' => array(
				'mandatory' => true,
				'maxlength' => 64,
				'tl_class'=>'w50'
			),
			'sql' => "varchar(64) NULL"
		),

		'description' => array
		(
			'label' => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['description'],
			'exclude' => true,
			'inputType' => 'textarea',
			'sql' => "text NULL"
		),

        'active' => [
            'label' => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['active'],
            'exclude' => true,
            'toggle' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => array('tl_class'=>'clr m12'),
            'sql' => "char(1) NOT NULL default ''"
        ],

		'cronExpression' => array
		(
			'label' => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['cronExpression'],
			'exclude' => true,
			'inputType' => 'text',
			'eval' => array(
				'mandatory' => true,
				'maxlength' => 64,
				'tl_class'=>'w50'
			),
			'sql' => "varchar(64) NULL"
		),

        'tstampLastRun' => [
            'label' => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['tstampLastRun'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'rgxp' => 'datim', 'datepicker' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'scriptToExecute' => array
        (
            'label' =>  &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['scriptToExecute'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'eval'					  => array('tl_class' => 'clr', 'includeBlankOption' => true),
            'sql'                     => "varchar(255) NULL"
        ),

        'currentlyRunning' => [
            'label' => &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['currentlyRunning'],
            'exclude' => true,
            'toggle' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => array('tl_class'=>'clr m12'),
            'sql' => "char(1) NOT NULL default ''"
        ],

        'lastExecutionResult' => [
            'label' =>  &$GLOBALS['TL_LANG']['tl_merconis_custom_scheduler_job']['lastExecutionResult'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'allowHtml' => true],
            'sql' => "longtext NULL"
        ],
	)
);