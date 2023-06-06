<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Models;

class Thresholds extends Model {

    public function get_threshold_by_code($code) {

        /* Get the heartbeat */
        $threshold = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('threshold?code=' . $code);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $threshold = database()->query("SELECT * FROM `thresholds` WHERE `code` = '{$code}'")->fetch_object() ?? null;

            if($threshold) {
                \Altum\Cache::$adapter->save(
                    $cache_instance->set($threshold)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('threshold_id=' . $threshold->threshold_id)
                );
            }

        } else {

            /* Get cache */
            $threshold = $cache_instance->get();

        }

        return $threshold;

    }


}
