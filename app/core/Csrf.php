<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum;

class Csrf {

    /* CSRF Protection for ajax requests */
    public static function set($name = 'token', $regenerate = false) {

        $token =  md5(time() . rand());

        if(!isset($_SESSION[$name])) {
            $_SESSION[$name] = $token;
        } else {

            if($regenerate) $_SESSION[$name] = $token;

        }

    }

    public static function get($name = 'token') {

        return $_SESSION[$name] ?? false;

    }

    public static function get_url_query($name = 'token') {

        return '&token=' . self::get($name);

    }

    public static function check($name = 'token') {
        return (
            (isset($_GET[$name]) && $_GET[$name] == self::get($name)) ||
            (isset($_POST[$name]) && $_POST[$name] == self::get($name))
        );
    }

}
