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
 * TODO describe file edit
 *
 * @package    local_greetings
 * @copyright  2023 2023 Ferenc 'Frank' Fengyel ,ferenc.lengyel@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot. '/local/greetings/lib.php');

require_login();

$url = new moodle_url('/local/greetings/index.php', []);
$PAGE->set_url($url);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading(get_string('editmessage', 'local_greetings'));

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$id = required_param('id', PARAM_INT);

// Get record from the database.
if (!$result = $DB->get_record('local_greetings_messages', ['id' => $id])) {
    throw new moodle_exception('norecordfound', 'local_greetings');
}

// Double-check the delete capability.
$canedit = has_capability('local/greetings:deleteanymessage', $context) ||
    (has_capability('local/greetings:deleteownmessage', $context) && $result->userid == $USER->id);

$messageform = new \local_greetings\form\message_form(null, ['message' => $result]);

if ($messageform->is_cancelled()) {

    redirect($PAGE->url); // Go to main greetings page.

} else {

    if ($canedit && $data = $messageform->get_data()) {
        $message = required_param('message', PARAM_TEXT);

        if (!empty($message)) {
            // Only update the message. Leave other data untouched.
            $result->message = $message;

            $DB->update_record('local_greetings_messages', $result);
        }

        redirect($PAGE->url); // Go to main greetings page.
    }
}


echo $OUTPUT->header();

if ($canedit) {
    $messageform->display();
} else {
    throw new moodle_exception('cannoteditmessage', 'local_greetings');
}

echo $OUTPUT->footer();
