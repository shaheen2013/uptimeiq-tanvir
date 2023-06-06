<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum;

class Settings {
    public static $settings = null;

    public static function initialize($settings) {

        self::$settings = $settings;

    }

    public static function get() {
        return self::$settings;
    }
}
