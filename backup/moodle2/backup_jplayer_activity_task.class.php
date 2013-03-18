<?php
// This file is part of jplayer module for Moodle - http://moodle.org/
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
 * @package    mod
 * @subpackage jplayer
 * @author     Tõnis Tartes <tonis.tartes@gmail.com>
 * @copyright  2013 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/jplayer/backup/moodle2/backup_jplayer_stepslib.php'); // Because it exists (must)
/**
 * jplayer backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_jplayer_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // jplayer only has one structure step
        $this->add_step(new backup_jplayer_activity_structure_step('jplayer_structure', 'jplayer.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of jplayer
        $search="/(".$base."\/mod\/jplayer\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@JPLAYERINDEX*$2@$', $content);

        // Link to jplayer view by moduleid
        $search="/(".$base."\/mod\/jplayer\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@JPLAYERVIEWBYID*$2@$', $content);

        return $content;
    }
}

?>
