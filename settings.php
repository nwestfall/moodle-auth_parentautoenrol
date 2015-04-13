<?php
/**
 * Auto enrol mentors, parents or managers based on a custom profile field.
 *
 * @package    auth
 * @subpackage parentautoenrol
 * @copyright  2015 Nathan Westfall (nathan@fistbumpstudios.com) ORIGINAL: 2013 Virgil Ashruf (v.ashruf@avetica.nl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $USER;

require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/auth/parentautoenrol/class/helper.php');

if ($ADMIN->fulltree) {

	// Get all roles and put their id's nicely into the configuration.
	$roles = get_all_roles();
	$i = 1;
	foreach($roles as $role) {
		$rolename[$i] = $role->shortname;
		$roleid[$i] = $role->id;
		$i++;
	}
	$rolenames = array_combine($roleid, $rolename);

	// Get all the profile fields for configuration
	$profilefields = parentautoenrol_helper::get_profile_fields();
	
	$settings->add(new admin_setting_configselect('auth_parentautoenrol/role', get_string('parentautoenrol_settingrole', 'auth_parentautoenrol'), get_string('parentautoenrol_settingrolehelp', 'auth_parentautoenrol'), '', $rolenames));
	$settings->add(new admin_setting_configselect('auth_parentautoenrol/compare', get_string('parentautoenrol_settingcompare', 'auth_parentautoenrol'), get_string('parentautoenrol_settingcomparehelp', 'auth_parentautoenrol'), 'username', array('username'=>'username','email'=>'email','id'=>'id')));
	$settings->add(new admin_setting_configselect('auth_parentautoenrol/profile_field', get_string('parentautoenrol_settingprofile_field', 'auth_parentautoenrol'), get_string('parentautoenrol_settingprofile_fieldhelp', 'auth_parentautoenrol'), '', $profilefields));
}