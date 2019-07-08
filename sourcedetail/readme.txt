=== About ===
name: Source detail
website: http://www.ushahidi.com
description: Adds a field set for actors and victims of an event
version: 0.1
requires: 2.0
tested up to: 2.3
author: OCHA Colombia - Ruben Rojas
author website: www.colombiassh.org

== Description ==
Creates a field set for source datil with Source type, source, source date
SIDIH OCHA

== Installation ==
0. Add the plugin event (copy and paste the code below) in
      a) views/reports_submit.php
      b) application/views/admin/reports/edit.php

-----------------------------------------------------------------------------------------
	<?php
	// Action::sourcedetail_form - Runs right after location
	Event::run('ushahidi_action.sourcedetail_form');
	?>
-----------------------------------------------------------------------------------------
1. Copy the entire /sourcedetail/ directory into your /plugins/ directory.
2. Activate the plugin.
3. Execute all sql files in sql/ folder

== Changelog ==
