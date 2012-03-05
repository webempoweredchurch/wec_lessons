<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::allowTableOnStandardPages('tx_weclesson_lesson');
t3lib_extMgm::addToInsertRecords('tx_weclesson_lesson');

$TCA['tx_weclesson_lesson'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_lesson',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		/* 'sortby' => 'sorting',*/
		'default_sortby' => 'ORDER BY class_id ASC,num ASC',
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_weclesson_lesson.gif',
	),
);

t3lib_extMgm::allowTableOnStandardPages('tx_weclesson_class');
t3lib_extMgm::addToInsertRecords('tx_weclesson_class');

$TCA['tx_weclesson_class'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_class',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_weclesson_class.gif',
	),
);

t3lib_extMgm::allowTableOnStandardPages('tx_weclesson_course');
t3lib_extMgm::addToInsertRecords('tx_weclesson_course');

$TCA['tx_weclesson_course'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_course',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_weclesson_course.gif',
	),
);

t3lib_extMgm::allowTableOnStandardPages('tx_weclesson_user_data');

$TCA['tx_weclesson_user_data'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:wec_lesson/locallang_db.xml:tx_weclesson_user_data',		
		'label'     => 'user_id',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_weclesson_user_data.gif',
	),
);



t3lib_div::loadTCA('tt_content');
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,recursive";

t3lib_extMgm::addPlugin(Array("LLL:EXT:wec_lesson/locallang_db.xml:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");

$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"] = "pi_flexform";
t3lib_extMgm::addPiFlexFormValue($_EXTKEY."_pi1", "FILE:EXT:wec_lesson/flexform_ds.xml");

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:wec_lesson/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/ts/', "WEC Lesson & Classes Template");

if (TYPO3_MODE=="BE") $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_weclesson_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_weclesson_pi1_wizicon.php';

?>