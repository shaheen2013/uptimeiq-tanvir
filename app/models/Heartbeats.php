<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Models;

class Heartbeats extends Model {

    public function get_heartbeat_by_code($code) {

        /* Get the heartbeat */
        $heartbeat = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('heartbeat?code=' . $code);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $heartbeat = database()->query("SELECT * FROM `heartbeats` WHERE `code` = '{$code}'")->fetch_object() ?? null;

            if($heartbeat) {
                \Altum\Cache::$adapter->save(
                    $cache_instance->set($heartbeat)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('heartbeat_id=' . $heartbeat->heartbeat_id)
                );
            }

        } else {

            /* Get cache */
            $heartbeat = $cache_instance->get();

        }

        return $heartbeat;

    }


}
