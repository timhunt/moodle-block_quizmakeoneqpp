<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Script that converts an attempt to one questoin per page.
 *
 * @package   block_quizmakeoneqpp
 * @copyright 2014 the Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

// Get params.
$attemptid = required_param('attempt', PARAM_INT);
$currentpage = required_param('page', PARAM_INT);

// Load required data.
$attemptobj = quiz_attempt::create($attemptid);

// Setup page.
$currentpage = $attemptobj->force_page_number_into_range($currentpage);
$PAGE->set_url($attemptobj->attempt_url(null, $currentpage));

// Check login and legitimate access.
require_login($attemptobj->get_course(), false, $attemptobj->get_cm());
if ($attemptobj->get_userid() != $USER->id) {
    throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'notyourattempt');
}
require_sesskey();

// Do the repagination, inclusing working out the equivalent new page number
// for the page we were on.
$oldlayout = $attemptobj->get_attempt()->layout;
$oldpage = 0;
$newpage = 0;
$pagefound = false;
$newlayout = array();
foreach (explode(',', $oldlayout) as $slot) {
    if ($slot == 0) {
        $oldpage += 1;
        if (!$pagefound && $oldpage == $currentpage) {
            $pagefound = true;
            $currentpage = $newpage;
        }
        continue;
    }
    $newlayout[] = $slot;
    $newlayout[] = 0;
    $newpage += 1;
}

// Save the new layout.
$DB->set_field('quiz_attempts', 'layout', implode(',', $newlayout), array('id' => $attemptid));

// Redirect back to the attemtp.
redirect($attemptobj->attempt_url(null, $currentpage));
