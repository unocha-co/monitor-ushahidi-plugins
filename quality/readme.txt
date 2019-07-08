=== About ===
name: Reports quality
website: http://www.ushahidi.com
description: Adds a review field in edit form, in which, super admin user can write the review of information quality of report, and send an autohor's email
alert of the review
version: 0.1
requires: 2.5
tested up to: 2.5
author: OCHA Colombia - Ruben Rojas
author website: www.salahumanitaria.co

== Description ==
description: Adds a review field in edit form, in which, super admin user can write the review of information quality of report, and send an autohor's email
alert of the review

== Installation ==
0. Add the plugin event (copy and paste the code below) in the source of the views:
      a) application/views/admin/reports/edit.php
-----------------------------------------------------------------------------------------
	<?php
	// Action::quality_form - Runs right after location
	Event::run('ushahidi_action.quality_form');
	?>
-----------------------------------------------------------------------------------------
1. Copy the entire /sourcedetail/ directory into your /plugins/ directory.
2. Activate the plugin.
3. Execute all sql files in sql/ folder

== Changelog ==
