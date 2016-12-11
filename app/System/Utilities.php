<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 12/7/16
 * Time: 11:18 PM
 */

namespace App\System;


class Utilities {

    /**
     * Check to see if the URL is a valid URL.
     *
     * @param $url
     * @return bool
     */
    public function validURL($url) {

        $regex = '/(?:https|http):\/\/\w+(?:\.\w{3}|\.\w+\.\w+)/';

        if (!preg_match($regex, $url)) {
            return false;
        }

        return true;

    }

}