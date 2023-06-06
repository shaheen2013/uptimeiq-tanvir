<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

use Altum\Alerts;

class StatusPageCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.status_pages')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('status-pages');
        }

        $main_user=\Altum\Teams::get_main_user();

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE `user_id` = {$main_user->user_id}")->fetch_object()->total ?? 0;

        if($main_user->plan_settings->status_pages_limit != -1 && $total_rows >= $main_user->plan_settings->status_pages_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('status-pages');
        }

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($main_user);

        /* Get all the available monitors */
        $monitors = (new \Altum\Models\Monitors())->get_monitors_by_user_id($main_user->user_id);

        if(!empty($_POST)) {
            $_POST['url'] = !empty($_POST['url']) && $main_user->plan_settings->custom_url_is_enabled ? get_slug(query_clean($_POST['url'])) : false;
            $_POST['name'] = mb_substr(trim(query_clean($_POST['name'])), 0, 256);
            $_POST['description'] = mb_substr(trim(query_clean($_POST['description'])), 0, 256);

            $_POST['domain_id'] = isset($_POST['domain_id']) && isset($domains[$_POST['domain_id']]) ? (!empty($_POST['domain_id']) ? (int) $_POST['domain_id'] : null) : null;
            $_POST['is_main_status_page'] = (bool) isset($_POST['is_main_status_page']) && isset($domains[$_POST['domain_id']]) && $domains[$_POST['domain_id']]->type == 0;
            $_POST['monitors_ids'] = empty($_POST['monitors_ids']) ? [] : array_map(
                function($monitor_id) {
                    return (int) $monitor_id;
                },
                array_filter($_POST['monitors_ids'], function($monitor_id) use($monitors) {
                    return array_key_exists($monitor_id, $monitors);
                })
            );

            //ALTUMCODE:DEMO if(DEMO) if($main_user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!empty($_POST['url']) && in_array($_POST['url'], explode(',', settings()->status_pages->blacklisted_keywords))) {
                Alerts::add_field_error('url', l('status_page.error_message.blacklisted_keyword'));
            }

            /* Check for duplicate url if needed */
            if($_POST['url']) {

                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_status_page = database()->query("SELECT `status_page_id` FROM `status_pages` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

                if($is_existing_status_page) {
                   Alerts::add_field_error('url', l('status_page.error_message.url_exists'));
                }

            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $timezone = $main_user->timezone;
                $theme = 'new-york';
                $monitors_ids = json_encode($_POST['monitors_ids']);
                $socials = [];
                foreach(require APP_PATH . 'includes/s/socials.php' as $key => $value) {
                    $socials[$key] = '';
                }
                $socials = json_encode($socials);
                $settings = json_encode([
                    'title' => null,
                    'meta_description' => null,
                    'meta_keywords' => null,
                    'font_family' => null,
                    'font_size' => 16,
                ]);

                if(!$_POST['url']) {
                    $is_existing_status_page = true;

                    /* Generate random url if not specified */
                    while($is_existing_status_page) {
                        $_POST['url'] = mb_strtolower(string_generate(10));

                        $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                        $is_existing_status_page = database()->query("SELECT `status_page_id` FROM `status_pages` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;
                    }

                }

                /* Prepare the statement and execute query */
                $status_page_id = db()->insert('status_pages', [
                    'user_id' => $main_user->user_id,
                    'domain_id' => $_POST['domain_id'],
                    'monitors_ids' => $monitors_ids,
                    'url' => $_POST['url'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'settings' => $settings,
                    'timezone' => $timezone,
                    'socials' => $socials,
                    'theme' => $theme,
                    'datetime' => \Altum\Date::$date,
                    'team_id' => $main_user->team_id,
                ]);

                /* Update custom domain if needed */
                if($_POST['is_main_status_page']) {
                    /* Database query */
                    db()->where('domain_id', $_POST['domain_id'])->update('domains', ['status_page_id' => $status_page_id, 'last_datetime' => \Altum\Date::$date]);

                    /* Clear the cache */
                    \Altum\Cache::$adapter->deleteItemsByTag('domain_id=' . $_POST['domain_id']);
                }

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('status-page-update/' . $status_page_id);
            }

        }

        /* Set default values */
        $values = [
            'url' => $_POST['url'] ?? '',
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'domain_id' => $_POST['domain_id'] ?? '',
            'is_main_status_page' => $_POST['is_main_status_page'] ?? '',
            'monitors_ids' => $_POST['monitors_ids'] ?? []
        ];

        /* Prepare the View */
        $data = [
            'monitors' => $monitors,
            'domains' => $domains,
            'values' => $values
        ];

        $view = new \Altum\View('status-page-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
