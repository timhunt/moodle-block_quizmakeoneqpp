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
 * This block adds a link to the quiz attempt page, and if the studen clicks it,
 * then their quiz attempt is converted to one question per page. This is
 * possibly a work-around for some of quiz accessibility issues.
 *
 * @package   block_quizmakeoneqpp
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_quizmakeoneqpp extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_quizmakeoneqpp');
    }

    public function applicable_formats() {
        // Can be added on the site front page, by admins only. Otherwise it
        // only appears on the quiz attempt page.
        return array('site' => true, 'mod-quiz-attempt' => true);
    }

    public function get_content () {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = '';

        // People who can edit blocks see an explanation of what this is.
        if ($this->page->pagetype != 'mod-quiz-attempt' &&
                $this->page->user_can_edit_blocks()) {
            // People who can edit blocks on the front page see an explanation.
            $this->content->text = get_string('help', 'block_quizmakeoneqpp');
        }

        // This is a bit of a hack, but every page where this block might appear
        // has a variable called $attemptobj, so we can grab it to get the data
        // we want.
        global $attemptobj, $page;

        if (empty($attemptobj) || !($attemptobj instanceof quiz_attempt)) {
            return $this->content;
        }

        if ($attemptobj->get_userid() !== $USER->id) {
            // Only show to user whose attempt it is.
            return $this->content;
        }

        // Is the quiz already one question per page?
        $count = 0;
        $currentlyoneqpp = true;
        foreach (explode(',', $attemptobj->get_attempt()->layout) as $slot) {
            $count += 1;
            if ($slot != 0 && $count % 2 == 0) {
                $currentlyoneqpp = false;
                break;
            }
        }

        if ($currentlyoneqpp) {
            // Nothing to do.
            return $this->content;
        }

        // It makes sense to show the link.
        $this->content->text = html_writer::link(
                new moodle_url('/blocks/quizmakeoneqpp/convert.php',
                        array('attempt' => $attemptobj->get_attemptid(),
                                'page' => $page, 'sesskey' => sesskey())),
                get_string('link', 'block_quizmakeoneqpp'));
        return $this->content;
    }
}
