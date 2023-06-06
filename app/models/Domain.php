<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Models;

class Domain extends Model {

    public function get_available_domains_by_user($user, $check_status_page_id_is_null = true, $show_status_page_id_domain = null) {

        /* Get the domains */
        $domains = [];

        /* Try to check if the domain posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('domains?user_id=' . $user->user_id . '&check_status_page_id_is_null=' . $check_status_page_id_is_null . '&show_status_page_id_domain=' . $show_status_page_id_domain);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Where */
            if(settings()->status_pages->additional_domains_is_enabled) {
                $where = "(user_id = {$user->user_id} OR `type` = 1)";
            } else {
                $where = "user_id = {$user->user_id}";
            }

            $where .= " AND `is_enabled` = 1";

            if($check_status_page_id_is_null) {
                if($show_status_page_id_domain) {
                    $where .= " AND (`status_page_id` IS NULL OR `status_page_id` = '{$show_status_page_id_domain}')";
                } else {
                    $where .= " AND `status_page_id` IS NULL";
                }
            }

            /* Get data from the database */
            $domains_result = database()->query("
                SELECT 
                    *
                FROM 
                    `domains` 
                WHERE 
                    {$where}
            ");
            while($row = $domains_result->fetch_object()) {
                if($row->type == 1 && !in_array($row->domain_id, $user->plan_settings->additional_domains ?? [])) continue;

                /* Build the url */
                $row->url = $row->scheme . $row->host . '/';

                $domains[$row->domain_id] = $row;
            }

            /* Properly tag the cache */
            $cache_instance->set($domains)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('domains?user_id=' . $user->user_id);

            foreach($domains as $domain) {
                $cache_instance->addTag('domain_id=' . $domain->domain_id);
            }

            \Altum\Cache::$adapter->save($cache_instance);

        } else {

            /* Get cache */
            $domains = $cache_instance->get();

        }

        return $domains;

    }

    public function get_available_additional_domains() {

        /* Get the domains */
        $domains = [];

        /* Get data from the database */
        $domains_result = database()->query("SELECT * FROM `domains` WHERE `is_enabled` = 1 AND `type` = 1");
        while($row = $domains_result->fetch_object()) {

            /* Build the url */
            $row->url = $row->scheme . $row->host . '/';

            $domains[$row->domain_id] = $row;
        }

        return $domains;

    }

    public function get_domain_by_host($host) {

        /* Get the domain */
        $domain = null;

        /* Try to check if the domain posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('domain?host=' . md5($host));

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $domain = db()->where('host', $host)->getOne('domains');

            if($domain) {
                /* Build the url */
                $domain->url = $domain->scheme . $domain->host . '/';

                \Altum\Cache::$adapter->save(
                    $cache_instance->set($domain)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('domain_id=' . $domain->domain_id)->addTag('domains?user_id=' . $domain->user_id)
                );
            }

        } else {

            /* Get cache */
            $domain = $cache_instance->get();

        }

        return $domain;

    }

    public function delete($domain_id) {

        /* Delete everything related to the status pages that the user owns */
        $result = database()->query("SELECT `status_page_id` FROM `status_pages` WHERE `domain_id` = {$domain_id}");

        while($status_page = $result->fetch_object()) {

            (new \Altum\Models\StatusPage())->delete($status_page->status_page_id);

        }

        /* Delete the domain */
        db()->where('domain_id', $domain_id)->delete('domains');

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('domain_id=' . $domain_id);

    }

}
