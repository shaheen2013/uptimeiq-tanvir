<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Models;

class StatusPage extends Model {

    public function get_status_page_full_url($status_page, $user, $domains = null) {

        /* Detect the URL of the status_page */
        if($status_page->domain_id) {

            /* Get available custom domains */
            if(!$domains) {
                $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($user, false);
            }

            if(isset($domains[$status_page->domain_id])) {

                if($status_page->status_page_id == $domains[$status_page->domain_id]->status_page_id) {

                    $status_page->full_url = $domains[$status_page->domain_id]->scheme . $domains[$status_page->domain_id]->host . '/';

                } else {

                    $status_page->full_url = $domains[$status_page->domain_id]->scheme . $domains[$status_page->domain_id]->host . '/' . $status_page->url . '/';

                }

            }

        } else {

            $status_page->full_url = SITE_URL . 's/' . $status_page->url . '/';

        }

        return $status_page->full_url;
    }

    public function get_status_page_by_url($status_page_url) {

        /* Get the status_page */
        $status_page = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('s_status_page?url=' . $status_page_url);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $status_page = database()->query("SELECT * FROM `status_pages` WHERE `url` = '{$status_page_url}' AND `domain_id` IS NULL")->fetch_object() ?? null;

            if($status_page) {
                \Altum\Cache::$adapter->save(
                    $cache_instance->set($status_page)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('status_page_id=' . $status_page->status_page_id)
                );
            }

        } else {

            /* Get cache */
            $status_page = $cache_instance->get();

        }

        return $status_page;

    }

    public function get_status_page_by_url_and_domain_id($status_page_url, $domain_id) {

        /* Get the status_page */
        $status_page = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('s_status_page?url=' . $status_page_url . '&domain_id=' . $domain_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $status_page = database()->query("SELECT * FROM `status_pages` WHERE `url` = '{$status_page_url}' AND `domain_id` = {$domain_id}")->fetch_object() ?? null;

            if($status_page) {
                \Altum\Cache::$adapter->save(
                    $cache_instance->set($status_page)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('status_page_id=' . $status_page->status_page_id)
                );
            }

        } else {

            /* Get cache */
            $status_page = $cache_instance->get();

        }

        return $status_page;

    }

    public function get_status_page_by_status_page_id($status_page_id) {

        /* Get the status_page */
        $status_page = null;

        /* Try to check if the status_page posts exists via the cache */
        $cache_instance = \Altum\Cache::$adapter->getItem('s_status_page?status_page_id=' . $status_page_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $status_page = database()->query("SELECT * FROM `status_pages` WHERE `status_page_id` = '{$status_page_id}'")->fetch_object() ?? null;

            if($status_page) {
                \Altum\Cache::$adapter->save(
                    $cache_instance->set($status_page)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('status_page_id=' . $status_page->status_page_id)
                );
            }

        } else {

            /* Get cache */
            $status_page = $cache_instance->get();

        }

        return $status_page;

    }

    public function delete($status_page_id) {

        $status_page = db()->where('status_page_id', $status_page_id)->getOne('status_pages', ['status_page_id', 'logo', 'favicon']);

        if(!$status_page) return;

        \Altum\Uploads::delete_uploaded_file($status_page->logo, 'status_pages_logos');
        \Altum\Uploads::delete_uploaded_file($status_page->favicon, 'status_pages_favicons');

        /* Delete the status_page */
        db()->where('status_page_id', $status_page_id)->delete('status_pages');

        /* Clear cache */
        \Altum\Cache::$adapter->deleteItemsByTag('status_page_id=' . $status_page_id);

    }

}
