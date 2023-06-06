<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

return [
    'facebook'=> [
        'format' => 'https://facebook.com/%s',
        'input_display_format' => true,
        'name' => 'Facebook',
        'icon' => 'fab fa-facebook',
        'max_length' => 64
    ],
    'instagram'=> [
        'format' => 'https://instagram.com/%s',
        'input_display_format' => true,
        'name' => 'Instagram',
        'icon' => 'fab fa-instagram',
        'max_length' => 64
    ],
    'twitter'=> [
        'format' => 'https://twitter.com/%s',
        'input_display_format' => true,
        'name' => 'Twitter',
        'icon' => 'fab fa-twitter',
        'max_length' => 64
    ],
    'email'=> [
        'format' => 'mailto:%s',
        'input_display_format' => false,
        'name' => 'Email',
        'icon' => 'fa fa-envelope',
        'max_length' => 320
    ],
    'website'=> [
        'format' => '%s',
        'input_display_format' => false,
        'name' => 'Website',
        'icon' => 'fa fa-globe',
        'max_length' => 2048
    ],
];
