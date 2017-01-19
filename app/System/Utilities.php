<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 12/7/16
 * Time: 11:18 PM
 */

namespace App\System;


class Utilities {

    private static $instance;

    /**
     * Singleton class.
     * @return Utilities
     */
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check to see if the URL is a valid URL.
     * @param $url
     * @return bool
     */
    public function validURL($url) {
        //Regular Expression to get all urls with https:// or http:// with www. afterwards.
        $regex = '/(?:https|http):\/\/\w+(?:\.\w{3}|\.\w+\.\w+)/';
        if (!preg_match($regex, $url)) { //Check to see if the Regular expression matches.
            return false;
        }
        //Valid URL.
        return true;

    }

    /**
     * Removes the .MP3 extension.
     * @param $fileName
     * @return mixed
     */
    public function removeExtension($fileName) {
        //TODO: convert this into a regular expression as this method only removes .mp3 but if the fileName is .png it will not remove it.
        return str_replace('.mp3', '', $fileName); //Remove the .mp3 from the fileName.
    }

}