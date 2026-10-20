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
 * /**.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_backupdrill\local;

/**
 * Limits are deliberately bounded even when records are modified manually.
 */
final class drill_configuration {
    /**
     * Handle validate.
     *
     * @param \stdClass $plan The plan.
     */
    public static function validate(\stdClass $plan): void {
        if (trim($plan->name) === '' || \core_text::strlen($plan->name) > 255) {
            throw new \moodle_exception('invalidplan', 'local_backupdrill');
        }
        foreach (['intervalhours' => [1, 8760], 'maxseconds' => [30, 7200],
                'maxbytes' => [1024, 2147483648], 'maxattempts' => [1, 3]] as $key => $bounds) {
            if (!isset($plan->{$key}) || !is_numeric($plan->{$key}) ||
                    $plan->{$key} < $bounds[0] || $plan->{$key} > $bounds[1]) {
                throw new \moodle_exception('invalidplan', 'local_backupdrill');
            }
        }
    }
}
