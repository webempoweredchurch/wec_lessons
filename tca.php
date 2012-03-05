<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_weclesson_lesson'] = array (
	'ctrl' => $TCA['tx_weclesson_lesson']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,num,title,overview,description,class_id,content,video_files,audio_files,resource_files,next_lessons'
	),
	'feInterface' => $TCA['tx_weclesson_lesson']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(3, 14, 7, 1, 19, 2038),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'num' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.num',
			'config' => Array (
				'type' => 'input',	
				'size' => '4',	
				'max' => '10',
			)
		),		
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '60',
			)
		),
		'class_id' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.class_id',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_weclesson_class',	
				'foreign_table_where' => 'ORDER BY tx_weclesson_class.sorting',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'overview' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.overview',		
			'config' => array (
				'type' => 'input',
				'size' => '50',	
				'max' => '100',
			)
		),		
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '50',
				'rows' => '3',
			)
		),		
		'content' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.content',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => array(
					'_PADDING' => 2,
					'RTE' => array(
						'notNewRecords' => 1,
						'RTEonly'       => 1,
						'type'          => 'script',
						'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon'          => 'wizard_rte2.gif',
						'script'        => 'wizard_rte.php',
					),
				),
			)
		),
		'video_files' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.video_files',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	
				'disallowed' => 'php,php3',	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_weclesson/', 
				'size' => 2,
				'minitems' => 0,
				'maxitems' => 5,
			)
		),
		'audio_files' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.audio_files',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	
				'disallowed' => 'php,php3',	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_weclesson',
				'size' => 2,	
				'minitems' => 0,
				'maxitems' => 5,
			)
		),
		'resource_files' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.resource_files',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	
				'disallowed' => 'php,php3',	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_weclesson',
				'size' => 4,	
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'next_lessons' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson.next_lessons',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_weclesson_lesson',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 5,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'num, title;;;;2-2-2, class_id;;;;3-3-3, overview, description, content;;;richtext[]:rte_transform[mode=ts], video_files, audio_files, resource_files, next_lessons, hidden;;1;;1-1-1')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime')
	)
);


$TCA['tx_weclesson_class'] = array (
	'ctrl' => $TCA['tx_weclesson_class']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,num,title,description,course_id,classes_required,image'
	),
	'feInterface' => $TCA['tx_weclesson_class']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(3, 14, 7, 1, 19, 2038),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'num' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_class.num',
			'config' => Array (
				'type' => 'input',	
				'size' => '4',	
				'max' => '10',
			)
		),		
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_class.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '100',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_class.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),		
		'course_id' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_class.course_id',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_weclesson_course',	
				'foreign_table_where' => 'ORDER BY tx_weclesson_course.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'classes_required' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_class.classes_required',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_weclesson_class',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_class.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 200,
				'uploadfolder' => 'uploads/tx_weclesson',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),		
	),
	'types' => array (
		'0' => array('showitem' => 'num,title;;;;2-2-2, description, course_id;;;;3-3-3, classes_required, image, hidden;;1;;1-1-1')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime, fe_group')
	)
);



$TCA['tx_weclesson_course'] = array (
	'ctrl' => $TCA['tx_weclesson_course']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,title,difficulty,required_courses, image'
	),
	'feInterface' => $TCA['tx_weclesson_course']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(3, 14, 7, 1, 19, 2038),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '100',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),		
		'difficulty' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.difficulty',		
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.difficulty.I.0', '0'),
					array('LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.difficulty.I.1', '1'),
					array('LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.difficulty.I.2', '2'),
					array('LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.difficulty.I.3', '3'),
				),
				'size' => 1,	
				'maxitems' => 1,
			)
		),
		'required_courses' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.required_courses',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_weclesson_course',	
				'foreign_table_where' => 'ORDER BY tx_weclesson_course.uid',	
				'size' => 5,	
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 200,
				'uploadfolder' => 'uploads/tx_weclesson',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),		
	),
	'types' => array (
		'0' => array('showitem' => 'title;;;;2-2-2, difficulty;;;;3-3-3, required_courses, image, hidden;;1;;1-1-1')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime, fe_group')
	)
);


$TCA['tx_weclesson_user_data'] = array (
	'ctrl' => $TCA['tx_weclesson_user_data']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'user_id,current_course,current_lesson,lesson_history,tstamp'
	),
	'feInterface' => $TCA['tx_weclesson_user_data']['feInterface'],
	'columns' => array (
		'user_id' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data.user_id',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'fe_users',	
				'foreign_table_where' => 'ORDER BY fe_users.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'current_course' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data.current_course',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_wecclass_course',	
				'foreign_table_where' => 'ORDER BY tx_wecclass_course.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'current_course' => array (
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data.current_class',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_wecclass_class',	
				'foreign_table_where' => 'ORDER BY tx_wecclass_class.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),		
		'current_lesson' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data.current_lesson',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'tx_wecclass_lesson',	
				'foreign_table_where' => 'ORDER BY tx_wecclass_lesson.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'lesson_completed' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data.lesson_completed',		
			'config' => array (
				'type' => 'check',
				'default' => '0',
			)
		),		
		'class_completed' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data.class_completed',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '100',
			)
		),		
		'lesson_history' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data.lesson_history',		
			'config' => array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_wecclass_lesson',	
				'size' => 10,	
				'minitems' => 0,
				'maxitems' => 500,
			)
		),
		'class_grade' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data.class_grade',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'max' => '100',
			)
		),		
	),
	'types' => array (
		'0' => array('showitem' => 'user_id;;;;1-1-1, current_course, current_lesson, lesson_history, class_history, course_history')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);


?>