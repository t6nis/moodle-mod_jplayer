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
 * The main jplayer configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 * 
 * @package    mod
 * @subpackage jplayer
 * @author     Tõnis Tartes <tonis.tartes@gmail.com>
 * @copyright  2013 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_jplayer_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('jplayername', 'jplayer'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'jplayername', 'jplayer');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor();

    //--------------------------------------- MEDIA SOURCE ----------------------------------------
        $mform->addElement('header', 'jplayersource', get_string('jplayersource', 'jplayer'));
        
        $mform->addHelpButton('jplayersource', 'jplayersource', 'jplayer');        
        $mform->addElement('select', 'urltype', get_string('urltype', 'jplayer'), array(0 => 'URL', 1 => 'FILE'));
        
        $mform->addElement('text', 'linkurl', get_string('linkurl', 'jplayer'), array('size' => '47')); 
        $mform->setDefault('linkurl', 'http://');
        $mform->setType('linkurl', PARAM_RAW_TRIMMED);
        // Disable my control if a checkbox is checked.
        $mform->disabledIf('linkurl', 'urltype', 'eq', 1);
        
        // jplayerfile
        $mform->addElement('filemanager', 'file', get_string('jplayerfile', 'jplayer'), null, array('subdirs' => 0, 'accepted_types' => jplayer_video_extensions()));
        $mform->addHelpButton('file', 'jplayerfile', 'jplayer');
        $mform->disabledIf('file', 'urltype', 'eq', 0);

        // type
        $mform->addElement('select', 'type', get_string('type', 'jplayer'), jplayer_list_type());
        $mform->setDefault('type', 'video');
        // streamer
        $mform->addElement('select', 'streamer', get_string('streamer', 'jplayer'), jplayer_list_streamer());
        $mform->setDefault('streamer', '');
        
    ////--------------------------------------- playlists ---------------------------------------
        $mform->addElement('header', 'playlists', get_string('playlists', 'jplayer'));
        $mform->addHelpButton('playlists', 'jplayerplaylist', 'jplayer');
        // playlist
        $mform->addElement('select', 'playlistposition', get_string('playlist', 'jplayer'), array('bottom' => 'bottom', 'right' => 'right', 'none' => 'none'));
        $mform->setDefault('playlistposition', 'none');
        // playlistsize
        $mform->addElement('text', 'playlistsize', get_string('playlistsize', 'jplayer'), array('size' => '6'));
        $mform->setType('playlistsize', PARAM_INT);
        $mform->setDefault('playlistsize', '260');

    //--------------------------------------- BEHAVIOUR ---------------------------------------
        $mform->addElement('header', 'behaviour', get_string('behaviour', 'jplayer'));
        $mform->addHelpButton('behaviour', 'jplayerbehaviour', 'jplayer');
        // autostart 
        $mform->addElement('select', 'autostart', get_string('autostart', 'jplayer'), array('true' => 'true', 'false' => 'false'));
        $mform->setDefault('autostart', 'false');
        // stretching 
        $mform->addElement('select', 'stretching', get_string('stretching', 'jplayer'), array('none' => 'none', 'uniform' => 'uniform', 'exactfit' => 'exactfit', 'fill' => 'fill'));
        $mform->setDefault('stretching', 'uniform');
        $mform->setAdvanced('stretching');
        // mute 
        $mform->addElement('select', 'mute', get_string('mute', 'jplayer'), array('true' => 'true', 'false' => 'false'));
        $mform->setDefault('mute', 'false');
        $mform->setAdvanced('mute');
        //controls
        $mform->addElement('select', 'controls', get_string('controls', 'jplayer'), array('true' => 'true', 'false' => 'false'));
        $mform->setDefault('controls', 'true');
        $mform->setAdvanced('controls');
        //repeat
        $mform->addElement('select', 'jplayerrepeat', get_string('jplayerrepeat', 'jplayer'), array('true' => 'true', 'false' => 'false'));
        $mform->setDefault('jplayerrepeat', 'false');
        $mform->setAdvanced('repeat');
        
    //--------------------------------------- APPEARANCE ---------------------------------------
        $mform->addElement('header', 'appearance', get_string('appearance', 'jplayer'));
        $mform->addHelpButton('appearance', 'jplayerappearance', 'jplayer');
        // title
        $mform->addElement('text', 'title', get_string('title', 'jplayer'), array('size' => '80'));
        $mform->setType('title', PARAM_TEXT);
        // width
        $mform->addElement('text', 'width', get_string('width', 'jplayer'), array('size' => '6'));
        $mform->setType('width', PARAM_RAW_TRIMMED);
        //$mform->addRule('width', get_string('required'), 'required', null, 'client');
        $mform->setDefault('width', '100%');
        // height
        $mform->addElement('text', 'height', get_string('height', 'jplayer'), array('size' => '6'));
        $mform->setType('height', PARAM_INT);
        //$mform->addRule('height', get_string('required'), 'required', null, 'client');
        $mform->setDefault('height', '480');
        // image
        $mform->addElement('filemanager', 'image', get_string('image', 'jplayer'), null, array('subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => jplayer_image_extensions(), 'mainfile' => true ));
        $mform->addHelpButton('image', 'image', 'jplayer');
        $mform->setAdvanced('image');
        $mform->disabledIf('image', 'type', 'eq', 'ytplaylist');
        //notes
        $mform->addElement('editor', 'notes_editor', get_string('notes', 'jplayer'), null, array('trusttext'=>true, 'subdirs'=>true, 'maxfiles'=>3));
        $mform->setType('notes_editor', PARAM_RAW);
        $mform->setAdvanced('notes_editor');
        
    //--------------------------------------- captions ---------------------------------------
        $mform->addElement('header', 'captions', get_string('captions', 'jplayer'));
        $mform->addHelpButton('captions', 'jplayercaptions', 'jplayer');
        // captionsback
        $mform->addElement('select', 'captionsback', get_string('captionsback', 'jplayer'), array('true' => 'true', 'false' => 'false'));
        $mform->setDefault('captionsback', 'false');
        // captionsfile
        $mform->addElement('filemanager', 'captionsfile', get_string('captionsfile', 'jplayer'), null, array('subdirs' => 0, 'accepted_types' => jplayer_captions_extensions()));
        $mform->addHelpButton('captionsfile', 'captionsfile', 'jplayer');
        $mform->setAdvanced('captionsfile');
        $mform->disabledIf('captionsfile', 'type', 'eq', 'ytplaylist');
        // captionsfontsize
        $mform->addElement('text', 'captionsfontsize', get_string('captionsfontsize', 'jplayer'), array('size' => 6));
        $mform->setType('captionsfontsize', PARAM_INT);
        $mform->setDefault('captionsfontsize', '14');
        // captionsstate
        $mform->addElement('select', 'captionsstate', get_string('captionsstate', 'jplayer'), array('true' => 'true', 'false' => 'false'));
        $mform->setDefault('captionsstate', 'false');
        $mform->setAdvanced('captions');
        
        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
        
    function data_preprocessing(&$default_values) {
        
        global $CFG;

        if ($this->current->instance) {
            if ($this->current->urltype == 0) {
                $default_values['linkurl'] = $this->current->jplayerfile;
            }
            //media file
            $draftitemid = file_get_submitted_draft_itemid('file');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_jplayer', 'file', 0, array('subdirs'=>0));
            $default_values['file'] = $draftitemid;
            //image file
            $draftitemid = file_get_submitted_draft_itemid('image');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_jplayer', 'image', 0, array('subdirs'=>0));
            $default_values['image'] = $draftitemid;
            //notes
            $notes_data = file_prepare_standard_editor($this->current, 'notes', array('trusttext'=>true, 'subdirs'=>0, 'maxfiles'=>3, 'maxbytes'=>$CFG->maxbytes, 'context' => $this->context), $this->context, 'mod_jplayer', 'notes', 0);
            $default_values['notes_editor'] = $notes_data->notes_editor;
            //captions file
            $draftitemid = file_get_submitted_draft_itemid('captionsfile');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_jplayer', 'captionsfile', 0, array('subdirs'=>0));
            $default_values['captionsfile'] = $draftitemid;
        }
    }
    
}
