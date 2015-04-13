<?php

class enrolmentor_helper {
	
	/**
	 * __construct() HIDE: WE'RE STATIC
	 */
	protected function __construct()
	{
		// static's only please!
	}
	
	/**
	 * get_enrolled_employees($roleid, $userid) 
	 * returns an array of user ids that resemble the userid's the user is enrolled in
	 *
	 */
	static public function get_enrolled_employees($roleid, $userid) {
		global $DB;
		$list = array();
		
		$sql  = "SELECT c.instanceid
				FROM {context} AS c
				JOIN {role_assignments} AS ra ON ra.contextid = c.id
				WHERE ra.roleid='{$roleid}'
				AND ra.userid='{$userid}'";
		
		$list = array_keys($DB->get_records_sql($sql));
		
		return $list;		
	}

	/**
	* Nathan Westfall
	*
	* get_enrolled_courses
	* returns an array of course ids
	*
	*/
	static public function get_enrolled_courses($userids) {
		global $DB;
		$list = array();

		foreach($userids as $user) {
			$sql = "SELECT c.id
					FROM {course} c
					 INNER JOIN {context} cx ON c.id = cx.instanceid AND cx.contextlevel = '50'
					 INNER JOIN {role_assignments} ra ON cx.id = ra.contextid
					 INNER JOIN {role} r ON ra.roleid = r.id
					 INNER JOIN {user} usr ON ra.userid = usr.id
					WHERE usr.id = '{$user}'
					ORDER BY c.fullname, usr.firstname";
			$list = array_unique(array_merge($list, array_keys($DB->get_records_sql($sql))));
		}

		return $list;
	}

	/**
	* Nathan Westfall
	*
	* get_enrolled_courses
	* returns an array of course ids
	*
	*/
	static public function get_my_enrolled_courses($userid) {
		global $DB;
		$list = array();

		$sql = "SELECT c.id
				FROM {course} c
				 INNER JOIN {context} cx ON c.id = cx.instanceid AND cx.contextlevel = '50'
				 INNER JOIN {role_assignments} ra ON cx.id = ra.contextid
				 INNER JOIN {role} r ON ra.roleid = r.id
				 INNER JOIN {user} usr ON ra.userid = usr.id
				WHERE usr.id = '{$userid}'
				ORDER BY c.fullname, usr.firstname";

		$list = array_keys($DB->get_records_sql($sql));

		return $list;
	}

	/**
	 * get_list_empolyees($user, $username)
	 * returns an array of user ids that resemble the userid's the user is enrolled in
	 *
	 */
	static public function get_list_employees($user, $username, $switch) {
		global $DB;
		$list = array();
		
		switch($switch->compare) {
			case 'username':
				$sql = "SELECT userid FROM {user_info_data}
				WHERE data = '{$username}'
				AND fieldid = '{$switch->profile_field}'";
				break;
			case 'id':
				$sql = "SELECT userid FROM {user_info_data}
				WHERE data = '{$user->id}'
				AND fieldid = '{$switch->profile_field}'";
				break;
			case 'email':
				$sql = "SELECT userid FROM {user_info_data}
				WHERE data = '{$user->email}'
				AND fieldid = '{$switch->profile_field}'";
				break;
		}
		
		$list = array_keys($DB->get_records_sql($sql));
		
		return $list;
	}
	
	/**
	 * get_profile_fields(null);
	 * returns an array of custom profile fields
	 *
	 */	
	static public function get_profile_fields() {
		global $DB;
		
		$fields = $DB->get_records_menu('user_info_field', null, null, $fields = 'id, shortname');

		return $fields;
	}
	
	/**
	 * doEnrol($toEnrol);
	 * returns an array of user ids that this user need to be enrolled in
	 *
	 */
	// static public function doEnrol($toEnrol, $roleid, $user){
	// 	foreach($toEnrol as $enrol) {
	// 		echo "<p>ik enrol " . $user->id . "met rol " . $roleid . "in " . context_user::instance($enrol)->id . "</p>";
	// 		role_assign($roleid, $user->id, context_user::instance($enrol)->id, '', 0, '');
	// 	}
	// }
	
	/**
	 * doUnenrol($toUnenrol);
	 * returns an array of user ids thad this user need to be unenrolled in
	 *
	 */
	// static public function doUnenrol($toUnenrol, $roleid, $user){
	// 	foreach($toUnenrol as $unenrol) {
	// 		echo "<p>ik unenrol " . $user->id . "met rol " . $roleid . "in " . context_user::instance($unenrol)->id . "</p>";
	// 		role_unassign($roleid, $user->id, context_user::instance($unenrol)->id, '', 0, '');
	// 	}
	// }

	/**
	 * Nathan Westfall
	 *
	 * doCEnrol($toEnrol);
	 * returns an array of user ids that this user need to be enrolled in
	 *
	 */
	static public function doCEnrol($toEnrol, $roleid, $user){
		foreach($toEnrol as $enrol) {
			//echo "<p>ik enrol " . $user->id . "met rol " . $roleid . "in " . context_user::instance($enrol)->id . "</p>";
			//role_assign($roleid, $user->id, context_user::instance($enrol)->id, '', 0, '');
			enrolmentor_helper::enroll_to_course($enrol, $user->id, $roleid);
		}
	}
	
	/**
	 * Nathan Westfall
	 *
	 * doCUnenrol($toUnenrol);
	 * returns an array of user ids thad this user need to be unenrolled in
	 *
	 */
	static public function doCUnenrol($toUnenrol, $roleid, $user){
		foreach($toUnenrol as $unenrol) {
			//echo "<p>ik unenrol " . $user->id . "met rol " . $roleid . "in " . context_user::instance($unenrol)->id . "</p>";
			//role_unassign($roleid, $user->id, context_user::instance($unenrol)->id, '', 0, '');
			enrolmentor_helper::unenroll_from_course($unenrol, $user->id, $roleid);
		}
	}

	/**
	* Nathan Westfall
	*
	* enroll_to_course($courseid, $userid, $roleid, $extendbase, $extendperiod)
	* CODE WRITTEN BY http://stackoverflow.com/a/19711475/2592620
	*/
	static public function enroll_to_course($courseid, $userid, $roleid=5, $extendbase=3, $extendperiod=0)  {
	    global $DB;

	    $instance = $DB->get_record('enrol', array('courseid'=>$courseid, 'enrol'=>'manual'), '*', MUST_EXIST);
	    $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
	    $today = time();
	    $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

	    if(!$enrol_manual = enrol_get_plugin('manual')) { throw new coding_exception('Can not instantiate enrol_manual'); }
	    switch($extendbase) {
	        case 2:
	            $timestart = $course->startdate;
	            break;
	        case 3:
	        default:
	            $timestart = $today;
	            break;
	    }  
	    if ($extendperiod <= 0) { $timeend = 0; }   // extendperiod are seconds
	    else { $timeend = $timestart + $extendperiod; }
	    $enrolled = $enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
	    //add_to_log($course->id, 'course', 'enrol', '../enrol/users.php?id='.$course->id, $course->id); //DEPRECIATED

	    return $enrolled;
	}

	/**
	* Nathan Westfall
	*
	* enroll_to_course($courseid, $userid, $roleid, $extendbase, $extendperiod)
	* CODE TAKEN FROM http://stackoverflow.com/a/19711475/2592620
	*/
	static public function unenroll_from_course($courseid, $userid, $roleid=5, $extendbase=3, $extendperiod=0)  {
	    global $DB;

	    $instance = $DB->get_record('enrol', array('courseid'=>$courseid, 'enrol'=>'manual'), '*', MUST_EXIST);
	    $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
	    $today = time();
	    $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

	    if(!$enrol_manual = enrol_get_plugin('manual')) { throw new coding_exception('Can not instantiate enrol_manual'); }
	    switch($extendbase) {
	        case 2:
	            $timestart = $course->startdate;
	            break;
	        case 3:
	        default:
	            $timestart = $today;
	            break;
	    }  
	    if ($extendperiod <= 0) { $timeend = 0; }   // extendperiod are seconds
	    else { $timeend = $timestart + $extendperiod; }
	    $enrolled = $enrol_manual->unenrol_user($instance, $userid, $roleid, $timestart, $timeend);
	    //add_to_log($course->id, 'course', 'enrol', '../enrol/users.php?id='.$course->id, $course->id); //DEPRECIATED

	    return $enrolled;
	}	
}