<?php

class tx_weclesson_hooks {

	function setHiddenFields(&$hiddenFields, &$obj) {
		// if already filled in topic, then return
		if (($obj->curEntry && $obj->curEntry['topic'])) {
			return;
		}
		
		// determine curLesson and add as hidden field for topic
		$setTopic = 0;
		
		// grab userdata...if available...and determine last lesson on
		// determine pid_list
		$pids = $GLOBALS['TSFE']->getStorageSiterootPids();
		$pid_list = $obj->conf['pid_list'] ? $obj->conf['pid_list'] : ($pids['_STORAGE_PID'] ? $pids['_STORAGE_PID'] : $GLOBALS['TSFE']->id);
		$userDataTable = 'tx_weclesson_user_data';
		$where = 'pid IN (' . $pid_list . ')';
		$where .= ' AND user_id=' . $GLOBALS['TSFE']->fe_user->user['uid'] . ' AND deleted=0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $userDataTable, $where, '', 'tstamp DESC');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res),$where);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		// now grab lesson name, if available
		if ($lsn = $row['current_lesson']) {
			$where = 'uid=' . $lsn;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_weclesson_lesson', $where, '', 'tstamp DESC');
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			
			$setTopic = $row['title'];
		}
		if (!$setTopic) {
			$setTopic = '%m/%d/%Y';
			$setTopic = strftime($setTopic,mktime());
		}
		$hiddenFields['curtopic'] = $setTopic;
	}
	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_lesson/hooks/class.tx_weclesson_hooks.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_lesson/hooks/class.tx_weclesson_hooks.php']);
}

?>