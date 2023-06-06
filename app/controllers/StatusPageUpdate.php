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
use Altum\Title;
use Altum\Uploads;

class StatusPageUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.status_pages')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('status-pages');
        }

        $status_page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages')) {
            redirect('status-pages');
        }

        /* Genereate the status_page full URL base */
        $status_page->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($status_page, $this->user);

        $status_page->socials = json_decode($status_page->socials);
        $status_page->settings = json_decode($status_page->settings);
        $status_page->monitors_ids = json_decode($status_page->monitors_ids);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, true, $status_page->status_page_id);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get all the available monitors */
        $monitors = (new \Altum\Models\Monitors())->get_monitors_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            $_POST['url'] = !empty($_POST['url']) ? get_slug(query_clean($_POST['url'])) : false;
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

            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['timezone']  = in_array($_POST['timezone'], \DateTimeZone::listIdentifiers()) ? query_clean($_POST['timezone']) : settings()->main->default_timezone;
            $_POST['password'] = !empty($_POST['password']) ?
                ($_POST['password'] != $status_page->password ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $status_page->password)
                : null;
            $_POST['is_se_visible'] = $this->user->plan_settings->search_engine_block_is_enabled ? (int) (bool) isset($_POST['is_se_visible']) : 1;
            $_POST['is_removed_branding'] = (int) (bool) isset($_POST['is_removed_branding']);
            $_POST['title'] = query_clean($_POST['title'], 70);
            $_POST['meta_description'] = query_clean($_POST['meta_description'], 160);
            $_POST['meta_keywords'] = query_clean($_POST['meta_keywords'], 160);
            $_POST['custom_css'] = mb_substr(trim(input_clean($_POST['custom_css'])), 0, 8192);
            $_POST['custom_js'] = mb_substr(trim($_POST['custom_js']), 0, 8192);
            $fonts = require APP_PATH . 'includes/s/fonts.php';
            $_POST['font_family'] = array_key_exists($_POST['font_family'], $fonts) ? query_clean($_POST['font_family']) : false;
            $_POST['font_size'] = (int) $_POST['font_size'] < 14 || (int) $_POST['font_size'] > 22 ? 16 : (int) $_POST['font_size'];

            /* Make sure the socials sent are proper */
            $socials = require APP_PATH . 'includes/s/socials.php';

            foreach($_POST['socials'] as $key => $value) {
                if(!array_key_exists($key, $socials)) {
                    unset($_POST['socials'][$key]);
                } else {
                    $_POST['socials'][$key] = mb_substr(query_clean($_POST['socials'][$key]), 0, $socials[$key]['max_length']);
                }
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

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
            if(
                ($_POST['url'] && $this->user->plan_settings->custom_url_is_enabled && $_POST['url'] != $status_page->url)
                || ($status_page->domain_id != $_POST['domain_id'])
            ) {

                $domain_id_where = $_POST['domain_id'] ? "AND `domain_id` = {$_POST['domain_id']}" : "AND `domain_id` IS NULL";
                $is_existing_status_page = database()->query("SELECT `status_page_id` FROM `status_pages` WHERE `url` = '{$_POST['url']}' {$domain_id_where}")->num_rows;

                if($is_existing_status_page) {
                    Alerts::add_field_error('url', l('status_page.error_message.url_exists'));
                }

            }

            /* Image uploads */
            $logo = \Altum\Uploads::process_upload($status_page->logo, 'status_pages_logos', 'logo', 'logo_remove', settings()->status_pages->logo_size_limit);
            $favicon = \Altum\Uploads::process_upload($status_page->favicon, 'status_pages_favicons', 'favicon', 'favicon_remove', settings()->status_pages->favicon_size_limit);
            $opengraph = \Altum\Uploads::process_upload($status_page->opengraph, 'status_pages_opengraph', 'opengraph', 'opengraph_remove', settings()->status_pages->opengraph_size_limit);

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $theme = 'new-york';
                $monitors_ids = json_encode($_POST['monitors_ids']);
                $socials = json_encode($_POST['socials']);
                $settings = json_encode([
                    'title' => $_POST['title'],
                    'meta_description' => $_POST['meta_description'],
                    'meta_keywords' => $_POST['meta_keywords'],
                    'font_family' => $_POST['font_family'],
                    'font_size' => $_POST['font_size'],
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
                db()->where('status_page_id', $status_page->status_page_id)->update('status_pages', [
                    'domain_id' => $_POST['domain_id'],
                    'monitors_ids' => $monitors_ids,
                    'url' => $_POST['url'],
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                    'settings' => $settings,
                    'project_id' => $_POST['project_id'],
                    'timezone' => $_POST['timezone'],
                    'password' => $_POST['password'],
                    'is_se_visible' => $_POST['is_se_visible'],
                    'is_removed_branding' => $_POST['is_removed_branding'],
                    'socials' => $socials,
                    'custom_css' => $_POST['custom_css'],
                    'custom_js' => $_POST['custom_js'],
                    'theme' => $theme,
                    'logo' => $logo,
                    'favicon' => $favicon,
                    'opengraph' => $opengraph,
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Update custom domain if needed */
                if($_POST['is_main_status_page']) {

                    /* If the main status page of a particular domain is changing, update the old domain as well to "free" it */
                    if($_POST['domain_id'] != $status_page->domain_id) {
                        /* Database query */
                        db()->where('domain_id', $status_page->domain_id)->update('domains', [
                            'status_page_id' => null,
                            'last_datetime' => \Altum\Date::$date,
                        ]);
                    }

                    /* Database query */
                    db()->where('domain_id', $_POST['domain_id'])->update('domains', [
                        'status_page_id' => $status_page_id,
                        'last_datetime' => \Altum\Date::$date,
                    ]);

                    /* Clear the cache */
                    \Altum\Cache::$adapter->deleteItemsByTag('domain_id=' . $_POST['domain_id']);
                }

                /* Update old main custom domain if needed */
                if(!$_POST['is_main_status_page'] && $status_page->domain_id && $domains[$status_page->domain_id]->status_page_id == $status_page->status_page_id) {
                    /* Database query */
                    db()->where('domain_id', $status_page->domain_id)->update('domains', [
                        'status_page_id' => null,
                        'last_datetime' => \Altum\Date::$date,
                    ]);

                    /* Clear the cache */
                    \Altum\Cache::$adapter->deleteItemsByTag('domain_id=' . $_POST['domain_id']);
                }

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItemsByTag('status_page_id=' . $status_page_id);
                \Altum\Cache::$adapter->deleteItemsByTag('user_id=' . $this->user->user_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('status-page-update/' . $status_page->status_page_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('status_page_update.title'), $status_page->name));

        /* Prepare the View */
        $data = [
            'monitors' => $monitors,
            'domains' => $domains,
            'projects' => $projects,
            'status_page' => $status_page
        ];

        $view = new \Altum\View('status-page-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
