<?php
/***********************************************************************
* Copyright notice
*
* (c) 2009 Christian Technology Ministries International Inc.
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
*  DESCRIPTION: The Admin reports and interface for WEC Lessons
*
*/

require_once(PATH_t3lib.'class.t3lib_basicfilefunc.php');

class tx_weclesson_admin {
	var $parentObj;
	var $config;
	var $fieldNames;
	var $dispFieldNames;
	var $userData;
	var $studentData;
	var $orgData;
	var $numStudents;
	var $groupList;
	var $csvArray;
	var $csvFileName;
	var	$sortby;

	// Generate a menu of admin reports and functions
	// will only show what have capabilities to show
	function adminMenu($parent, $user=0) {
		$this->parentObj = $parent;
		$adminAction = (int)$this->parentObj->piVars['admin'];
		$this->sortby = (int)$this->parentObj->piVars['sortby'];
		$this->config = $this->parentObj->config;
		$this->conf = $this->parentObj->conf;

		// initialize
		if (!$this->cObj) $this->cObj = t3lib_div::makeInstance('tslib_cObj');

		$this->loadUserData();
		$this->loadClassData();
		$this->loadStudentData();

		// determine level of admin
		// determine org

		$this->templateCode = $this->cObj->fileResource($this->config['templateCodeAdmin'] ? $this->config['templateCodeAdmin'] : t3lib_extMgm::siteRelPath('wec_lesson') . 'tmpl/weclesson_admin.tmpl');
		$this->marker['###ADMIN_HEADER###'] = $this->parentObj->pi_getLL('admin_header',"Admin Menu");
		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 1));
		$this->marker['###RETURN_TO_MENU###']  = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_exit_menu','Return to Admin Menu').'</span></a>';
		$this->marker['###CLOSE_WINDOW###']  = '<a class="button" href="#" onclick="closeBox();return false;"><span>'.$this->parentObj->pi_getLL('admin_close_window','Close Window').'</span></a>';

		$GLOBALS['TSFE']->additionalHeaderData['prototype'] = '<script src="typo3/contrib/prototype/prototype.js" type="text/javascript"></script>';
		$GLOBALS['TSFE']->additionalHeaderData['wec_lesson'] .= '<link href="' . t3lib_extMgm::siteRelPath('wec_lesson') . 'res/adminmenu.css' . '" rel="stylesheet" type="text/css" />';
		$GLOBALS['TSFE']->additionalHeaderData['wec_lesson'] .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath('wec_lesson') . 'res/adminmenu.js"></script>';

		// determine if show a given report (admin menu item selected)
		switch ($adminAction) {
			case 2: $this->studentOverviewReport(); break;
			case 3: $this->studentDetailedReport(); break;

			case 4: $this->classSnapshotReport(); break;
			case 5: $this->classDetailedReport(); break;

			case 6: $this->groupReport(); break;
			case 7: $this->userListReport(); break;
			
			case 9: $this->allOrgReport(); break;
		}
		// if show a report, then generate it
		$reportContent = '';
		if ($adminAction > 1) {
			$templateMarker = '###TEMPLATE_ADMIN_REPORT###';
			$this->template = $this->cObj->getSubpart($this->templateCode, $templateMarker);

			$this->marker['###CHOOSE_SORTING###'] = $this->showSortOptions();

			// Do the substitution with the template
			$reportContent = $this->cObj->substituteMarkerArrayCached($this->template, $this->marker, $this->subpartMarker, array());
			// clear out any empty template fields
			$reportContent = preg_replace('/###.*?###/', '', $reportContent);
			
			// allow lightbox popup window if a report
			$this->template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_ADMIN_WINDOW###');
			$reportContent = str_replace("\n",'',$reportContent);
			$reportContent = addslashes($reportContent);
			$this->marker['###ADMIN_REPORT###'] = $reportContent;
			$reportContent = $this->cObj->substituteMarkerArrayCached($this->template, $this->marker, $this->subpartMarker, array());
		}

		// always show the menu
		$this->generateMenu();
		$this->overviewStats();
		$this->template = $this->cObj->getSubpart($this->templateCode, '###TEMPLATE_ADMIN_MENU###');
		$content = $this->cObj->substituteMarkerArrayCached($this->template, $this->marker, $this->subpartMarker, array());
		$content = preg_replace('/###.*?###/', '', $content);
		
		$content = $reportContent . $content;

		return $content;
	}

	// Generate Menu
	function generateMenu() {
		$this->marker['###MENU_HEADER###'] = $this->parentObj->pi_getLL('admin_menu_header','Please select an option:');

		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 2));
		$this->marker['###STUDENT_OVERVIEW_REPORT_MENU###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_student_overview_report_menu','Student Overview Report').'</span></a>';
//		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 3));
//		$this->marker['###STUDENT_DETAILED_REPORT_MENU###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_student_detailed_report_menu','Student Detailed Report').'</span></a>';

		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 4));
		$this->marker['###CLASS_OVERVIEW_REPORT_MENU###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_class_overview_report_menu','Class Snapshot Report').'</span></a>';
		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 5));
		$this->marker['###CLASS_DETAILED_REPORT_MENU###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_class_detailed_report_menu','Class Detailed Report').'</span></a>';

//		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 6));
//		$this->marker['###GROUP_REPORT_MENU###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_group_report_menu','Group Reports').'</span></a>';

		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 7));
$this->marker['###USERLIST_REPORT_MENU###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_userlist_report_menu','Full User List Report').'</span></a>';

		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => 9));
		$this->marker['###ALLORG_REPORT_MENU###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_allorg_report_menu','Master Organization Report').'</span></a>';


//		$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id);
//		$this->marker['###EXIT_ADMIN###'] = '<a class="button" href="#" onclick="location.href=\''.$goURL.'\';return false;"><span>'.$this->parentObj->pi_getLL('admin_exit','Exit Admin').'</span></a>';

	}

	// Load data with given [org]
	// 		loads all user_data and fe_users
	//		loads all lesson, course, and class data
	//
	function loadStudentData($pidList=0) {
		// take data from user_data and fe_users
		$fromStr = 'tx_weclesson_user_data AS A, fe_users AS B';

		// load student data
		$selStr = 'A.*,B.username,B.email,B.name,B.first_name,B.last_name,B.address,B.city,B.zone,B.zip,B.country,B.lastlogin,B.is_online,B.tx_wecuser_attends,B.tx_wecmaxpoint_assessment_score,B.pid';
		// @todo need to allow to add additional fields here

		$where = 'A.deleted=0';
		$where .= ' AND A.user_id = B.uid';
		if ($pidList)
			$where .= ' AND B.pid IN (' . $pidList . ')';

		// set sortby
		switch ($this->sortby) {
			case 1:	$orderby = 'B.name DESC'; break;
			case 2:	$orderby = 'A.current_class,B.name DESC'; break;
			case 3:	$orderby = 'B.is_online DESC'; break;
			case 4:	$orderby = ''; break;
			default: $orderby = 'B.name,A.tstamp DESC';
		}
		// do the query and add all data
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selStr, $fromStr, $where, '', $orderby);
		if (mysql_error()) 
			t3lib_div::debug(array(mysql_error(), $res), "SELECT ".$selStr." FROM ".$fromStr." WHERE ".$where);
		$this->studentData = array();
		$this->orgData = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->studentData[$row['user_id']][] = $row;
			$this->orgData[$row['pid']][] = $row;
		}

		// count users
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,name,pid,crdate,tstamp', 'fe_users', 'deleted=0', '', 'tstamp');
		$this->userCount = 0;
		$this->newUserCount = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->userCount++;
			if ($row['crdate'] >= mktime(0,0,0,date("m")-1,date("d"),date("Y"))) {
				$this->newUserCount++;
			}
		} 
	}

	function loadClassData($pidList=0) {

		// load in lesson, course & class data (for report info)
		$where = $this->parentObj->pid_list ? 'pid IN (' . $this->parentObj->pid_list . ')' : '1';
		$where .= ' AND deleted=0 AND hidden=0';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,num,title', 'tx_weclesson_class', $where, '', 'num ASC');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res), $where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->classList[$row['uid']] = $row;
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', 'tx_weclesson_course', $where, '', 'sorting');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res), $where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->courseList[$row['uid']] = $row;
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,num,title', 'tx_weclesson_lesson', $where, '', 'num ASC');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $res), $where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->lessonList[$row['uid']] = $row;
		}

		// read in language files
		$lang =  $GLOBALS['TSFE']->config['config']['language'] ? $GLOBALS['TSFE']->config['config']['language'] : 'default';
		$this->LOCAL_LANG = $this->parentObj->LOCAL_LANG[$lang];

		// set all the field names
		$this->fieldNames = array('name','username','email','location','address','telephone','city','zone','zip','country','current_class','current_course',
			'class_start','class_completed','current_lesson','completed_classes','completed_courses','registered_date', 'lastlogin', 'is_online');

		// load display name fields from LLL
		foreach ($this->fieldNames as $fld) {
			$fldLabel = strtolower($fld);
			$this->dispFieldNames[$fld] = $this->LOCAL_LANG[$fld];
		}

		// add any extra fields
		if ($extraFieldsArray = $this->addFields($this->parentObj->conf['adminFieldNames'])) {
			$this->fieldNames = array_merge($this->fieldNames,$extraFieldsArray);
		}

		// set the date format
		$this->date_format = $this->LOCAL_LANG['date_format'] ? $this->LOCAL_LANG['date_format'] : '%m/%d/%y';
	}

	// show overview stats for given org
	function overviewStats() {
		$this->marker['###ADMIN_OVERVIEW_STATS_HEADER###'] = 'Overview Stats';
		// organization name
		$this->marker['###ORGANIZATION_NAME_LABEL###'] = 'Organization Name: ';
		$this->marker['###ORGANIZATION_NAME###'] = '';

		$activePastMonth = 0;
		$activePastWeek = 0;
		$completedClasses = 0;
		$studentCheck = array();
		
		foreach ($this->studentData as $student) {
			$numRecs = count($student);
			foreach ($student as $stdData) {
//				$numRecs--;
//				if ($numRecs > 0) {
//					if ($stdData['lesson_completed']) {
//						if (strlen($completedClasses)) $completedClasses .= ', ';
//						$completedClasses .= $this->classList[$stdData['current_class']]['title'];
//					}
//					continue;
//				}
				if ($v = $stdData['class_completed']) {
					$completedClasses++;
				}				
				if ($studentCheck[$stdData['user_id']])
					continue;
				$studentCheck[$stdData['user_id']]++;
				if ($v = $stdData['is_online']) {
					if ($v >= mktime(0,0,0,date("m")-1,date("d"),date("Y"))) {
						$activePastMonth++;
					}
					if ($v >= mktime(0,0,0,date("m"),date("d")-7,date("Y"))) {
						$activePastWeek++;
					}
				}
			}
		}

		// how many students
		$this->marker['###OVERVIEW_NUM_USERS_LABEL###'] = '# of students: ';
		$this->marker['###OVERVIEW_NUM_USERS###'] = count($this->studentData);

		// how many logged in past month
		$this->marker['###OVERVIEW_USERS_PAST_MONTH_LABEL###'] = 'active in past month: ';
		$this->marker['###OVERVIEW_USERS_PAST_MONTH###'] = $activePastMonth;
		$this->marker['###OVERVIEW_USERS_PAST_WEEK_LABEL###'] = 'active in past week: ';
		$this->marker['###OVERVIEW_USERS_PAST_WEEK###'] = $activePastWeek;

		// how many have completed
		$this->marker['###OVERVIEW_COMPLETED_COURSES_LABEL###'] = 'completed courses: ';
		$this->marker['###OVERVIEW_COMPLETED_COURSES###'] = $completedClasses;

		// how many users
		$this->marker['###OVERVIEW_TOTAL_USERS_LABEL###'] = 'total users: ';
		$this->marker['###OVERVIEW_TOTAL_USERS###'] = $this->userCount;

		// new students in last month
		$this->marker['###OVERVIEW_NEW_USERS_LABEL###'] = 'new users(last mo): ';
		$this->marker['###OVERVIEW_NEW_USERS###'] = $this->newUserCount;
	}

	// Format field accordingly
	function formatField($fld, $stdData) {
		$val = $stdData[$fld];
		switch ($fld) {
			case 'location':
					$val = '';
					if ($stdData['address']) $val .= $stdData['address'];
					if ($stdData['city']) $val .= (strlen($val) ? ',' : '') . $stdData['city'];
					if ($stdData['zone']) $val .= (strlen($val) ? ',' : '') . $stdData['zone'];
					break;
			case 'attends':
					$val = $stdData['tx_wecuser_attends'] ? 'yes' : 'no'; break;
			case 'registered_date':
					$val = $stdData['crdate'];
					if ($val) $val = $this->getStrftime($this->date_format,$val);
					break;
			case 'assessment_score':
					$val = $stdData['tx_wecmaxpoint_assessment_score']; break;
			case 'current_class':
					$val = $this->classList[$val]['title']; break;
			case 'current_lesson':
					$val = $this->lessonList[$val]['title']; break;
			case 'class_start':
					$val = $stdData['crdate'];
					if ($val) $val = $this->getStrftime($this->date_format,$val);
					break;
			case 'class_completed':
					if ($stdData['class_completed']) {
						$val = $stdData['tstamp'];
						if ($val) $val = $this->getStrftime($this->date_format,$val);
					}
					break;
			case 'lastlogin':
					if ($val) $val = $this->getStrftime($this->date_format,$val);
					break;
			case 'is_online':
					if ($val) $val = $this->getStrftime($this->date_format,$val);
					break;
			case 'completed_classes':
					$val = !empty($this->completedClasses) ? $this->completedClasses : 'none';
					break;
			default:

		}
		return $val;
	}

	// this will show an overview of students for [org]
	//		- list # students
	//		- list avg students progress
	//		- list avg students assessment
	//		-
	function studentOverviewReport() {
		$reportName = $this->parentObj->pi_getLL('admin_student_overview_label','Student Overview Report');
		$dispFields = array('name','email','location','registered_date','current_class','completed_classes','is_online');
		$this->csvArray = array();
		$this->csvFileName = $this->parentObj->pi_getLL('admin_student_overview_file','Student_Overview');

		// override the display fields
		if ($newFieldsArray = $this->addFields($this->parentObj->conf['studentReportFields'])) {
			$dispFields = $newFieldsArray;
		}
		// print header
		$output .= '<div id="report-table-header">'.$reportName.'</div>';
		$output .= '<table id="report-table">';

		// print header row
		$output .= '<thead><tr>';
		foreach ($dispFields as $dsp) {
			$output .= '<th scope="col">' . $this->dispFieldNames[$dsp] . '</th>';
			$this->csvArray[] = $this->dispFieldNames[$dsp];
		}
		$this->csvArray[] = '\n';
		$output .= '</tr></thead>';
		$output .= '<tbody>';

		// go through each student and print out info
		foreach ($this->studentData as $student) {
			$numRecs = count($student);
			$this->completedClasses = "";
			foreach ($student as $stdData) {
				$numRecs--;
				if ($numRecs > 0) {
					if ($stdData['class_completed']) {
						if (strlen($this->completedClasses)) $this->completedClasses .= ', ';
						$this->completedClasses .= $this->classList[$stdData['current_class']]['title'];
					}
					continue;
				}
				$output .= '<tr>';
				foreach ($dispFields as $fld) {
					$output .= '<td>';
					$val = $this->formatField($fld,$stdData);
					$output .= $val;
					$output .= ' ';
					$output .= '</td>';
					$this->csvArray[] = $val;
				}
				$this->csvArray[] = '\n';
				$newStudent = false;
				$output .= '</tr>';
			}
		}
		$output .= '</tbody></table>';
		$this->marker['###REPORT###'] = $output;
		$this->makeCSVFile();
	}

	// this is a detailed report
	// 	- this will list students for the optional given org
	// 	- will show all courses/classes and completion
	// 	- will show how much time has spent online
	// 	- will show # logins and last login
	// 	- will show assessment via hook
	function studentDetailedReport() {

		$this->marker['###REPORT###'] = 'No report...yet';
	}

	// this will show a report for all courses/classes
	//   show each class and how many students enrolled, attempted, completed, average time to complete
	function classDetailedReport() {
		$reportName = $this->parentObj->pi_getLL('admin_class_detailed_label','Class Detailed Report');
		$this->csvArray = array();
		$this->csvFileName = $this->parentObj->pi_getLL('admin_class_overview_file','Class_Overview');

		$dispFields = array('name','email','location','current_lesson', 'registered_date','is_online');

		// override the display fields
		if ($newFieldsArray = $this->addFields($this->parentObj->conf['classReportFields'])) {
			$dispFields = $newFieldsArray;
		}

		// print header
		$output .= '<div id="report-table-header">'.$reportName.'</div>';
		$output .= '<table id="report-table">';

		// print header row
		$output .= '<thead><tr>';
		$headerName = $this->parentObj->pi_getLL('admin_class_name_header','Class Name');
		$output .= '<th score="col">' . $headerName . '</th>';
		$this->csvArray[] = $headerName;
		foreach ($dispFields as $dsp) {
			$output .= '<th scope="col">'.$this->dispFieldNames[$dsp].'</th>';
			$this->csvArray[] = $this->dispFieldNames[$dsp];
		}
		$this->csvArray[] = '\n';
		$output .= '</tr></thead>';
		// print each line of data
		$output .= '<tbody>';
		// go through each course/class
		foreach ($this->classList as $theClass) {
			$output .= '<tr><td style="font-weight:bold;font-size:110%;">' . $theClass['title'] . '</td></tr>';
			$numInClass = 0;
			foreach ($this->studentData as $student) {
				foreach ($student as $stdData) {
					if ($stdData['current_class'] == $theClass['uid']) {
						$numInClass++;
						$output .= '<tr><td> </td>';
						// add class title for each row
						$this->csvArray[] = $theClass['title'];
						// display all the fields
						foreach ($dispFields as $fld) {
							$output .= '<td>';
							$val     = $this->formatField($fld,$stdData);
							$output .= $val;
							$output .= ' ';
							$output .= '</td>';
							$this->csvArray[] = $val;
						}
						$this->csvArray[] = '\n';
						$newStudent = false;
						$output .= '</tr>';
					}
				}
			}
		}

		$output .= '</tbody></table>';
		$this->marker['###REPORT###'] = $output;

		$this->makeCSVFile();
	}

	// this will show a report for a given organization
	// a unique org is based on the pid
	function classSnapshotReport() {

		// show total registered users
		$reportName = $this->parentObj->pi_getLL('admin_snapshot_class_label','Class Snapshot Report');

		$dispFields = array('class','num_in_class','percent_in_class','num_completed','percent_completed','avg_days_to_complete');

		// override the display fields
		if ($newFieldsArray = $this->addFields($this->parentObj->conf['snapshotReportFields'])) {
			$dispFields = $newFieldsArray;
		}

		// print header
		$output .= '<div id="report-table-header">'.$reportName.'</div>';
		$output .= '<table id="report-table">';

		// print header row
		$output .= '<thead><tr>';
		$output .= '<th score="col">' . $this->parentObj->pi_getLL('admin_class_name_header','Class Name') . '</th>';
		foreach ($dispFields as $dsp) {
			$output .= '<th scope="col">' . $this->LOCAL_LANG[$dsp] . '</th>';
		}
		$output .= '</tr></thead>';

		// print each line of data
		$output .= '<tbody>';

		// go through each course/class and calculate
		$numInClass = array();
		$numCompleted = array();
		$timeToComplete = array();
		$numStudents = array();
		$totalStudents = 0;
		foreach ($this->classList as $theClass) {
			foreach ($this->studentData as $student) {
				foreach ($student as $stdData) {
					if ($stdData['current_class'] == $theClass['uid']) {
						$clsid = $theClass['uid'];
						$numStudents[$clsid]++;
						$numInClass[$clsid]++;
						$totalStudents++;
						if ($timeEnded = $stdData['class_completed']) {
							$numCompleted[$clsid]++;
							$timeToComplete[$clsid] += ($timeEnded - $stdData['crdate']);
						}
					}
				}
			}
		}

		// now output the report....
		foreach ($this->classList as $theClass) {
			$output .= '<tr><td style="font-weight:bold;font-size:110%;">' . $theClass['title'] . '</td>';
			$clsid = $theClass['uid'];
			foreach ($dispFields as $fld) {
				$output .= '<td align=center>';
				switch ($fld) {
					case 'class':
						$val = $theClass['name'];
						break;
					case 'num_in_class':
						$val = $numStudents[$clsid] ? $numStudents[$clsid] : '0';
						break;
					case 'percent_in_class':
						$val = number_format(($numStudents[$clsid] / $totalStudents) * 100, 1) . '%';
						break;
					case 'num_completed':
						$val = $numCompleted[$clsid] ? $numCompleted[$clsid] : '0';
						break;
					case 'percent_completed':
						$val = $numInClass[$clsid] ? number_format(($numCompleted[$clsid] / $numInClass[$clsid]) * 100, 1) . '%' : '0%';
						break;
					case 'avg_days_to_complete':
						// convert to days
						$val = $numCompleted[$clsid] ? number_format(($timeToComplete[$clsid] / $numCompleted[$clsid]) / (3600 * 24), 1) : '0';
						break;
				}
				$output .= $val;
				$output .= ' ';
				$output .= '</td>';
			}
			$output .= '</tr>';
		}

		$output .= '</tbody><table>';
		$this->marker['###REPORT###'] = $output;

		//$this->makeCSVFile();
	}

	// this will list all organizations in a given install, with overview of students enrolled, active students, lessons completed, courses/classes completed
	function allOrgReport() {
		// @todo check for admin access to this. If not, then return...


		// setup fields
		$reportName = $this->parentObj->pi_getLL('admin_master_org_label','Master Organization Report');
		$dispFields = array('org_name','num_students','num_active_in_3_months','num_active_in_1_month','num_percent_completed','num_assessment');
		$this->csvArray = array();
		$this->csvFileName = $this->parentObj->pi_getLL('admin_master_org_file','Master_Organization');

		// override the display fields
		if ($newFieldsArray = $this->addFields($this->parentObj->conf['masterReportFields'])) {
			$dispFields = $newFieldsArray;
		}

		// grab masterOrgPidList and parse
		$masterPidList = $this->conf['masterPidList'];
		if (strlen($masterPidList)) {
			// parse all pids
			$pidList = t3lib_div::trimExplode(',',$masterPidList);

			// print header
			$output .= '<div id="report-table-header">'.$reportName.'</div>';
			$output .= '<table id="report-table">';
			// print header row
			$output .= '<thead><tr>';
			foreach ($dispFields as $dsp) {
				$output .= '<th scope="col" style="padding-left:5px;"> ' . $this->LOCAL_LANG[$dsp] . ' </th>';
				$this->csvArray[] = $this->LOCAL_LANG[$dsp];
			}
			$this->csvArray[] = '\n';
			$output .= '</tr></thead>';
			$output .= '<tbody>';
			// grab data for each in pid list
			$orgList = array();
			foreach ($pidList as $pidData) {
				$pidParse = t3lib_div::trimExplode('|',$pidData);
				$orgPidNum  = $pidParse[0];
				$orgName = $pidParse[1];

				// fetch student data for given pid
				$this->loadStudentData($orgPidNum);


				$orgList[$orgPidNum] = $orgName;
				// go through each student and accumulate stats data
				//   STATS: number of users, number of active (past 3 months), number in each assessment, number who have completed a lesson
				//
				foreach ($this->studentData as $student) {
					$numRecs = count($student);
					$numStudents[$orgPidNum]++;
					$didComplete = false;
					foreach ($student as $stdData) {
						$numRecs--;
						// for each record stored, see if active, when active, and if completed
						if ($stdData['class_completed']) {
							$numCompleted[$orgPidNum]++;
						}
						if ($v = $stdData['is_online']) {
							if ($v >= mktime(0,0,0,date("m")-1,date("d"),date("Y"))) {
								$numActivePastMonth[$orgPidNum]++;
							}
							if ($v >= mktime(0,0,0,date("m")-3,date("d"),date("Y"))) {
								$numActivePast3Months[$orgPidNum]++;
							}
							$numActive[$orgPidNum]++;
						}
						if ($as = $stdData['tx_wecmaxpoint_assessment_score']) {
							$numAssessment[$orgPidNum][$as]++;
						}
					}
				}
				// now print out the stats data
				
				// print out header for given org
				$output .= '<tr ><td  style="background-color:#bbf;font-weight:bold;font-size:110%;" colspan="' . (count($dispFields) + 1). '">' . $orgName . '</td></tr>';

				$output .= '<tr>';
				foreach ($dispFields as $fld) {
					$val = '';
					$output .= '<td align=center>';
					switch ($fld) {
						case 'num_students':
							$val = $numStudents[$orgPidNum];
							break;
						case 'num_active_in_3_months':
							$val = $numActivePast3Months[$orgPidNum] ? $numActivePast3Months[$orgPidNum] : '0';
							break;
						case 'num_active_in_1_month':
							$val = $numActivePastMonth[$orgPidNum] ? $numActivePastMonth[$orgPidNum] : '0';
							break;
						case 'num_completed':
							$val = $numCompleted[$orgPidNum] ? $numCompleted[$orgPidNum] : '0';
							break;
						case 'percent_completed':
							$val = $numStudents[$orgPidNum] ? number_format(($numCompleted[$orgPidNum] / $numStudents[$orgPidNum]) * 100, 1) . '%' : '0%';
							break;
						case 'num_percent_completed':
							$val = $numCompleted[$orgPidNum] ? $numCompleted[$orgPidNum] : '0';
							$val = $val . ' (' . ($numStudents[$orgPidNum] ? (number_format(($numCompleted[$orgPidNum] / $numStudents[$orgPidNum]) * 100, 1) . '%') : '0%') . ')';
							break;
						case 'num_assessment':
							$val = '';
							for ($i = 1; $i <= 5; $i++) {
								if ($as = $numAssessment[$orgPidNum][$i]) 
									$val .= '#' . $i . '=' . $as . (($i < 5 ) ? ',' : '') . ' ';
							}
						
							break;
					}
					$output .= $val;
					$output .= ' ';
					$output .= '</td>';
				}				
				$this->csvArray[] = '\n';
				$output .= '</tr>';				
			}

			$output .= '</tbody></table>';
			$this->marker['###REPORT###'] = $output;
			$this->makeCSVFile();

		echo $output;
		exit();
		}
	}
	
	
	// Load data with given [org]
	// 		loads all fe_users
	//		loads all lesson, course, and class data
	//
	function loadUserData($pidList=0) {
		// take data from user_data and fe_users
		$fromStr = 'fe_users';
		
		// load student data
		$selStr = 'uid,pid,username,email,name,first_name,last_name,address,city,zone,zip,country,lastlogin,is_online,tx_wecuser_attends,tx_wecmaxpoint_assessment_score';
		
		// @todo need to allow to add additional fields here
		$where = 'deleted=0';
		if ($pidList)
			$where .= ' AND pid IN (' . $pidList . ')';

		// set sortby
		$orderBy = 'name DESC';
		// set sortby
		switch ($this->sortby) {
			case 1:	$orderby = 'name DESC'; break;
			case 2:	$orderby = 'current_class,name DESC'; break;
			case 3:	$orderby = 'lastlogin DESC'; break;
			case 4:	$orderby = ''; break;
			default: $orderby = 'name,tstamp DESC';
		}
		
		// do the query and add all data
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($selStr, $fromStr, $where, '', $orderby);
		if (mysql_error()) 
			t3lib_div::debug(array(mysql_error(), $res), "SELECT ".$selStr." FROM ".$fromStr." WHERE ".$where);		
		$this->userData = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->userData[$row['user_id']][] = $row;
		}
	}
		
	// this will show an overview of fe_users + any classes they have been in
	//		- list all students and fe_users
	//		- list avg students progress
	//		- list avg students assessment
	//		-
	function userListReport() {
		$reportName = $this->parentObj->pi_getLL('admin_user_list_label','User List Report');
		$dispFields = array('name','email','location','registered_date','current_class','completed_classes','is_online');
		$this->csvArray = array();
		$this->csvFileName = $this->parentObj->pi_getLL('admin_userlist_file','List_Overview');

		// override the display fields
		if ($newFieldsArray = $this->addFields($this->parentObj->conf['studentReportFields'])) {
			$dispFields = $newFieldsArray;
		}
		
		// print header
		$output .= '<div id="report-table-header">'.$reportName.'</div>';
		$output .= '<table id="report-table">';

		// print header row
		$output .= '<thead><tr>';
		foreach ($dispFields as $dsp) {
			$output .= '<th scope="col">' . $this->dispFieldNames[$dsp] . '</th>';
			$this->csvArray[] = $this->dispFieldNames[$dsp];
		}
		$this->csvArray[] = '\n';
		$output .= '</tr></thead>';
		$output .= '<tbody>';

		// go through each feuser and see if a student, and print out info
		foreach ($this->userData as $feuser) {
			$foundStudent = false;
			foreach ($this->studentData as $student) {
				if ($student['user_id'] != $feuser['uid']) 
					continue;
				$foundStudent = true;	
				$numRecs = count($student);
				$this->completedClasses = "";
				foreach ($student as $stdData) {
					$numRecs--;
					if ($numRecs > 0) {
						if ($stdData['class_completed']) {
							if (strlen($this->completedClasses)) $this->completedClasses .= ', ';
							$this->completedClasses .= $this->classList[$stdData['current_class']]['title'];
						}
						continue;
					}
					$output .= '<tr>';
					foreach ($dispFields as $fld) {
						$output .= '<td>';
						$val = $this->formatField($fld,$stdData);
						$output .= $val;
						$output .= ' ';
						$output .= '</td>';
						$this->csvArray[] = $val;
					}
					$this->csvArray[] = '\n';
					$newStudent = false;
					$output .= '</tr>';
				}
			}
			if (!$foundStudent) {
				$output .= '<tr>';
				foreach ($dispFields as $fld) {
					$output .= '<td>';
					$val = $this->formatField($fld,$feuser);
					$output .= $val;
					$output .= ' ';
					$output .= '</td>';
					$this->csvArray[] = $val;
				}
				$this->csvArray[] = '\n';
				$newStudent = false;
				$output .= '</tr>';				
			}
		}
		$output .= '</tbody></table>';
		$this->marker['###REPORT###'] = $output;
		$this->makeCSVFile();
	}	

	// show sort options
	function showSortOptions() {
		// add standard options...
		$sortDropdown = '<span>' . $this->parentObj->pi_getLL('admin_menu_sort','Sort by: ') . '</span>';
		$sortDropdown .= '<select size="1" onchange="if (this.selectedIndex >= 0) location.href=this.options[this.selectedIndex].value;">';
		$sortOptions = array(0 => 'Default', 1 => 'Name', 2 => 'Class', 3 => 'Last Login');
		$getVars = t3lib_div::_GET('tx_weclesson_pi1');
		foreach ($sortOptions as $key=>$sortName) {
			$goURL = $this->parentObj->getAbsoluteURL($GLOBALS['TSFE']->id,array('admin' => $this->parentObj->piVars['admin'], 'sortby' => $key));
			$sortDropdown .= '<option value="'.$goURL.'" ' . (($getVars['sortby'] == $key) ? "selected" : "") . '>' . $sortName . '</option>';
		}
		$sortDropdown .= '</select>';

		return $sortDropdown;
	}


	// this will write out a file and also set marker for link to download
	function makeCSVFile() {
		if (!$this->csvArray || empty($this->csvArray))
			return;

		// go through array and write out...
		$csvFileText = '';
		$csvCounter = 0;
		foreach ($this->csvArray as $val) {
			if ($val == '\n') {
				$csvCounter++;
			}
			else {
				$csvFileText[$csvCounter][] = $val;
			}
		}

		// generate a filename
		$this->csvFileName .= '-' . date('mdy');
		$this->csvFileName .= '.csv';

		// build file name + path
		$this->fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$this->csvFileName = $this->fileFunc->cleanFileName($this->csvFileName);
		$csvFilePath = PATH_site . 'typo3temp/temp/';
		$csvUniqueName = $csvFilePath . $this->csvFileName;

		// now write out the file
		$fh = fopen($csvUniqueName, 'w');
		if ($fh) {
			foreach ($csvFileText as $out) {
				fputcsv($fh, $out);
			}
		}
		fclose($fh);

		$csvDownloadPath = 'typo3temp/temp/' . $this->csvFileName;
		$this->marker['###DOWNLOAD_CSV_FILE###'] = '<a class="button" href="' . $csvDownloadPath . '"><span>'.$this->parentObj->pi_getLL('admin_download_csv','Download CSV File').'</span></a>';
	}

	/**
	*==================================================================================
	* this will add extra fields and return an array
	*	@param string	TYPO3 conf string that has new fields in format: name|description,name2|description2, where description# is optional
	*  @return array with all strings added
	*==================================================================================
	*/
	function addFields($fieldList) {
		$addArray = array();
		if (strlen($fieldList)) {
			$addFieldList = t3lib_div::trimExplode(',', $fieldList);
			foreach($addFieldList as $newField) {
				$newF = t3lib_div::trimExplode('|',$newField);
				array_push($addArray,$newF[0]);
				// update the dispFieldNames (which is locallang of field names)
				if ($newF[1] && !$this->dispFieldNames[$newF[0]]) {
					$this->dispFieldNames[$newF[0]] = $newF[1];
				}
			}
		}
		return $addArray;
	}

	/**
	*==================================================================================
	*  GetStrftime -- get strftime with locale conversion
	*
	*   @param	string		$format: format string for strftime
	*   @param	string		$content: data to format
	* 	@return formatted date string
	*==================================================================================
	*/
	function getStrftime($format,$content) {
		$content = strftime($format,$content);
		$tmp_charset = $conf['strftime.']['charset'] ? $conf['strftime.']['charset'] : $GLOBALS['TSFE']->localeCharset;
		if ($tmp_charset)	{
				$content = $GLOBALS['TSFE']->csConv($content,$tmp_charset);
		}
		return $content;
	}
}