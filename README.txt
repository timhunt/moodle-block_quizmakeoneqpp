Make quiz one question per page block
=====================================

It has been suggested that letting students make their quiz attempt one question
per page is a work-around for some of the accessibility problems in the quiz.

This block lets you do that.


Set-up instructions
-------------------

1. Install this Moodle block plugin in the usual way.

2. As site admin, turn editing on on the site front page, and add an instance
   of this block there.

3. Configure that block, and set Page contexts to 'Display thorughout the entire site'.
   (Despite what that says, the block will only be visible during quiz attempts, and
   will be visible to admins/managers only on the front page.)


Using the block
---------------

Once the block has been installed, when a student attempts a quiz, or when a
teacher previews a quiz, if the quiz is not already set to one question per page,
then this block will appear, containing a link 'Switch to one question per page'.
Clicking that link does what it says.

If the quiz is alreay showing one question per page, then the block does not
appear.

Of course, this will only work if the quiz setting
Appearance -> Show blocks during quiz attemptsAdvanced element is set to Yes.


What if you don't want blocks visible during the quiz attempt?
--------------------------------------------------------------

Rather than displaying thins link in a separate block, you could override the
quiz renderer in your theme to show the link there. You can do that by
copying and pasting the following class into your theme's renderers.php file.
See http://docs.moodle.org/dev/Overriding_a_renderer for more details on that.

(Note, this has not been tested.)

class theme_MYTHEME_mod_quiz_renderer extends mod_quiz_renderer{
    public function countdown_timer(quiz_attempt $attemptobj, $timenow) {
        global $page; // Hack to get the current page number here.

        // Override this method to add the 'convert to one question per page'
        // link to the quiz navigation block.
        $output = parent::countdown_timer($attemptobj, $timenow);

        if ($attemptobj->get_userid() !== $USER->id) {
            // Only show to user whose attempt it is.
            return $output;
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
            return $output;
        }

        // It makes sense to show the link.
        $output .= html_writer::div(html_writer::link(
                new moodle_url('/blocks/quizmakeoneqpp/convert.php',
                        array('attempt' => $attemptobj->get_attemptid(),
                                'page' => $page, 'sesskey' => sesskey())),
                get_string('link', 'block_quizmakeoneqpp')));

        return $output;
    }
}
