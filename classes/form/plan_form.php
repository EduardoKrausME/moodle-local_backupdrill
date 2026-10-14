<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * /**.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_backupdrill\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/** A site-admin-only plan form. */
final class plan_form extends \moodleform {
    /** @return void */
    protected function definition(): void {
        $form = $this->_form;
        $form->addElement('text', 'name', get_string('planname', 'local_backupdrill'), ['size' => 50]);
        $form->setType('name', PARAM_TEXT);
        $form->addRule('name', null, 'required');
        $form->addElement('filepicker', 'backupfile', get_string('sourcefile', 'local_backupdrill'), null,
            ['accepted_types' => ['.mbz'], 'maxbytes' => 536870912]);
        $existing = $this->_customdata['existingfiles'] ?? [0 => get_string('uploadnew', 'local_backupdrill')];
        $form->addElement('select', 'existingfileid',
            get_string('existingfile', 'local_backupdrill'), $existing);
        $form->setType('existingfileid', PARAM_INT);
        $form->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_backupdrill'));
        $form->addElement('text', 'intervalhours', get_string('intervalhours', 'local_backupdrill'));
        $form->setType('intervalhours', PARAM_INT);
        $form->setDefault('intervalhours', 24);
        $form->addElement('text', 'maxseconds', get_string('maxseconds', 'local_backupdrill'));
        $form->setType('maxseconds', PARAM_INT);
        $form->setDefault('maxseconds', 1800);
        $form->addElement('advcheckbox', 'autocleanup', get_string('autocleanup', 'local_backupdrill'));
        $this->add_action_buttons(true, get_string('saveplan', 'local_backupdrill'));
    }
}
