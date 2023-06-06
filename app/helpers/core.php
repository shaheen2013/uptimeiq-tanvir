<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

function settings() {
    return \Altum\Settings::$settings;
}

function db() {
    return \Altum\Database::$db;
}

function database() {
    return \Altum\Database::$database;
}

function language($language = null) {
    return \Altum\Language::get($language);
}

function l($key, $language = null, $null_coalesce = false) {
    return \Altum\Language::get($language)[$key] ?? \Altum\Language::get(\Altum\Language::$main_name)[$key] ?? ($null_coalesce ? null : 'missing_translation: ' . $key);
}
