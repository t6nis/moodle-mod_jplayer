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
 * @package    mod
 * @subpackage jplayer
 * @author     Tõnis Tartes <tonis.tartes@gmail.com>
 * @copyright  2013 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once('locallib.php');

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function jplayer_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        default: return null;
    }
}

/**
 * Saves a new instance of the jplayer into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $jplayer An object from the form in mod_form.php
 * @param mod_jplayer_mod_form $mform
 * @return int The id of the newly inserted jplayer record
 */
function jplayer_add_instance(stdClass $jplayer, mod_jplayer_mod_form $mform = null) {
    global $DB, $CFG;

    $jplayer->timecreated = time();
    
    //Stuff
    $context = get_context_instance(CONTEXT_MODULE, $jplayer->coursemodule);

    $fs = get_file_storage();

    if ($jplayer->urltype == 1) {
        $items = array('file', 'image', 'captionsfile');
    } else {
        $items = array('image', 'captionsfile');
        $jplayer->jplayerfile = $jplayer->linkurl;
    }
    
    foreach ($items as $value) {
        
        $file = '';
        
        //media file
        $draftitemid = file_get_submitted_draft_itemid($value);
        file_prepare_draft_area($draftitemid, $context->id, 'mod_jplayer', $value, 0, array('subdirs'=>0));
        file_save_draft_area_files($draftitemid, $context->id, 'mod_jplayer', $value, 0, array('subdirs'=>0));      
        $file = $fs->get_area_files($context->id, 'mod_jplayer', $value, 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
        //getting the filename with extension
        $file_details = $fs->get_file_by_hash(key($file));

        if ($value == 'file') {
            $jplayer->jplayerfile = $file_details->get_filename();
        } else {
            if (!empty($file)) {
                $jplayer->$value = $file_details->get_filename();
            } else {
                $jplayer->$value = NULL;
            }            
        }
    
    }
    //notes
    $jplayer = file_postupdate_standard_editor($jplayer, 'notes', array('subdirs'=>0, 'maxfiles'=>3, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>true, 'context'=>$context), $context, 'mod_jplayer', 'notes', 0);
    
    return $DB->insert_record('jplayer', $jplayer);
}

/**
 * Updates an instance of the jplayer in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $jplayer An object from the form in mod_form.php
 * @param mod_jplayer_mod_form $mform
 * @return boolean Success/Fail
 */
function jplayer_update_instance(stdClass $jplayer, mod_jplayer_mod_form $mform = null) {
    global $DB, $CFG;

    $jplayer->timemodified = time();
    $jplayer->id = $jplayer->instance;

    # You may have to add extra stuff in here #
    $context = get_context_instance(CONTEXT_MODULE, $jplayer->coursemodule);

    $fs = get_file_storage();
    
    if ($jplayer->urltype == 1) {
        $items = array('file', 'image', 'captionsfile');
    } else {
        $items = $items = array('image', 'captionsfile');
        $jplayer->jplayerfile = $jplayer->linkurl;
    }
    
    foreach ($items as $value) {
        
        $file = '';
        
        //media file
        $draftitemid = file_get_submitted_draft_itemid($value);
        file_prepare_draft_area($draftitemid, $context->id, 'mod_jplayer', $value, 0, array('subdirs'=>0));
        file_save_draft_area_files($draftitemid, $context->id, 'mod_jplayer', $value, 0, array('subdirs'=>0));
        $file = $fs->get_area_files($context->id, 'mod_jplayer', $value, 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
        //getting the filename with extension
        $file_details = $fs->get_file_by_hash(key($file));
        
        if ($value == 'file') {
            $jplayer->jplayerfile = $file_details->get_filename();
        } else {
            if (!empty($file)) {
                $jplayer->$value = $file_details->get_filename();
            } else {
                $jplayer->$value = NULL;
            }            
        }
        
    }

    //notes
    $jplayer = file_postupdate_standard_editor($jplayer, 'notes', array('subdirs'=>0, 'maxfiles'=>3, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>true, 'context'=>$context), $context, 'mod_jplayer', 'notes', 0);

    return $DB->update_record('jplayer', $jplayer);
}

/**
 * Removes an instance of the jplayer from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function jplayer_delete_instance($id) {
    global $DB;

    if (! $jplayer = $DB->get_record('jplayer', array('id' => $id))) {
        return false;
    }

    # Delete any dependent records here #

    $DB->delete_records('jplayer', array('id' => $jplayer->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function jplayer_user_outline($course, $user, $mod, $jplayer) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $jplayer the module instance record
 * @return void, is supposed to echp directly
 */
function jplayer_user_complete($course, $user, $mod, $jplayer) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in jplayer activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function jplayer_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link jplayer_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function jplayer_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see jplayer_get_recent_mod_activity()}

 * @return void
 */
function jplayer_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function jplayer_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function jplayer_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of jplayer?
 *
 * This function returns if a scale is being used by one jplayer
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $jplayerid ID of an instance of this module
 * @return bool true if the scale is used by the given jplayer instance
 */
function jplayer_scale_used($jplayerid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('jplayer', array('id' => $jplayerid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of jplayer.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any jplayer instance
 */
function jplayer_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('jplayer', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give jplayer instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $jplayer instance object with extra cmidnumber and modname property
 * @return void
 */
function jplayer_grade_item_update(stdClass $jplayer) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($jplayer->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $jplayer->grade;
    $item['grademin']  = 0;

    grade_update('mod/jplayer', $jplayer->course, 'mod', 'jplayer', $jplayer->id, 0, null, $item);
}

/**
 * Update jplayer grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $jplayer instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function jplayer_update_grades(stdClass $jplayer, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/jplayer', $jplayer->course, 'mod', 'jplayer', $jplayer->id, 0, $grades);
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function jplayer_get_file_areas($course, $cm, $context) {
    $areas = array('file', 'notes', 'image', 'captionsfile');
    $areas['file'] = get_string('jplayerfile', 'jplayer');
    $areas['notes'] = get_string('notes', 'jplayer');
    $areas['image'] = get_string('image', 'jplayer');
    $areas['captionsfile'] = get_string('captionsfile', 'jplayer');
    return $areas;
}

/**
 * File browsing support for jplayer file areas
 *
 * @package mod_jplayer
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function jplayer_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        // students can not peak here!
        return null;
    }

    $fs = get_file_storage();

    if ($filearea == 'file' || $filearea == 'notes' || $filearea == 'image' || $filearea == 'captionsfile') {
    
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_jplayer', $filearea, 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_jplayer', $filearea, 0);
            } else {
                // not found
                return null;
            }
        }

        return new jplayer_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, true, false);
        
    }
    // note: resource_intro handled in file_browser automatically

    return null;
}

/**
 * Serves the files from the jplayer file areas
 *
 * @package mod_jplayer
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the jplayer's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function jplayer_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_course_login($course, true, $cm);

    if (($filearea == 'notes') || ($filearea == 'file') || ($filearea == 'image') || ($filearea == 'captionsfile')) {
        #OK Continue..
    } else {        
        return false;
    }

    $fileid = (int)array_shift($args);

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_jplayer/$filearea/$fileid/$relativepath";
    
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    
    send_stored_file($file, 360, 0, false);
    
}

/**
 * File browsing support class
 */
class jplayer_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}
////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding jplayer nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the jplayer module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function jplayer_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the jplayer settings
 *
 * This function is called when the context for the page is a jplayer module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $jplayernode {@link navigation_node}
 */
function jplayer_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $jplayernode=null) {
}


/**
* Construct Javascript SWFObject embed code for <body> section of view.php
* Please note: some URLs append a '?'.time(); query to prevent browser caching
*
* @param $jplayer (mdl_mplayer DB record for current mplayer module instance)
* @return string
*/
function jplayer_video($jplayer) {

    global $CFG, $COURSE, $CFG;

    $cm = get_coursemodule_from_instance('jplayer', $jplayer->id, $COURSE->id);        
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $video = jplayer_player_helper($jplayer, $cm, $context);

    //Notes
    $jplayer->notes = file_rewrite_pluginfile_urls($jplayer->notes, 'pluginfile.php', $context->id, 'mod_jplayer', 'notes', 0);
    $video .= html_writer::tag('div', $jplayer->notes, array('id' => 'videoNotes'));

    return $video;
}

/*
 * Enabled video extensions
 */
function jplayer_video_extensions() {
    $extensions = array(
        '.mp4',
        '.flv',
        '.webm',
        '.MP4',
        '.FLV',
        '.WEBM'
    );
    return $extensions;
}

/*
 * Enabled image extensions
 */
function jplayer_image_extensions() {
    $extensions = array(
        '.jpg',
        '.jpeg',
        '.png',
        '.JPG',
        '.JPEG',
        '.PNG'
    );
    return $extensions;
}

/*
 * Enabled captions extensions
 */
function jplayer_captions_extensions() {
    $extensions = array(
        '.vtt',
        '.srt',
        '.VTT',
        '.SRT'
    );
    return $extensions;
}