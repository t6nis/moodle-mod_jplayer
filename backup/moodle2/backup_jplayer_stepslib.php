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

/**
 * Define the complete jplayer structure for backup, with file and id annotations
 */
class backup_jplayer_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        // jplayer user data is in the xmldata field in DB - anyway lets skip that

        // Define each element separated
        $jplayer = new backup_nested_element('jplayer', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated', 'timemodified',
            'urltype', 'jplayerfile', 'type', 'streamer', 'playlistposition', 
            'playlistsize', 'autostart', 'stretching', 'mute', 'controls', 
            'jplayerrepeat', 'title', 'width', 'height', 'image', 'notes',
            'notesformat', 'captionsback', 'captionsfile', 'captionsfontsize', 
            'captionsstate'));

        // Build the tree

        // Define sources
        $jplayer->set_source_table('jplayer', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations

        // Define file annotations
        $jplayer->annotate_files('mod_jplayer', 'intro', null);
        $jplayer->annotate_files('mod_jplayer', 'notes', null);
        $jplayer->annotate_files('mod_jplayer', 'file', null);
        $jplayer->annotate_files('mod_jplayer', 'captionsfile', null);
        $jplayer->annotate_files('mod_jplayer', 'image', null);

        // Return the root element (jplayer), wrapped into standard activity structure
        return $this->prepare_activity_structure($jplayer);
    }
}
