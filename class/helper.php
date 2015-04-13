<?php
/**
 * Auto enrol mentors, parents or managers based on a custom profile field.
 *
 * @package    auth
 * @subpackage parentautoenrol
 * @copyright  2015 Nathan Westfall (nathan@fistbumpstudios.com) ORIGINAL: 2013 Virgil Ashruf (v.ashruf@avetica.nl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class parentautoenrol_helper {
	
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
	* get_enrolled_courses($userids)
	* $userids = array of user IDs
	*
	* returns an array of course ids from the given users array
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
	* get_enrolled_courses($userid)
	* $userid = single userid
	*
	* returns an array of course ids that myself is enrolled in
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
	 * Nathan Westfall
	 *
	 * doCEnrol($toEnrol, $roleid, $user);
	 * 
	 * returns an array of user ids that this user need to be enrolled in
	 *
	 */
	static public function doCEnrol($toEnrol, $roleid, $user){
		foreach($toEnrol as $enrol) {
			parentautoenrol_helper::enroll_to_course($enrol, $user->id, $roleid);
		}
	}
	
	/**
	 * Nathan Westfall
	 *
	 * doCUnenrol($toUnenrol, $roleid, $user);
	 * returns an array of user ids thad this user need to be unenrolled in
	 *
	 */
	static public function doCUnenrol($toUnenrol, $roleid, $user){
		foreach($toUnenrol as $unenrol) {
			parentautoenrol_helper::unenroll_from_course($unenrol, $user->id, $roleid);
		}
	}

	/**
	* Nathan Westfall
	*
	* CODE WRITTEN BY http://stackoverflow.com/a/19711475/2592620
	*
	* enroll_to_course($courseid, $userid, $roleid)
	* $courseid - id of the course to enroll in
	* $userid - id of the user to enroll
	* $roleid - id of the role to give user in course
	* 
	* returns true or false
	*/
	static public function enroll_to_course($courseid, $userid, $roleid)  {
	    global $DB;

	    $instance = $DB->get_record('enrol', array('courseid'=>$courseid, 'enrol'=>'manual'), '*', MUST_EXIST);
	    $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
	    $today = time();
	    $timestart = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

	    if(!$enrol_manual = enrol_get_plugin('manual')) { throw new coding_exception('Can not instantiate enrol_manual'); }

	    $enrolled = $enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, 0);

	    return $enrolled;
	}

	/**
	* Nathan Westfall
	*
	* CODE TAKEN FROM http://stackoverflow.com/a/19711475/2592620 and edited to do the reverse
	*
	* enroll_to_course($courseid, $userid, $roleid)
	* $courseid - id of the course to enroll in
	* $userid - id of the user to enroll
	* $roleid - id of the role to give user in course
	*
	* returns true or false
	*/
	static public function unenroll_from_course($courseid, $userid, $roleid)  {
	    global $DB;

	    $instance = $DB->get_record('enrol', array('courseid'=>$courseid, 'enrol'=>'manual'), '*', MUST_EXIST);
	    $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
	    $today = time();
	    $timestart = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

	    if(!$enrol_manual = enrol_get_plugin('manual')) { throw new coding_exception('Can not instantiate enrol_manual'); }

	    $enrolled = $enrol_manual->unenrol_user($instance, $userid, $roleid, $timestart, 0);

	    return $enrolled;
	}	
}