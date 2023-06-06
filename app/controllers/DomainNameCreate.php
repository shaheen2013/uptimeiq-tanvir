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

class DomainNameCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.domain_names')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('domain-names');
        }

        $main_user=\Altum\Teams::get_main_user();

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE `user_id` = {$main_user->user_id}")->fetch_object()->total ?? 0;

        if($main_user->plan_settings->domain_names_limit != -1 && $total_rows >= $main_user->plan_settings->domain_names_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('domain-names');
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($main_user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($main_user->user_id);

        $domain_name_timings = require APP_PATH . 'includes/domain_name_timings.php';

        if(!empty($_POST)) {
            $_POST['name'] = query_clean($_POST['name']);
            $_POST['target'] = query_clean($_POST['target']);
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['whois_notifications'] = array_map(
                function($notification_handler_id) {
                    return (int) $notification_handler_id;
                },
                array_filter($_POST['whois_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['whois_notifications_timing'] = array_key_exists($_POST['whois_notifications_timing'], $domain_name_timings) ? $_POST['whois_notifications_timing'] : array_key_first($domain_name_timings);
            $whois_notifications = json_encode([
                'whois_notifications' => $_POST['whois_notifications'],
                'whois_notifications_timing' => $_POST['whois_notifications_timing'],
            ]);
            $_POST['ssl_notifications'] = array_map(
                function($notification_handler_id) {
                    return (int) $notification_handler_id;
                },
                array_filter($_POST['ssl_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['ssl_notifications_timing'] = array_key_exists($_POST['ssl_notifications_timing'], $domain_name_timings) ? $_POST['ssl_notifications_timing'] : array_key_first($domain_name_timings);
            $ssl_notifications = json_encode([
                'ssl_notifications' => $_POST['ssl_notifications'],
                'ssl_notifications_timing' => $_POST['ssl_notifications_timing'],
            ]);

            //ALTUMCODE:DEMO if(DEMO) if($main_user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'target'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }


            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Prepare the statement and execute query */
                $domain_name_id = db()->insert('domain_names', [
                    'project_id' => $_POST['project_id'],
                    'user_id' => $main_user->user_id,
                    'name' => $_POST['name'],
                    'target' => $_POST['target'],
                    'whois_notifications' => $whois_notifications,
                    'ssl_notifications' => $ssl_notifications,
                    'next_check_datetime' => \Altum\Date::$date,
                    'datetime' => \Altum\Date::$date,
                    'team_id' => $main_user->team_id,
                ]);

                /* Set a nice success message */
                Alerts::add_success(l('domain_name_create.success_message'));

                redirect('domain-names');
            }

        }

        /* Set default values */
        $values = [
            'name' => $_POST['name'] ?? '',
            'target' => $_POST['target'] ?? '',
            'project_id' => $_POST['project_id'] ?? '',
            'ssl_notifications' => $_POST['ssl_notifications'] ?? [],
            'ssl_notifications_timing' => 3,
            'whois_notifications' => $_POST['whois_notifications'] ?? [],
            'whois_notifications_timing' => 3,
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'] ?? 0,
        ];

        /* Prepare the View */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'values' => $values,
            'domain_name_timings' => $domain_name_timings,
        ];

        $view = new \Altum\View('domain-name-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
