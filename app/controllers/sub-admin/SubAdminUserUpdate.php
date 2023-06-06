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
use Altum\Models\Plan;

class SubAdminUserUpdate extends Controller {

    public function index() {

        if (\Altum\Teams::get_main_user()->type != 2) {
            redirect('dashboard');
        }

        $user_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Check if user exists */
        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('admin/users');
        }

        $user_data = db()->where('user_id', $user_id)->getOne('users');
        if($user_data->type == 1) {
            Alerts::add_error('You can not Edit site-admin!');
            redirect('sub-admin/users');
        }

        $user->plan_settings = json_decode($user->plan_settings);

        $monitor_check_intervals = require APP_PATH . 'includes/monitor_check_intervals.php';
        $additional_domains = db()->where('is_enabled', 1)->where('type', 1)->get('domains');

        if(!empty($_POST)) {
            /* Filter some the variables */
            $_POST['name'] = input_clean($_POST['name']);
            $_POST['status'] = (int) $_POST['status'];
            $_POST['type'] = (int) $_POST['type'];
            $_POST['plan_trial_done'] = (int) $_POST['plan_trial_done'];

            if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
                $_POST['referred_by'] = !empty($_POST['referred_by']) ? (int) $_POST['referred_by'] : null;
            }

            switch($_POST['plan_id']) {
                case 'free':

                    $plan_settings = json_encode(settings()->plan_free->settings);

                    break;

                case 'custom':

                    $plan_settings = [
                        'monitors_limit'                    => (int) $_POST['monitors_limit'],
                        'monitors_check_intervals'          => $_POST['monitors_check_intervals'],
                        'heartbeats_limit'                  => (int) $_POST['heartbeats_limit'],
                        'thresholds_limit'                  => (int) $_POST['thresholds_limit'],
                        'domain_names_limit'                => (int) $_POST['domain_names_limit'],
                        'status_pages_limit'                => (int) $_POST['status_pages_limit'],
                        'projects_limit'                    => (int) $_POST['projects_limit'],
                        'domains_limit'                     => (int) $_POST['domains_limit'],
                        'teams_limit'                       => (int) $_POST['teams_limit'],
                        'team_members_limit'                => (int) $_POST['team_members_limit'],
                        'logs_retention'                    => (int) $_POST['logs_retention'],
                        'statistics_retention'              => (int) $_POST['statistics_retention'],

                        'additional_domains'                => $_POST['additional_domains'] ?? [],
                        'analytics_is_enabled'              => (bool) isset($_POST['analytics_is_enabled']),
                        'qr_is_enabled'                     => (bool) isset($_POST['qr_is_enabled']),
                        'removable_branding_is_enabled'     => (bool) isset($_POST['removable_branding_is_enabled']),
                        'custom_url_is_enabled'             => (bool) isset($_POST['custom_url_is_enabled']),
                        'password_protection_is_enabled'    => (bool) isset($_POST['password_protection_is_enabled']),
                        'search_engine_block_is_enabled'    => (bool) isset($_POST['search_engine_block_is_enabled']),
                        'custom_css_is_enabled'             => (bool) isset($_POST['custom_css_is_enabled']),
                        'custom_js_is_enabled'              => (bool) isset($_POST['custom_js_is_enabled']),
                        'email_reports_is_enabled'          => (bool) isset($_POST['email_reports_is_enabled']),
                        'api_is_enabled'                    => (bool) isset($_POST['api_is_enabled']),
                        'affiliate_commission_percentage'   => (int) $_POST['affiliate_commission_percentage'],
                        'no_ads'                            => (bool) isset($_POST['no_ads'])
                    ];

                    foreach(require APP_PATH . 'includes/notification_handlers.php' as $notification_handler) {
                        $plan_settings['notification_handlers_' . $notification_handler . '_limit'] = (int) $_POST['notification_handlers_' . $notification_handler . '_limit'];
                    }

                    $plan_settings = json_encode($plan_settings);

                    break;

                default:

                    $_POST['plan_id'] = (int) $_POST['plan_id'];

                    /* Make sure this plan exists */
                    if(!$plan_settings = db()->where('plan_id', $_POST['plan_id'])->getValue('plans', 'settings')) {
                        redirect('admin/user-update/' . $user->user_id);
                    }

                    break;
            }

            $_POST['plan_expiration_date'] = (new \DateTime($_POST['plan_expiration_date']))->format('Y-m-d H:i:s');

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            /* Check for any errors */
            $required_fields = ['name', 'email'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }
            if(mb_strlen($_POST['name']) < 1 || mb_strlen($_POST['name']) > 64) {
                Alerts::add_field_error('name', l('admin_users.error_message.name_length'));
            }
            if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false) {
                //ALTUMCODE:DEMO if(DEMO) {
                Alerts::add_field_error('email', l('global.error_message.invalid_email'));
                //ALTUMCODE:DEMO }
            }
            if(db()->where('email', $_POST['email'])->has('users') && $_POST['email'] !== $user->email) {
                Alerts::add_field_error('email', l('admin_users.error_message.email_exists'));
            }
            if(!in_array($_POST['type'], [0,2,3])) {
                Alerts::add_field_error('type', 'Choose given option!');
            }

            if(!empty($_POST['new_password']) && !empty($_POST['repeat_password'])) {
                if(mb_strlen($_POST['new_password']) < 6 || mb_strlen($_POST['new_password']) > 64) {
                    Alerts::add_field_error('new_password', l('global.error_message.password_length'));
                }
                if($_POST['new_password'] !== $_POST['repeat_password']) {
                    Alerts::add_field_error('repeat_password', l('global.error_message.passwords_not_matching'));
                }
            }

            /* If there are no errors, continue */
            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Update the basic user settings */
                db()->where('user_id', $user->user_id)->update('users', [
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'status' => $_POST['status'],
                    'type' => $_POST['type'],
                    'plan_id' => $_POST['plan_id'],
                    'plan_expiration_date' => $_POST['plan_expiration_date'],
                    'plan_expiry_reminder' => $user->plan_expiration_date != $_POST['plan_expiration_date'] ? 0 : 1,
                    'plan_settings' => $plan_settings,
                    'plan_trial_done' => $_POST['plan_trial_done'],
                    'referred_by' => $user->referred_by != $_POST['referred_by'] ? $_POST['referred_by'] : $user->referred_by,
                ]);

                /* Update the password if set */
                if(!empty($_POST['new_password']) && !empty($_POST['repeat_password'])) {
                    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

                    /* Database query */
                    db()->where('user_id', $user->user_id)->update('users', ['password' => $new_password]);
                }

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItemsByTag('user_id=' . $user->user_id);

                redirect('sub-admin/user-update/' . $user->user_id);
            }

        }

        /* Get all the plans available */
        $plans = db()->where('status', 0, '<>')->get('plans');

        /* Main View */
        $data = [
            'user' => $user,
            'plans' => $plans,
            'additional_domains' => $additional_domains,
            'monitor_check_intervals' => $monitor_check_intervals,
        ];

        $view = new \Altum\View('sub-admin/user-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
