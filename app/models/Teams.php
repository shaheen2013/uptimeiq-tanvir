<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Models;

class Teams extends Model {

    public function get_team_by_team_id($team_id) {

        /* Get the team */
        $team = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('team?team_id=' . $team_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $team = db()->where('team_id', $team_id)->getOne('teams');

            if($team) {
                \Altum\Cache::$adapter->save(
                    $cache_instance->set($team)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $team->user_id)
                );
            }

        } else {

            /* Get cache */
            $team = $cache_instance->get();

        }

        return $team;

    }

}
