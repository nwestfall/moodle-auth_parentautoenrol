Within a big organisation we will work with managers who need an overview of learner progress. They will be assigned a parent role inside the user context. Sadly current mass role assignment plugins do not offer us the exact functionality we require to operationalise the intended business process.

Therefore I have stripped apart auth_mcae and enrol_mentor to form auth_enrolmentor. This plugin hooks into the user_authenticated event and then checks who the user is managing. The plugin then assigns the logged in user to his employees using the role as defined in the settings. Logic in the plugin is based on comparing a value of a custom profile field to either username, email or id. This is configurable inside the settings.

Installation:
- Unzip the plugin to yourmoodle/auth and follow installation instructions on ./admin/index.php

- If not already present, create a custom profile field that is going to contain either the username, email address or id of the parent/mentor/manager.
- Fill your profile fields using your preferred method. For instance: CSV.
- Enable the plugin. When users log in and they are a manager in someone's profile, they will be automatically enrolled.

---

This plugin was forked from @eSrem enrolmentor plugin. (https://github.com/eSrem/moodle-auth_enrolmentor)

This plugin works a bit different than the original (as well as being functional on Moodle versions higher than 2.6).  It allows student(s) in the system (using a custom field) to be monitored by another user.  The user chosen to be monitoring the student will be enrolled in all the same courses as the student(s), but with permissions setup by the administrator.  This way the person can see upcoming due dates, course documents, and anything else the students can see.

Tested on
	-Moodle 2.7
	-Moodle 2.8

View the git repo at http://gitlab.fistbumpstudios.com/LoudonvilleChristianSchool/ParentAutoEnrol

Author: Nathan Westfall