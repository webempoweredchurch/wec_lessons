<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_weclesson_lesson=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_weclesson_course=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_weclesson_class=1
');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_weclesson_pi1.php', '_pi1', 'list_type', 1);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecjournal']['setHiddenFields'][] = 'EXT:wec_lesson/hooks/class.tx_weclesson_hooks.php:&tx_weclesson_hooks->setHiddenFields';

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['wec_lesson'] = 'EXT:wec_lesson/hooks/class.tx_weclesson_realurl.php:&tx_weclesson_realurl->addRealURLConfig';

?>