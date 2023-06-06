<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Models;

class PingServers extends Model {

    public function get_ping_servers() {

        /* Get all available ping servers */
        $ping_servers = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('ping_servers');

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $ping_servers_result = database()->query("SELECT * FROM `ping_servers` WHERE `is_enabled` = 1");
            while($row = $ping_servers_result->fetch_object()) $ping_servers[$row->ping_server_id] = $row;

            \Altum\Cache::$adapter->save(
                $cache_instance->set($ping_servers)->expiresAfter(86400 * 30)
            );

        } else {

            /* Get cache */
            $ping_servers = $cache_instance->get();

        }

        return $ping_servers;

    }

}
