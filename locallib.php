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
 * Internal library of functions for module jplayer
 *
 * All the jplayer specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage jplayer
 * @author Tõnis Tartes <tonis.tartes@gmail.com>
 * @copyright  2013 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
* Define type of media to serve
* @return array
*/
function jplayer_list_type() {
    return array(
        'video' => 'Video',
        'vplaylist' => 'Video Playlist',
        'youtube' => 'YouTube',
        'ytplaylist' => 'Youtube Playlist',
        'vimeo' => 'Vimeo',
        'url' => 'Full URL',
        'rss' => 'RSS Playlist',
        //'xml' => 'XML Playlist',
        //'http' => 'HTTP (pseudo) Streaming',
        //'lighttpd' => 'Lighttpd Streaming',
        'rtmp' => 'RTMP Streaming'
    );
}

/**
* HTTP streaming (Xmoov-php) not yet working!
* 
* For Lighttpd streaming or RTMP (Flash Media Server or Red5),
* enter the path to the gateway in the corresponding empty quotes
* and uncomment the appropriate lines
* e.g. 'path/to/your/gateway.jsp' => 'RTMP');
*
* For RTMP streaming, uncomment and edit this line: //, 'rtmp://yourstreamingserver.com/yourmediadirectory' => 'RTMP'
* to reflect your streaming server's details. It's probably a good idea to change the 'RTMP' bit to the name of your streaming service,
* i.e. 'My Media Server' or 'Acme Media Server'.
* Remember not to include the ".mplayer" file extensions in video file names when using RTMP.
* @return array
*/
function jplayer_list_streamer() {
    return array(
        '' => 'None',
        'rtmp://flash.ut.ee/uttv_avalik/mp4:' => 'UTTV avalik video(kirjuta väljale "Media File" ainult faili nimi)',
        //'lighttpd' => 'Lighttpd', 
    );
}

/*
 * JPlayer helper
 */
function jplayer_player_helper($jplayer, $cm, $context) {
    global $CFG, $COURSE, $CFG;
    
    $fs = get_file_storage();
    $videofiles = $fs->get_area_files($context->id, 'mod_jplayer', 'file', false, '', false);
    $captionsfiles = $fs->get_area_files($context->id, 'mod_jplayer', 'captionsfile', false, '', false);

    switch($jplayer->type): 
        case 'video':
            return jplayer_player($jplayer, $context, $videofiles, $captionsfiles);
            break;
        case 'vplaylist':
            return jplayer_player_playlist($jplayer, $context, $videofiles, $captionsfiles);
            break;
        case 'youtube':
            return jplayer_player($jplayer, $context, $videofiles, $captionsfiles);
            break;
        case 'ytplaylist': 
            return jplayer_player_playlist($jplayer, $context, $videofiles, $captionsfiles);
            break;
        case 'vimeo':
            return jplayer_player_vimeo($jplayer);
            break;
        case 'url':
            return jplayer_player($jplayer, $context, $videofiles, $captionsfiles);
            break;
        case 'rss':
            return jplayer_player_playlist($jplayer, $context, $videofiles, $captionsfiles);
            break;
        case 'rtmp':
            return jplayer_player($jplayer, $context, $videofiles, $captionsfiles);
            break;
    endswitch; 
    
}

/*
 * Embed video player
 */
function jplayer_player($jplayer, $context, $videofiles, $captionsfiles) {
    global $CFG, $COURSE, $CFG;
    
    $videos = '';
    $captions = '';
    $general_options = '';
    $playlist_options = '';
    $caption_settings = '';
    $caption_attribute = '';
    $videourl = '';
    $img_attribute = '';
    
    //Videos
    if ($jplayer->urltype == 1) {
        foreach($videofiles as $videofile) {
            $videolabel = explode('.', $videofile->get_filename());
            $videourl = moodle_url::make_file_url('/pluginfile.php', '/'.$context->id.'/mod_jplayer/file/0/'.$videofile->get_filename());
            $videos .= '{ file: "'.$videourl.'", label: "'.$videolabel[0].'" }, '; 
        }
    } else {
        $videofiles = $jplayer->streamer.$jplayer->jplayerfile;
        $videos .= '{ file: "'.$videofiles.'" }, ';
    }
        
    //Start image (Does not work for Vimeo)
    if ($jplayer->image) {
        $imgurl = moodle_url::make_file_url('/pluginfile.php', '/'.$context->id.'/mod_jplayer/image/0/'.$jplayer->image);
        $img_attribute = ' image: "'.$imgurl.'", ';
    }
    
    //Captions
    if ($jplayer->captionsstate != 'false') {
        foreach($captionsfiles as $captionsfile) {
            $captionlabel = explode('.', $captionsfile->get_filename());
            $captionsurl = moodle_url::make_file_url('/pluginfile.php', '/'.$context->id.'/mod_jplayer/captionsfile/0/'.$captionsfile->get_filename());
            $captions .= '{ file: "'.$captionsurl.'", label: "'.$captionlabel[0].'" },';
        }

        //Add captions to params
        if (count($captionsfiles) >= 1) {
            $caption_settings .= 'captions: {'.
                                    'back: '.$jplayer->captionsback.', '.
                                    'fontsize: '.$jplayer->captionsfontsize.
                                '}, ';
            $caption_attribute .= 'captions: ['.
                                    $captions.
                                   '], ';                        
        }  
    }
    $playlist_attributes = array('title');
    $general_attributes = array('controls', 'jplayerrepeat', 'autostart', 'stretching', 'mute', 'width', 'height');
    
    foreach($jplayer as $key => $value) {
        if (in_array($key, $playlist_attributes)) {
            $playlist_options .= $key.': "'.$value.'", ';
        }
        if (in_array($key, $general_attributes)) {
            if ($key == 'jplayerrepeat') {
                $key = 'repeat';
            }
            $general_options .= $key.': "'.$value.'", ';
        }
    }
    
    $attributes = 'playlist: [{'.
                    $img_attribute.
                    $caption_attribute.
                    $playlist_options.
                    'sources: ['.$videos.']'.
                  '}], ';    
    $attributes .= $caption_settings;
    $attributes .= $general_options;
    
    //Player
    $player = html_writer::tag('div', '..Loading..', array('id' => 'videoElement'));
    //JS
    $jscode = 'jwplayer("videoElement").setup({'.
               $attributes.
              '});'; 
    $player .= html_writer::script($jscode);
   
    return $player;
}

/* 
 * Embed Playlist player
 */
function jplayer_player_playlist($jplayer, $context, $videofiles, $captionsfiles) {
    global $CFG, $COURSE, $CFG, $OUTPUT;
    
    $videos = '';
    $captions = '';
    $general_options = '';
    $playlist_options = '';
    $caption_settings = '';
    $caption_attribute = '';
    $img_attribute = '';
    $videourl = '';
    $default_image = $OUTPUT->pix_url('icon_large', 'jplayer');

    //Videos
    if ($jplayer->urltype == 1) {
        foreach($videofiles as $videofile) {
            $videolabel = explode('.', $videofile->get_filename());
            $videourl = moodle_url::make_file_url('/pluginfile.php', '/'.$context->id.'/mod_jplayer/file/0/'.$videofile->get_filename());
            $videos .= '{ file: "'.$videourl.'", title: "'.$videolabel[0].'", image: "'.$default_image.'" }, '; 
        }
    } else {
        $videofiles = $jplayer->jplayerfile;
        $videos .= '{ file: "'.$videofiles.'" }, ';
    }
    
    //Captions - NOT SUPPORTED WITH PLAYLISTS ATM
    /*
    foreach($captionsfiles as $captionsfile) {
        $captionlabel = explode('.', $captionsfile->get_filename());
        $captionsurl = moodle_url::make_file_url('/pluginfile.php', '/'.$context->id.'/mod_jplayer/captionsfile/0/'.$captionsfile->get_filename());
        $captions .= '{ file: "'.$captionsurl.'", label: "'.$captionlabel[0].'" },';
    }
    
    //Add captions to params
    if (count($captionsfiles) >= 1) {
        $caption_settings .= 'captions: {'.
                                'back: '.$jplayer->captionsback.', '.
                                'fontsize: '.$jplayer->captionsfontsize.
                            '}, ';
        $caption_attribute .= 'tracks: ['.
                                $captions.
                               '], ';                        
    }
    */
    $general_attributes = array('title', 'controls', 'jplayerrepeat', 'autostart', 'stretching', 'mute', 'width', 'height');
    
    foreach($jplayer as $key => $value) {
        if (in_array($key, $general_attributes)) {
            if ($key == 'jplayerrepeat') {
                $key = 'repeat';
            }
            $general_options .= $key.': "'.$value.'", ';
        }
    }
    
    //Playlist differences
    if ($jplayer->type == 'ytplaylist') {
        if (stristr($jplayer->jplayerfile, 'youtube.com')) { 
            $playlistid = explode('list=', $jplayer->jplayerfile);
            $playlistid = explode('&', $playlistid[1]);
            $jplayer->jplayerfile = $playlistid[0];
        }
        $playlist_options .= 'playlist: "https://gdata.youtube.com/feeds/api/playlists/'.$jplayer->jplayerfile.'?alt=rss", ';
    } else if ($jplayer->type == 'rss') {
        $playlist_options .= 'playlist: "'.$videourl.'", ';
    } else if ($jplayer->type == 'vplaylist') {
        $playlist_options .= 'playlist: ['.$videos.'], ';
    }
    $playlist_options .= 'listbar: { position: "'.$jplayer->playlistposition.'", size: "'.$jplayer->playlistsize.'" }, ';
    $playlist_options .= 'primary: "flash", ';

    $attributes = $general_options;
    $attributes .= $caption_settings;
    $attributes .= $playlist_options;
    
    //Player
    $player = html_writer::tag('div', '..Loading..', array('id' => 'videoElement'));
    //JS
    $jscode = 'jwplayer("videoElement").setup({'.
               $attributes.
              '});'; 
    $player .= html_writer::script($jscode);
    
    return $player;
}

/*
 * Vimeo Player(iframe)
 */
function jplayer_player_vimeo($jplayer) {
    global $CFG, $COURSE, $CFG;
    if (stristr($jplayer->jplayerfile, 'vimeo.com')) { 
        $videoid = explode('vimeo.com/', $jplayer->jplayerfile);
        $jplayer->jplayerfile = $videoid[1];
    }
    $content = '<iframe id="videoPlayer" src="https://player.vimeo.com/video/'.$jplayer->jplayerfile.'?api=1&player_id=videoPlayer" width="'.$jplayer->width.'" height="'.$jplayer->height.'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
    $player = html_writer::tag('div', $content, array('id' => 'videoElement'));
    
    return $player;
}