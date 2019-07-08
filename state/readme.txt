=== About ===
name: State
website: http://www.ushahidi.com
description: Adds a state dropdown
version: 0.1
requires: 2.0
tested up to: 2.3
author: OCHA Colombia - Ruben Rojas
author website: www.colombiassh.org

== Description ==
Creates a field to select a state of an event and filters the cities list

== Installation ==
1. Copy the entire /state/ directory into your /plugins/ directory.
2. Activate the plugin.
3. Execute all sql files in sql/ folder, the plugin comes with Colombia states
4. Add Event::run before dropdown of select_city in the follow view:
    - themes/your_theme(or default)/views/report_submit.php
        <?php Event::run('ushahidi_action.state'); ?>

== Changelog ==
