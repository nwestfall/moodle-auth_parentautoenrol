<?php
/**
 * Auto enrol mentors, parents or gaurdians based on a custom profile field.
 *
 * @package    auth
 * @subpackage parentautoenrol
 * @copyright  2015 Nathan Westifall (nathan@fistbumpstudios.com) ORIGINAL: 2013 Virgil Ashruf (v.ashruf@avetica.nl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015041500;
$plugin->requires  = 2014051200;
$plugin->component = 'auth_parentautoenrol';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0';