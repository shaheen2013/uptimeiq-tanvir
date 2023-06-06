<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

function get_website_certificate($url) {
    try {
        $domain = parse_url($url, PHP_URL_HOST);

        $get = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => TRUE,
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $read = @stream_socket_client('ssl://' . $domain . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);

        if(!$read || $errstr) return false;

        $cert = stream_context_get_params($read);

        $certInfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        return empty($certInfo) ? false : $certInfo;

    } catch (\Exception $exception) {
        return false;
    }
}

function display_response_time($number) {
    if($number > 1000) {
        return nr($number / 1000, 2) . ' ' . l('global.date.short_seconds');
    } else {
        return nr($number, 3) . ' ' . l('global.date.short_milliseconds');
    }
}
