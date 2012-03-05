<?php
/***********************************************************************
* Copyright notice
*
* (c) 2010 Christian Technology Ministries International Inc.
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
* International (http://CTMIinc.org). The WEC is developing TYPO3-based
* (http://typo3.org) free software for churches around the world. Our desire
* is to use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
*
* You can redistribute this file and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation;

* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
*************************************************************************/
/**
 * Plugin 'WEC Lessons & Classes' for the 'wec_lesson' extension.
 *
 * @author	Web-Empowered Church Team <lessons@webempoweredchurch.org>
 * @package	TYPO3
 * @subpackage	tx_weclesson
*
*  DESCRIPTION:
*
*/

/*
	@todo: 
		- only supports a student taking one class/lesson. If want multiple classes/lessons, then need to extend userData saved & add selection of which class+lesson to do...
		- add ability to be notified by email when can take next lesson

*/

require_once(PATH_tslib.'class.tslib_pibase.php');


class tx_weclesson_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_weclesson_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_weclesson_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'wec_lesson';	// The extension key.
	var $pi_checkCHash = true;
	var $cObj;			// The backReference to the mother cObj object set at call time

	var $lessonTable 	= 'tx_weclesson_lesson';
	var $classTable 	= 'tx_weclesson_class';
	var $courseTable 	= 'tx_weclesson_course';
	var $userDataTable 	= 'tx_weclesson_user_data';

	var $config;		// configuration of flexform + TypoScript
	var $view;			// view to show
	var $templateCode;	// the template file
	var $formErrorText; // errors in form text
	var $db_fields;		// database fields (for processing)
	var $marker;		// global marker array
	var $subpartMarker;	// global subpart marker array
	var $pid_list;		// storage page id(s) list
	var $isAdmin; 		// if is administrator
	var $userID;		// id of user
	var $userName;		// name of user

	var $lessonList;	// list of lessons in sequential order
	var $classList;		// list of classes in sequential order
	var	$courseList;	// list of courses in sequential order
	var $userData;		// user data for given user
	var $curLesson;		// current lesson user is on
	var $curClass;		// current class user is in
	var $curCourse;		// current course user is in [optional]
	var $latestLesson;	// latest lesson in userdata
	
	var $lessonWaitHours;// how many hours to wait until can do next lesson

	/**
	 * All needed configuration values are stored in the member variable $this->arrConfig and the template code goes in $this->arrConfig['templateCode'] .
	 *
	 * @param	array		Configuration array from TS
	 * @return	void
	 */
	function init($conf) {
		if (!$this->cObj) $this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->conf = $conf; // Storing configuration as a member var
		$this->pi_loadLL();
		
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm();		// Initialize the FlexForms array
		$this->config = $this->setFFconfig(); // Set the Flexform and TypoScript values

		$this->userID = $GLOBALS['TSFE']->fe_user->user['uid'];
		$this->userName = $GLOBALS['TSFE']->fe_user->user['username'];

		// add CSS file
		$GLOBALS['TSFE']->additionalHeaderData['wec_lesson'] .= '<link href="' . t3lib_extMgm::siteRelPath('wec_lesson') . 'res/wec_lesson.css' . '" rel="stylesheet" type="text/css" />';

		// set if admin
		if ($this->userID && ($admins = $this->config['admin_user_list'])) {
			$adminList = t3lib_div::trimExplode(',', $admins);
			foreach ($adminList as $thisAdmin) {
				if (($thisAdmin == $this->userID) || ($thisAdmin == $this->userName)) {
					$this->isAdmin = 1;
					break;
				}
			}
		}		

		// determine pid list
		$pids = $GLOBALS['TSFE']->getStorageSiterootPids();
		if ($this->cObj->data['pages']) {
			$this->pid_list = $this->cObj->data['pages'];
		}
		else if ($this->conf['pid_list']) // or specify in TypoScript
			$this->pid_list = $this->conf['pid_list'];
		else if ($pids['_STORAGE_PID']) {
			$this->pid_list = $pids['_STORAGE_PID'];
		}
		else {
			$this->pid_list = $GLOBALS['TSFE']->id;	// the default is the current page
		}
		
		// determine which view to show
		$this->view = $this->config['view'] ? $this->config['view'] : 'LESSON';
		// set up template
		$this->templateCode = $this->config['templateCode'];

		// set wait time based on flexform/TS value
		$waitT = $this->config['advanceLessons'];
		if (!strcmp($waitT,'tillGraded')) {		 $waitT = -1; }
		else if (!strcmp($waitT,'immediate')) {	 $waitT = 0;  }
		else if (substr($waitT,0,4) == "wait") { $waitT = (int) substr($waitT,4); }
		else {									 $waitT = 0;  }
		$this->lessonWaitHours = (int) $waiT;
if ($this->piVars['admin'] >= 1) {
	$this->view = 'ADMIN';
}
		// Load course, class, and lesson data as well as existing user data
		$this->loadData();

		// set current user data
		if (count($this->userData)) {
			reset($this->userData);
			$this->latestLesson = current($this->userData);
			$this->curClass = $this->latestLesson['current_class'] ? $this->classList[$this->latestLesson['current_class']] : 0;
			$this->curLesson = $this->latestLesson['current_lesson'] ? $this->lessonList[$this->latestLesson['current_lesson']] : 0;
			// update some values
			if ($this->curLesson)
				$this->curLesson['lesson_completed'] = $this->latestLesson['lesson_completed'];
			if ($this->curClass)
				$this->curClass['lesson_history'] = $this->latestLesson['lesson_history'];
		}
				
		// Determine current lesson, class, and course
		//==================================================================================
		// LOGIC:
		//  - if end of lesson, then mark completed.
		//	- if come from clicking a start course/start class button => go there no matter what
		//  - if come from clicking a start lesson button => go there
		//  - if come from choosing lesson or class (from dropdown or other way) => go there
		//  - if load user data...if available => load current lesson / class
		//  - if no course known, then let choose course
		//  - if no class known, then let choose class
		//  - if no lesson known, then let choose lesson
		//
		
		// if end of lesson, then update
		if ($lsnNum = $this->piVars['elsn']) {
			// go to next lesson...if can
			$lsn = $this->lessonList[$lsnNum];
			$doAdvance = true;
			if ($this->lessonWaitHours) {
				// save time
				$saveData['lesson_completed'] = mktime();
			}
			else {
				$lsn = $this->getNextLesson($lsn);
				if ($lsn) {
					$this->piVars['lsn'] = $lsn['uid'];
					$saveData['current_lesson'] = $lsn['uid'];
					$this->curLesson = $lsn;
				}
				else { // end of class
					$saveData['class_completed'] = mktime();
				}
			}
			// record in userdata that done
			$this->saveUserData($saveData);			

			// jump to new lesson using userData info
			unset($this->piVars['elsn']);
			if ($lsn)
				$paramArray['lsn'] = $lsn['uid'];
			$gotoURL = $this->pi_linkTP_keepPIvars_url($paramArray, 1);			

			header('Location: '.t3lib_div::locationHeaderUrl($gotoURL));
		}

//		if (isset($this->piVars['cls']) && $this->piVars['cls'] === 0) {
//			$this->view = 'CLASS_LIST';
//		}

		// #1 If PASSED IN Start Course or Start Class....from start xyz button
		if ($crs = $this->piVars['startcrs']) {
			$this->curCourse = $this->courseList[$crs];
			$this->curClass = 0;
			$this->curLesson = 0;
		}
		else if ($cls = $this->piVars['startcls']) {
			$this->curClass = $this->classList[$cls];
			$this->curLesson = 0;
		}
		// #2 if PASSED IN Course or Class or Lesson....
		//    choose lesson or class or course from dropdown...
		if (!$this->curCourse && ($crs = $this->piVars['crs'])) {
			if ($crs >= 0)
				$this->curCourse = $this->courseList[$crs];
			else 
				$this->view = 'COURSE_LIST';
		}
		if (!$this->curClass && ($cls = $this->piVars['cls'])) {
			if ($cls >= 0)
				$this->curClass = $this->classList[$cls];
			else
				$this->view = 'CLASS_LIST';
		}
		if ($lsn = $this->piVars['lsn']) {
			if ($lsn >= 0)
				$this->curLesson = $this->lessonList[$lsn];
			else
				$this->view = 'LESSON_LIST';
		}
		// @todo Error handling -- what if set course, class, or lesson by hand and is invalid?

		// #3 if load user data, then load current lesson / class
		// use user data (if available) if class or lesson not set
		if (!$this->curLesson && !$this->curClass && count($this->userData)) {
			reset($this->userData);
			$this->latestLesson = current($this->userData);
			$this->curClass = $this->latestLesson['current_class'] ? $this->classList[$this->latestLesson['current_class']] : 0;
			$this->curLesson = $this->latestLesson['current_lesson'] ? $this->lessonList[$this->latestLesson['current_lesson']] : 0;
//			if ($latestLesson['lesson_completed'] > $this->curLesson) 
//				$this->curLesson = $this->latestLesson['lesson_completed'];

			// update some values
			$this->curLesson['lesson_completed'] = $this->latestLesson['lesson_completed'];
			$this->curClass['lesson_history'] = $this->latestLesson['lesson_history'];
		}

		// #4 if do not know course, then choose first one, or let choose from list
		if (!$this->curCourse && count($this->courseList)) {
			if (count($this->courseList) == 1) {
				reset($this->courseList);
				$this->curCourse = current($this->courseList);				
			}
			else if (count($this->courseList) > 1) {
				$this->view = 'COURSE_LIST';
			}
		}
						
		// #5 find the current class, if know current lesson
		if ($this->curLesson && !$this->curClass && count($this->classList)) {
			// find the class and setup
			foreach ($this->classList as $thisClass) {
				if ($this->curLesson['class_id'] == $thisClass['uid']) {
					$this->curClass = $thisClass;
					break;
				}
			}			
		}
		// #6 if do not know class, then go to first in list, or let choose
		if (!$this->curClass && $this->curCourse && count($this->classList)) {
			if (($this->config['sequentialClasses'] || (count($this->classList) == 1))) {	
				foreach($this->classList as $cls) {
					if ($this->countLessons($cls)) {
						$this->curClass = $cls;
						break;
					}
				}
				if (!$this->curClass) {
					$this->view = 'CLASS_LIST';
				}
			}
			else if (count($this->classList)) {
				$this->view = 'CLASS_LIST';
			}
		}

		// #7 if we do not have a lesson set, then set one here
		if (!$this->curLesson && $this->curClass) {
			// if sequentialLessons, then start at 0
			if (($this->config['sequentialLessons']) || (count($this->lessonList == 1))) {
				foreach ($this->lessonList as $lsn) {
					if ($lsn['class_id'] == $this->curClass['uid']) {
						$this->curLesson = $lsn;
						break;
					}
				}
			}
			// otherwise let them choose
			else if (count($this->lessonList)) {
				$this->view = 'LESSON_LIST';
			}
		}
	
		// save user data if starting a new course, class, or lesson
		if ($this->piVars['startcls'] || $this->piVars['startcrs']) {
			$this->saveUserData($addData);
		}
	}
	/**
	 * Define all possible fields from TypoScript and FlexForm.
	 *
     * @return	array       Configuration array made from TypoScript and FlexForm
	 */
    function setFFconfig() {
		// config: name, flexform sheet, flexform field, type(1=file, 2=), vDEF)
        $arrFFConfig = array(
            'templateCode'        => array('template_file', 'sDEF', 'templateFile', 1),
            'pid'                 => array('pages', 		'sDEF', 'pid',	3),
            'view'                => array('view', 			'sDEF', 'view',	2),
            'classPage'   		  => array('classPage', 	'sDEF', 'classPage',	2),

            'advanceLessons'   	  => array('advanceLessons',    's_options', 'advanceLessons',	2),
            'sequentialLessons'   => array('sequentialLessons', 's_options', 'sequentialLessons',	2),
            'sequentialClasses'   => array('sequentialClasses', 's_options', 'sequentialClasses',	2),
            'useJournal'   		  => array('useJournal', 's_options', 'useJournal',	2),

            'admin_user_list'     => array('admin_user_list', 's_admin', 'admin_user_list',	2),
        );
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['setFFconfig'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['setFFconfig'] as $_funcRef) {
				$arrSelectConf = t3lib_div::callUserFunction($_funcRef,$arrFFConfig, $this);
			}
		}
        return $arrOutput = $this->getFFconfig($arrFFConfig);
    }

    /**
	 * Check configuration in TypoScript and FlexForm.
	 * FlexForm has precendence over TypoScript
	 *
	 * @param	array		Definition array for TypoScipt and FlexForm
	 * @return	array       Configuration array made from TypoScript and FlexForm
	 */
    function getFFconfig($arrFFConfig) {
    	$strTemp = '';
        foreach ($arrFFConfig as $strKey => $arrItem) {
        	$strValue = !empty($arrItem[4]) ? $arrItem[4] : 'vDEF';
            $strFFValue = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $arrItem[0], $arrItem[1], 'lDEF', $strValue);
            $arrItem[2] = "['" . str_replace('.',".']['",$arrItem[2]) . "']";
            eval("\$strTemp = \$this->conf".$arrItem[2].";");
            if ($arrItem[3]==1) { // file
                $arrOutput[$strKey] = $this->cObj->fileResource($strFFValue ? 'uploads/tx_weclesson/' . $strFFValue : $strTemp);
            } elseif ($arrItem[3]==3) { // pid value
            	$arrOutput[$strKey] = ($strFFValue!='') ? $strFFValue : $strTemp;
            	$arrOutput[$strKey] = $arrOutput[$strKey] ? $arrOutput[$strKey] : $GLOBALS['TSFE']->id;
            } else { // integer or string value 
				// use the TS constant first, then the flexform
                $arrOutput[$strKey] = ($strTemp != '') ? $strTemp : $strFFValue;
				//$arrOutput[$strKey] = ($strFFValue != '') ? $strFFValue : $strTemp;
            }
        }
        return $arrOutput;
    }

	//
	function loadData() {
		if ($this->pid_list)
			$where = 'pid IN ('.$this->pid_list.')';

		// handle languages
		$lang = ($l = $GLOBALS['TSFE']->sys_language_uid) ? $l : '0,-1';
		if (strlen($where))
			$where .= ' AND ';
		$where .= 'sys_language_uid IN ('.$lang.') ';

		// load lessons
		$lwhere = $where . $this->cObj->enableFields($this->lessonTable);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->lessonTable, $lwhere, '', 'num,sorting');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res),$lwhere);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->lessonList[$row['uid']] = $row;
		}

		// load classes
		$clwhere = $where . $this->cObj->enableFields($this->classTable);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->classTable, $clwhere, '', 'sorting');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res),$clwhere);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->classList[$row['uid']] = $row;
		}

		// load courses
		$cowhere = $where . $this->cObj->enableFields($this->courseTable);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->courseTable, $cowhere, '', 'sorting');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res),$cowhere);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->courseList[$row['uid']] = $row;
		}

		// this loads data for each class that user is taking.
		if ($this->userID) {
			$uwhere = 'user_id=' . $this->userID;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->userDataTable, $uwhere, '', 'tstamp DESC');
			if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res), $uwhere);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$this->userData[$row['current_class']] = $row;
			}
//t3lib_div::debug($this->userData,"userData loaded=");			
		}
	}

	/**
	 * The main method of the Plugin
	 *
	 * @param	string		$content: The Plugin content
	 * @param	array		$conf: The Plugin configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->init($conf);
	    if ($conf['isLoaded'] != 'yes')
	      return $this->pi_getLL('errorIncludeStatic');
		switch ($this->view) {
			case 'START':  		$content = $this->displayStartPage();
				break;
			case 'ADMIN':		$content = $this->displayAdmin(); 
				break;
			case 'COURSE_LIST': $content = $this->displayCourseList();
				break;
			case 'COURSE': 		$content = $this->displayCourse();
				break;
			case 'CLASS_LIST':  $content = $this->displayClassList();
				break;
			case 'CLASS': 		$content = $this->displayClass();
				break;
			case 'LESSON_LIST': $content = $this->displayLessonList();
				break;
			case 'LESSON':
			default:			$content = $this->displayLesson();
				break;
		}

		return $this->pi_wrapInBaseClass($content);
	}

	// will fill with generic markers (for all views)
	function fillMarkerArray() {
		if ($this->curCourse) {
			$this->marker['###COURSE_TITLE###'] = $this->curCourse['title'];
			$this->marker['###COURSE_DESCRIPTION###'] = $this->curCourse['description'];
			$this->marker['###COURSE_OVERVIEW###'] = $this->pi_getLL('course_overview','');
			$this->marker['###COURSE_LABEL###'] = $this->pi_getLL('course_label','Course:');
			$this->marker['###COURSE_DESCRIPTION_LABEL###'] = $this->pi_getLL('course_description_label','');
			$this->marker['###COURSE_IMAGE###'] =  $this->curCourse['image'] ? '<img src="'.$this->curCourse['image'].'">' : '';
		}
		if ($this->curClass) {
			$this->marker['###CLASS_TITLE###'] = $this->curClass['title'];
			$this->marker['###CLASS_DESCRIPTION###'] = $this->curClass['description'];
			$this->marker['###CLASS_NUM_LABEL###'] = $this->pi_getLL('class_num_label','Class#');
			$this->marker['###CLASS_LABEL###'] = $this->pi_getLL('class_label','Class:');
			$this->marker['###CLASS_DESCRIPTION_LABEL###'] = $this->pi_getLL('class_description_label','');
			$this->marker['###CLASS_IMAGE###'] = $this->curClass['image'] ? '<img src="'.$this->curClass['image'].'">' : '';
		}
		if ($this->curLesson) {
			$this->marker['###LESSON_TITLE###'] = $this->curLesson['title'];
			$this->marker['###LESSON_TITLE_NUMBERED###'] = ($this->conf['numberLessons'] ? $this->curLesson['num'] . $this->pi_getLL('numbering_after','. ') : '') . $this->marker['###LESSON_TITLE###'];
			$this->marker['###LESSON_OVERVIEW###'] = $this->curLesson['overview'];
			$this->marker['###LESSON_DESCRIPTION###'] = $this->curLesson['description'];
			$this->marker['###LESSON_NUM_LABEL###'] = $this->pi_getLL('lesson_num_label','Lesson #');			
			$this->marker['###LESSON_LABEL###'] = $this->pi_getLL('lesson_label','Lesson:');			
			$this->marker['###LESSON_NUMBER###'] = $this->curLesson['num'];
			// count lessons in this class
			$totalLessons = 0;
			foreach ($this->lessonList as $lsn) {
				if ($this->curLesson['class_id'] == $lsn['class_id'])
					$totalLessons++;
			}
			$this->marker['###TOTAL_LESSONS###'] = $totalLessons;
		}

		$this->marker['###PLUGIN_URL###'] = t3lib_extMgm::siteRelPath('wec_lesson');

		if ($this->isAdmin) {
			$goURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 1));
			$this->marker['###ADMIN_BUTTON###'] = '<a style="text-decoration:none;font-weight:bold;" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>' . $this->pi_getLL('admin_button','Admin') . '</span></a>';
		}

	}

	// Checks to see if given lesson is last in the course
	//
	// return true/false
	//
	function isLastLesson($lsn=0) {
		if (!$lsn) $lsn = $this->curLesson;
		if (!$lsn) return true;
		
		// go through all lessons and see if this is the last one...
		$lastFoundLesson = 0;
		foreach ($this->lessonList as $thisLesson) {
		 	if ($lsn['class_uid'] == $thisLesson['class_uid']) {
				$lastFoundLesson = $thisLesson;
			}
		}		
		if ($lastFoundLesson && ($lastFoundLesson['class_uid'] == $thisLesson['class_uid'])) {
			return true;
		}
		
		return false;
	}
	
	// Find the next class after current one
	//
	// return   lesson 	the next class in the sequence. NULL if last class
	function getNextClass($cls) {
		$isFound = false;
		$nextClass = NULL;
		foreach ($this->classList as $thisClass) {
		 	if ($cls['uid'] == $thisClass['uid']) {
				$isFound = true;
			}
			else if ($isFound && ($cls['course_id'] == $thisClass['course_id'])) {
				$nextClass = $thisClass;
				break;
			}
		}
		return $nextClass;
	}
	
	// Find the next lesson after current one
	//
	// return  lesson 	the next lesson in the sequence. NULL if last lesson
	function getNextLesson($lsn) {
		if (empty($lsn)) $lsn = $this->curLesson;
		if (empty($lsn)) return 0;
		$isFound = false;
		$nextLesson = NULL;
		foreach ($this->lessonList as $thisLesson) {
		 	if ($lsn['uid'] == $thisLesson['uid']) {
				$isFound = true;
			}
			else if ($isFound && ($lsn['class_id'] == $thisLesson['class_id'])) {
				$nextLesson = $thisLesson;
				break;
			}
		} 
		
		return $nextLesson;		
	}

	// Count lessons in a given class
	//
	// return  integer		the count of lessons in a given class
	function countLessons($cls) {
		$isFound = false;
		$lessonCount = 0;
		foreach ($this->lessonList as $thisLesson) {
		 	if ($thisLesson['class_id'] == $cls['uid']) {
				$lessonCount++;
			}
		} 
		return $lessonCount;		
	}
	
	// Check to see if can advance to the next lesson
	//    - if no waiting time, then can advance.
	//	  - 
	// Does a check via hours, and within an hour, tests ok
	//
	// return boolean -- if can advance
	function canAdvanceToNextLesson($lsn=0) {
		if (empty($lsn)) 
			$lsn = $this->curLesson;
		if (empty($lsn)) 
			return false;
			
		$needToWaitHours = $this->lessonWaitHours;	
		$clsData = $this->userData[$lsn['class_id']];
		// find lesson end time
		$lessonEndTime = !empty($clsData['lesson_completed']) ? $clsData['lesson_completed'] : $clsData['tstamp'];
		$haveWaitedHours = (mktime() - $lessonEndTime) / 3600;
		
		// if no waiting needed, then return true
		if (!$lessonEndTime || !$needToWaitHours)
			return true;
			
		// if want to add a fudge factor, so that if less than 3 hours, can go ahead and do lesson, then can set that here
		if ($this->conf['fudgeHours']) 
			$needToWaitHours -= $this->conf['fudgeHours'];
		// if have not waited for enough time, then alert with message
		if ($haveWaitedHours < $needToWaitHours) {
			$waitStr = $this->pi_getLL('wait_for_next_lesson','You must wait for the next lesson. You have ###TIME### left.');	
			$waitStr2 = $this->pi_getLL('wait_for_next_lesson2',' You can look back at the previous lesson or come back later.');	
			$leftToWaitHours = ($this->lessonWaitHours - $haveWaitedHours);
			$leftToWaitDays = (int) ($leftToWaitHours / 24);
			if ($leftToWaitDays >= 1) {
				$waitTimeStr = (int) $leftToWaitDays . $this->pi_getLL('wait_time_days',' day(s) and ');
				$leftToWaitHours = $leftToWaitHours % $leftToWaitDays;
			}
			$waitTimeStr .= (int) $leftToWaitHours . $this->pi_getLL('wait_time_hours',' hours'); 
			$waitStr = str_replace('###TIME###',$waitTimeStr, $waitStr);
			$this->marker['###LESSON_INFO###'] .= $waitStr;
			$this->marker['###LESSON_INFO###'] .= $waitStr2;
			
			return false;
		}

		return true;
	}

	// this will handle showing or filling in lesson info if lesson is not available
	//
	// returns true that lesson info is filled in, otherwise false
	function displayLessonInfo() {
		$showLessonInfo = false;

		// if not logged in and editWindow, then show message
		if (!$this->userID && !empty($this->conf['editWindow']) && ($this->conf['editWindow'] != "not_required")) {
			$this->marker['###LESSON_INFO###'] = $this->pi_getLL('login_required_lessons','You need to login to your account in order to see lessons.');
			$this->marker['###LESSON_BUTTON###'] = '<a class="button" href="#" onclick="history.go(-1);return false;"><span>' . $this->pi_getLL('go_back_button','Go back') . '</span></a>';
			
			$this->subpartMarker['###SHOW_LESSON_MAIN###'] = '';
			$showLessonInfo = true;
		}
		// if no lessons available because forced to choose class, then display classes...
		else if (isset($this->piVars['cls']) && $this->piVars['cls'] == 0) {
			// @todo handle if sequential classes, then force to go to next class, not choose. if no more lessons, then choose course
			
			$content = $this->displayClassList(true);
			$this->marker['###LESSON_INFO###'] = $content;
			$this->subpartMarker['###SHOW_LESSON_MAIN###'] = '';
			$showLessonInfo = true;
		}	
		// No lessons available	
		else if (!$this->curLesson) {
			$this->marker['###LESSON_INFO###'] = $this->pi_getLL('no_lessons_available',"There are no lessons available yet for this class.");
			$goURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id,array('cls' => 0));
			$this->marker['###LESSON_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return true;"><span>' . $this->pi_getLL('choose_different_class_btn','Choose A Different Class') . '</span></a>';
			$this->marker['###CHOOSE_LESSON_LABEL###'] = '';
			$this->marker['###LESSON_DROPDOWN###'] = '';
			
			$this->subpartMarker['###SHOW_LESSON_MAIN###'] = '';
			$showLessonInfo = true;
		}
		// Show completed lesson
		else if ($this->curLesson['lesson_completed']) { 
			// check if at end of lessons, if so, then let know.
			if ($this->isLastLesson($this->curLesson)) {
				$this->marker['###LESSON_INFO###'] = $this->pi_getLL('completed_class_info','You have finished all lessons in this class.');
				$nextClass = $this->getNextClass($this->curClass);
				if ($nextClass) { // allow to go to next class
					$goURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id, array('cls' => $nextClass['uid']));
					$this->marker['###LESSON_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>' . $this->pi_getLL('next_class_button','Go To Next Class') . '</span></a>';
				}
				else { // allow to pick a course
					// @todo -- if sequential classes, just choose next one...otherwise can pick
					$this->marker['###LESSON_INFO###'] .= $this->pi_getLL('completed_course_info','You have also finished the last class in this course.');
					$goURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id, array('crs' => -1));
					$this->marker['###LESSON_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>' . $this->pi_getLL('next_course_button','Choose Course') . '</span></a>';
				}
				$showLessonInfo = true;
			}
			// allow to go onto next lesson
			else if ($this->canAdvanceToNextLesson($this->curLesson) || $this->isAdmin) { 
				$this->marker['###LESSON_INFO###'] = $this->pi_getLL('completed_lesson_info','This lesson is completed');
				$nextLesson = $this->getNextLesson($this->curLesson);
				if ($nextLesson) { // allow to go to next lesson
					$this->saveUserData($saveData);
					$goURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id,array('lsn' => $nextLesson['uid']));
					$this->marker['###LESSON_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>' . $this->pi_getLL('next_lesson_button','Go To Next Lesson') . '</span></a>';
				}
				else { // go to next class
					$saveData['class_completed'] = mktime();
					$this->saveUserData($saveData);
					
					// @todo -- if sequential lessons, then choose next one
					$this->marker['###LESSON_INFO###'] .= $this->pi_getLL('completed_course_info','You have also finished the last class in this course.');
					$goURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id, array('crs' => -1));
					$this->marker['###LESSON_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>' . $this->pi_getLL('next_course_button','Choose Course') . '</span></a>';
				}
			}
			// else if cannot go to next lesson, then lesson_info was filled in with canAdvanceToNextLesson()
			
			$showLessonInfo = true;
			$this->subpartMarker['###SHOW_LESSON_MAIN###'] = '';
		}		
		return $showLessonInfo;
	}
	
	// Dropdown select menu of all lessons that have done (unless admin)
	//
	function displaySelectLessonMenu() {
		$selList = '<select size="1" onchange="if (this.selectedIndex >= 0) location.href=this.options[this.selectedIndex].value;">';
		$lessonCount = 0;
		foreach ($this->lessonList as $thisUID => $thisLesson)  {
			if ($thisLesson['class_id'] == $this->curClass['uid']) {
				$paramArray['lsn'] = $thisLesson['uid'];
//				$paramArray['no_cache'] = 1;
				$lessonName = ($this->conf['numberLessons'] ? $thisLesson['num'] . $this->pi_getLL('numbering_after','. ') : '') . $thisLesson['title'];
				if (($len=$this->conf['dropdownMenuChars']) && (strlen($lessonName) > $len)) {
					$lessonName = substr($lessonName,0,$len) . $this->pi_getLL('over_characters','...');
				}
				$selList .= '<option value="' . $this->getAbsoluteURL($GLOBALS['TSFE']->id, $paramArray) . '" '. (($thisLesson['uid'] == $this->curLesson['uid']) ? ' selected' : '') .'>'.$lessonName.'</option>';
				$lessonCount++;
				if (!$this->isAdmin && $this->config['sequentialLessons'] && ((!$this->latestLesson && ($thisLesson['uid'] == $this->curLesson['uid'])) || ($this->latestLesson && ($this->latestLesson['current_lesson'] == $thisLesson['uid']))))
					break;
			}
		}
		$selList .= '</select>';
		if ($lessonCount) { // show previous lessons, if available
			$this->marker['###LESSON_DROPDOWN###'] = $selList;
			if ($this->config['sequentialLessons']  && !$this->isAdmin)
				$this->marker['###CHOOSE_LESSON_LABEL###'] = $this->pi_getLL('choose_previous_lessons_label','Previous Lesson(s):');
			else
				$this->marker['###CHOOSE_LESSON_LABEL###'] = $this->pi_getLL('choose_lessons_available_label','Lessons Available:');
		}
	}

	// Dropdown select menu of all classes that have done (unless admin)
	//
	function displaySelectClassMenu() {
		$selList = '<select size="1" onchange="if (this.selectedIndex >= 0) location.href=this.options[this.selectedIndex].value;">';
		$classCount = 0;
		foreach ($this->classList as $thisUID => $thisClass)  {
			if ($thisClass['course_id'] == $this->curClass['course_id']) {
				$paramArray['startcls'] = $thisClass['uid'];
				$className = $thisClass['title'];
				if (($len=$this->conf['dropdownMenuChars']) && (strlen($className) > $len)) {
					$className = substr($className,0,$len) . $this->pi_getLL('over_characters','...');
				}
				$selList .= '<option value="'.$this->getAbsoluteURL($GLOBALS['TSFE']->id, $paramArray, FALSE).'" '. (($thisClass['uid'] == $this->curClass['uid']) ? ' selected' : '') .'>'.$className.'</option>';
				$classCount++;
//				if (!$this->isAdmin && $this->config['sequentialClasses'] && ($thisClass == $this->curClass))
//					break;
			}
		}
		
		$selList .= '</select>';
		if ($classCount > 1) { // show previous classs, if available
			$this->marker['###CLASS_DROPDOWN###'] = $selList;
			if ($this->config['sequentialClasses']  && !$this->isAdmin)
				$this->marker['###CHOOSE_CLASS_LABEL###'] = $this->pi_getLL('choose_previous_classes_label','Previous Classes:');
			else
				$this->marker['###CHOOSE_CLASS_LABEL###'] = $this->pi_getLL('choose_classes_available_label','Classes Available:');
		}		
	}
		
	// Display the lesson content and all pieces
	//   Three states: 1) No lesson -- get message + link to choosing a class
	//				   2) End of lesson -- get message + if can advance, then button to go on
	//				   3) Current lesson -- show lesson
	
	function displayLesson() {
		$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_LESSON###');
		$this->fillMarkerArray();

		// Show current lesson if can
		if (!$this->displayLessonInfo()) {
			$this->subpartMarker['###SHOW_LESSON_INFO###'] = '';
			$this->displayVideo($this->curLesson['video_files']);
			$this->displayAudio($this->curLesson['audio_files']);
			$this->displayResources($this->curLesson['resource_files']);
			$this->displayText($this->curLesson['content'], $template);
		}
		
		$this->displaySelectLessonMenu();
//		if ($this->isAdmin) @todo allow option to show class menu
			$this->displaySelectClassMenu();
			
		// then do the substitution with the template
		$lessonContent = $this->cObj->substituteMarkerArrayCached($template, $this->marker, $this->subpartMarker, array());
		// clear out any empty template fields
		$lessonContent = preg_replace('/###.*?###/', '', $lessonContent);
		// change any typo3_search to markers
		$lessonContent = preg_replace('/######/','###', $lessonContent);
		
		return $lessonContent;
	}
	
	// display text. can be paged / scrollbar / full text.
	function displayText($content, $template) {
		if (is_array($this->conf['general_stdWrap.'])) {
			$content = $this->cObj->stdWrap($content, $this->conf['general_stdWrap.']);
		}
				
		// if have tabbed content, then display it
		// @todo check if tabbed content by ###TAB_MENU or ???
		if (preg_match("/<h3>([^`]*?)<\/h3>/", $content))
			$content = $this->displayTabbedContent($content, $template);
		else {
			// not tabbed content, so just show under lesson info
			// @todo should make a separate section for this???
			$this->subpartMarker['###SHOW_LESSON_MAIN###'] = '';
			unset($this->subpartMarker['###SHOW_LESSON_INFO###']);
			
			$this->marker['###LESSON_INFO###'] = $content;
		}

	}

	function displayTabbedContent($content, $template) {
		$GLOBALS['TSFE']->additionalHeaderData['wec_lesson'] .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath('wec_lesson') . 'res/tabmenu.js"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['wec_lesson'] .= '<link href="' . t3lib_extMgm::siteRelPath('wec_lesson') . 'res/tabmenu.css' . '" rel="stylesheet" type="text/css" />';

		// grab tabs (h3 tags) from content
		$tabHeaders = $this->grabTagHeaders('h3',$content);

		// build tab menu
		$this->marker['###TAB_MENU###'] = '<div id="tabMenu"><ul>';
		for ($i = 0; $i < count($tabHeaders); $i++) {
			$this->marker['###TAB_MENU###'] .= '<li id="tab-'.($i+1).'"><a href="#" title="' . $tabHeaders[$i]['header_clean'] . '" onclick="selectTab(\''.($i+1).'\');return false;"><span>' . $tabHeaders[$i]['header'] . '</span></a></li>';
			$this->marker['###TAB'.($i+1).'_NAME###'] = $tabHeaders[$i]['header'];
		}
		$this->marker['###TAB_MENU###'] .= '</ul></div>';
		$this->marker['###TAB_MENU_END###'] = '
			<script type="text/javascript">
				/*<![CDATA[*/
				selectTab(1);
				/*]]>*/
			</script>';

		// grab all content for h4 sections
		$contentSections = $this->grabTagContent('h4',$content);
		$tag = 'h3';
		// put all content into correct markers
		for ($i = 0; $i < count($contentSections); $i++) {
			$this->marker['###HEADER_'.$contentSections[$i]['header_clean'].'###'] = $contentSections[$i]['header'];
			// strip out any h3 markers
			$showContent = $contentSections[$i]['content'];
			$showContent = preg_replace('/<'.$tag.'.*>(.*)<\/'.$tag.'>/','',$showContent);
			$this->marker['###CONTENT_'.$contentSections[$i]['header_clean'].'###'] = $showContent;
		}
		
		$goURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id, array('elsn' => $this->curLesson['uid']), false);
		$this->marker['###COMPLETED_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->pi_getLL('completed_button','I am done with this lesson').'</span></a>';
		
		return $content;
	}

	function grabTagHeaders($tag, $content) {
		$tagHeaders = array();
		preg_match_all('/<'.$tag.'.*>(.*)<\/'.$tag.'>/', $content, $matches);
		$i = 0;
		foreach ($matches[0] as $m) {
			$tagContent = $this->cleanContent($m);
			if (strlen($tagContent)) {
				$tagHeaders[$i]['header_clean'] = $tagContent;
				$tagHeaders[$i]['header'] = strip_tags($m);
				$i++;
			}
		}
		return $tagHeaders;
	}

	function grabTagContent($tag, $content) {
		preg_match_all('/<'.$tag.'.*>(.*)<\/'.$tag.'>/', $content, $matches);
		$tabMatches = $matches[0];
		for ($i = 0; $i < count($tabMatches); $i++) {
			$tabHeader = $this->cleanContent($tabMatches[$i]);
			$st = stripos($content,$tabMatches[$i]) + strlen($tabMatches[$i]);
			$nextSt = ($i == (count($tabMatches) - 1)) ? strlen($content) : stripos($content,$tabMatches[$i+1]);
			$nextContent = substr($content,$st,$nextSt - $st);
			$nextContent = trim($nextContent,"\xA0 \t\n\r");
			$sectionText[$i]['header_clean'] = $tabHeader;
			$sectionText[$i]['content'] = $nextContent;
			$sectionText[$i]['header'] = $tabMatches[$i];

			$content = substr($content,$nextSt, strlen($content));
		}
		return $sectionText;
	}

	// This cleans all tags and trims whitespace and keeps only alphanumeric chars
	function cleanContent($m) {
		// remove all tags
		$m = strip_tags($m);
		// convert any chars
		$m = strtr($m, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
		// trim whitespace
		$m = trim($m,"\xA0 \t\n\r\0");
		// only keep alpha characters
		$m = preg_replace('/[\/\\- ]/', '_', $m);
		$m = preg_replace('/[^A-Za-z0-9_]/', '', $m);

		return $m;
	}

	// display video with player
	function displayVideo($vidFiles) {
		if (empty($vidFiles)) {
			$this->subpartMarker['###VIDEO_SECTION###'] = '';
			$this->marker['###VIDEO_INFO###'] = '<div style="height:300px;width:100%;margin:0 auto;padding-top:20px;text-align:center;">'.$this->pi_getLL('no_video_yet','No video is available yet.').'</div>';
			return;
		}
		// include file
//		$GLOBALS['TSFE']->additionalHeaderData['wec_lesson'] .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath('wec_lesson') . 'res/flowplayer/flowplayer-3.0.6.min.js"></script>';

//@todo break up into separate markers + files if more than one file is in vidFiles

		// load up file and possibly look to see if exists
//		$videoPath = $this->conf['videoPath'];
//		if (substr($videoPath,strlen($videoPath) -1,1) != '/') $videoPath .= '/';
		$videoPath = t3lib_div::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://' . $_SERVER['HTTP_HOST'] . '/' . 'uploads/tx_weclesson/';

		$this->marker['###SITE_URL###'] = t3lib_div::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://' . $_SERVER['HTTP_HOST'] . '/';
		$this->marker['###VIDEO_FILE_PATH###'] = $videoPath;

		$vidFileList = t3lib_div::trimExplode(',',$vidFiles);
		foreach ($vidFileList as $videoFile) {
			if (strstr($videoFile,'_low')) {
				$this->marker['###VIDEO_LOW_FILE###'] = basename($videoFile);
			}
			else {
				$this->marker['###VIDEO_FILE###'] = basename($videoFile);
			}
		}
	}

	// display audio with player
	function displayAudio($audioFiles) {
		// if no audio files, then return
		
		// include player
		
		// load up audio file(s)
		
	
	}

	// display resources
	function displayResources($audioFiles) {
		// for PDFs or DOCs, list with icon.
		// Add ALT tag to link so can


	}

	// -- display list of lessons have taken. when completed.
	function displayLessonList() {
		$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_LESSON_LIST###');
		$this->fillMarkerArray();		

		// display each of the lessons in the list		
		$itemTemplate = $this->cObj->getSubpart($template, '###LESSON_LIST_ITEM###');
		$itemList = '';
		foreach ($this->lessonList as $thisUID=>$thisLesson) {
			$itemMarker = array();
			$item = $thisLesson;
			if (($item['class_id'] == $this->curClass['uid']) || !count($this->classList)) {
				$itemMarker['###LESSON_LABEL###'] = $this->pi_getLL('lesson_label', 'Lesson :');
				$itemMarker['###LESSON_NUM_LABEL###'] = $this->pi_getLL('lesson_num_label', 'Lesson#');
				$itemMarker['###LESSON_NUM###'] = $item['num'];
				$itemMarker['###LESSON_TITLE###'] = $item['title'];
				$itemMarker['###LESSON_DESCRIPTION###'] = $item['description'];
				$itemMarker['###LESSON_OVERVIEW###'] = $item['overview'];
				$goURL = $this->getAbsoluteURL($GLOBALS['TSFE']->id,array('lsn' => $item['uid']));
				if ($this->isAdmin || !$this->config['sequentialLessons'] || ($item['num'] < $this->curLesson['num']))
					$itemMarker['###LESSON_TAKE_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->pi_getLL('lesson_take_button','Take this lesson').'</span></a>';
				
				$itemList .= $this->cObj->substituteMarkerArrayCached($itemTemplate, $itemMarker, array(), array());
			}
		}
		$this->subpartMarker['###LESSON_LIST_ITEM###'] = $itemList;
		
		// then do the substitution with the template
		$listContent = $this->cObj->substituteMarkerArrayCached($template, $this->marker, $this->subpartMarker, array());
		// clear out any empty template fields
		$listContent = preg_replace('/###.*?###/', '', $listContent);
		// change any typo3_search to markers
		$listContent = preg_replace('/############/','###', $listContent);
		
		return $listContent;		
	}

	// display list of classes can take for a given course
	function displayClassList($letChoose = false) {
		$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_CLASS_LIST###');
		$this->fillMarkerArray();		
		// display each of the class in the list		
		$itemTemplate = $this->cObj->getSubpart($template, '###CLASS_LIST_ITEM###');
		$itemList = '';
		foreach ($this->classList as $thisUID=>$thisClass) {
			$itemMarker = array();
			$item = $thisClass;
			if (($item['course_id'] == $this->curCourse['uid']) || !count($this->courseList)) {
				$itemMarker['###CLASS_LABEL###'] = $this->pi_getLL('class_label', 'Class :');
				$itemMarker['###CLASS_NUM_LABEL###'] = $this->pi_getLL('class_num_label', 'Class#');
				$itemMarker['###CLASS_NUM###'] = $item['num'];
				$itemMarker['###CLASS_TITLE###'] = $item['title'];
				$itemMarker['###CLASS_DESCRIPTION###'] = $item['description'];
				$itemMarker['###CLASS_OVERVIEW###'] = $item['overview'];
				$goPid = $this->config['classPage'] ? $this->config['classPage'] : $GLOBALS['TSFE']->id;
				$goURL = $this->getAbsoluteURL($goPid,array('startcls' => $item['uid']), FALSE);
				if ($this->isAdmin || $letChoose || !$this->config['sequentialClasses'] || ($item['num'] < $this->curClass['num']))
					$itemMarker['###CLASS_TAKE_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->pi_getLL('class_take_button','Take this class').'</span></a>';
				$itemMarker['###CLASS_IS_ENROLLED###'] = ($this->curClass['uid'] == $item['uid']) ? $this->pi_getLL('class_is_enrolled',"Is Enrolled") : '';
				if (strlen($itemMarker['###CLASS_IS_ENROLLED###'])) $itemMarker['###CLASS_TAKE_BUTTON###'] = '';
				
				$itemList .= $this->cObj->substituteMarkerArrayCached($itemTemplate, $itemMarker, array(), array());
			}
		}
		$this->subpartMarker['###CLASS_LIST_ITEM###'] = $itemList;
		
		// then do the substitution with the template
		$listContent = $this->cObj->substituteMarkerArrayCached($template, $this->marker, $this->subpartMarker, array());
		// clear out any empty template fields
		$listContent = preg_replace('/###.*?###/', '', $listContent);
		// change any typo3_search to markers
		$listContent = preg_replace('/######/','###', $listContent);
		
		return $listContent;
	}
	
	// display list of courses can take
	function displayCourseList() {
		$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_COURSE_LIST###');
		$this->fillMarkerArray();		
		// display each of the class in the list		
		$itemTemplate = $this->cObj->getSubpart($template, '###COURSE_LIST_ITEM###');
		$itemList = '';
		foreach ($this->courseList as $thisUID=>$thisCourse) {			
			$itemMarker = array();
			$item = $thisCourse;
//			if ($item['course_id'] == $this->curCourse['uid']) {
				$itemMarker['###COURSELABEL###'] = $this->pi_getLL('class_label', 'Class :');
				$itemMarker['###COURSENUM_LABEL###'] = $this->pi_getLL('class_num_label', 'Class#');
				$itemMarker['###COURSENUM###'] = $item['num'];
				$itemMarker['###COURSETITLE###'] = $item['title'];
				$itemMarker['###COURSEDESCRIPTION###'] = $item['description'];
				$itemMarker['###COURSEOVERVIEW###'] = $item['overview'];
				$goPid = $this->config['classPage'] ? $this->config['classPage'] : $GLOBALS['TSFE']->id;
				$goURL = $this->getAbsoluteURL($goPid,array('startcrs' => $item['uid']));
				if ($this->isAdmin || !$this->config['sequentialClasses'] || ($item['num'] < $this->curClass['num']))
					$itemMarker['###COURSETAKE_BUTTON###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->pi_getLL('class_take_button','Take this class').'</span></a>';
				$itemMarker['###COURSEIS_ENROLLED###'] = ($this->curClass['uid'] == $item['uid']) ? $this->pi_getLL('class_is_enrolled',"Is Enrolled") : '';
				if (strlen($itemMarker['###COURSEIS_ENROLLED###'])) $itemMarker['###COURSETAKE_BUTTON###'] = '';
				
				$itemList .= $this->cObj->substituteMarkerArrayCached($itemTemplate, $itemMarker, array(), array());
//			}
		}
		$this->subpartMarker['###COURSE_LIST_ITEM###'] = $itemList;
		
		// then do the substitution with the template
		$listContent = $this->cObj->substituteMarkerArrayCached($template, $this->marker, $this->subpartMarker, array());
		// clear out any empty template fields
		$listContent = preg_replace('/###.*?###/', '', $listContent);
		// change any typo3_search to markers
		$listContent = preg_replace('/######/','###', $listContent);
		
		return $listContent;
	}

	// display start page
	function displayStartPage() {
		
		$template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_START_PAGE###');
		$this->fillMarkerArray();		
		$this->displaySelectLessonMenu();
		$this->displaySelectClassMenu();
		
		$this->marker['###CURRENT_CLASS_LABEL###'] = $this->pi_getLL('current_class_label', 'Current Class:');
//$this->curLesson = 0;		
//$this->curLesson['lesson_completed'] = true;
		if (!$this->displayLessonInfo()) {
			$this->marker['###CURRENT_LESSON_LABEL###'] = $this->pi_getLL('current_lesson_label', 'Lesson:');
			$goPid = $this->config['classPage'] ? $this->config['classPage'] : $GLOBALS['TSFE']->id;
			$goURL = $this->getAbsoluteURL($goPid,array('lsn' => $this->curLesson['uid']));
			$this->marker['###START_LESSON_BUTTON###'] = '<a href="'.$goURL.'" class="button"><span>'.$this->pi_getLL('start_lesson_button','Start Lesson').'</span></a>';
		}

		// then do the substitution with the template
		$startPageContent = $this->cObj->substituteMarkerArrayCached($template, $this->marker, $this->subpartMarker, array());
		// clear out any empty template fields
		$startPageContent = preg_replace('/###.*?###/', '', $startPageContent);
		// change any typo3_search to markers
		$startPageContent = preg_replace('/######/','###', $startPageContent);		
		
		return $startPageContent;
	}

	// Save user data
	//  - this saves the current class data for the user
	//
	function saveUserData($addData = 0) {
		$userIndex = $this->curClass['uid'] ? $this->curClass['uid'] : 0;
//t3lib_div::debug($this->curClass['uid'],'saveuserData for class uid=');
		$updatedUserData = array();
		$newUserData = array();
		
		if (is_array($addData)) {
			if (!$this->userData[$userIndex]) {
				$newUserData = $addData;
			}
			else {
				$updatedUserData = $addData;
			}
		}
		// build or update data to save
		if ($this->curLesson) $updatedUserData['current_lesson'] = $this->curLesson['uid'];
		if ($this->curClass) $newUserData['current_class'] = $this->curClass['uid'];
		if ($this->curCourse) $newUserData['current_course'] = $this->curCourse['uid'];
		$updatedUserData = array_merge($updatedUserData,$newUserData);
		
		// lesson history
		//    lesson # | tstamp started | tstamp completed | score | 
		
		// only to be included in new records
		// see if there is existing userdata for user + class
		if (!$this->userData[$userIndex]) {
			// if not, then create new user data...
			$newUserData['crdate'] = mktime();
			$newUserData['tstamp'] = mktime();
			$newUserData['user_id'] = $this->userID;
			$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->userDataTable, $newUserData);
			if (mysql_error()) t3lib_div::debug(mysql_error(),"db insert error");
			$newUserDataID = $GLOBALS['TYPO3_DB']->sql_insert_id();
			$this->userData[$userIndex] = $updatedUserData;
		}
		else {
			$updatedUserData = array_merge($this->userData[$userIndex],$updatedUserData);
			$updatedUserData['tstamp'] = mktime();
				
			// else UPDATE DATA with LATEST here
			$where = 'user_id=' . $this->userID . ' AND current_class=' . $userIndex;
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->userDataTable, $where, $updatedUserData);
			if (mysql_error()) t3lib_div::debug(mysql_error(),"db update error");
		}
	}
	
	/** 
	*
	*/
	function displayAdmin() {
		require_once(t3lib_extMgm::extPath('wec_lesson') . "pi1/class.tx_weclesson_admin.php");
		$adminObj = t3lib_div::makeInstance('tx_weclesson_admin');
		return $adminObj->adminMenu($this);
	}

	/**
	* Getting the full URL (ie. http://www.host.com/... to the given ID with all needed params
	* This function handles cross-site (on same server) links
	*
	* @param integer  $id: Page ID
	* @param string   $urlParameters: array of parameters to include in the url (i.e., "$urlParameters['action'] = 4" would append "&action=4")
	* @return string  $url: URL
*/
	function getAbsoluteURL($id, $extraParameters = '', $useCache = 1) {
		// clear out piVars that are not set
		foreach ($this->piVars as $key=>$pvar) {
			if (!$extraParameters[$key])
				unset($this->piVars[$key]);
		}
		// determine pageURL based on params and useCache values
		$pageURL = $this->pi_linkTP_keepPIvars_url($extraParameters, $useCache, 0, $id);
		
		// if did not cross page boundaries, then generate url from info
		if (strpos($pageURL,"http") === FALSE) {
			$serverProtocol = t3lib_div::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://';
			$absURL = $serverProtocol . $_SERVER['HTTP_HOST'] . '/' . $pageURL;
		}
		else // crosses boundaries (likely different url on same server)
			$absURL = $pageURL;

		$absURL = str_replace('&','&amp;', $absURL);
		return $absURL;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_lesson/pi1/class.tx_weclesson_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_lesson/pi1/class.tx_weclesson_pi1.php']);
}

?>