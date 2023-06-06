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

class HeartbeatCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.heartbeats')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('heartbeats');
        }

        $main_user=\Altum\Teams::get_main_user();

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE `user_id` = {$main_user->user_id}")->fetch_object()->total ?? 0;

        if($main_user->plan_settings->heartbeats_limit != -1 && $total_rows >= $main_user->plan_settings->heartbeats_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('heartbeats');
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($main_user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($main_user->user_id);

        if(!empty($_POST)) {
            $_POST['name'] = query_clean($_POST['name']);
            $_POST['run_interval'] = (int) $_POST['run_interval'];
            $_POST['run_interval_type'] = in_array($_POST['run_interval_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_type'] : 'seconds';
            $_POST['run_interval_grace'] = (int) $_POST['run_interval_grace'];
            $_POST['run_interval_grace_type'] = in_array($_POST['run_interval_grace_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_grace_type'] : 'seconds';
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['is_ok_notifications'] = array_map(
                function($notification_handler_id) {
                    return (int) $notification_handler_id;
                },
                array_filter($_POST['is_ok_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['email_reports_is_enabled'] = (int) (bool) $_POST['email_reports_is_enabled'];

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

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $code = md5(time() . $_POST['name'] . $main_user->user_id . microtime());
                $next_run_datetime = (new \DateTime())->modify('+5 years')->format('Y-m-d H:i:s');
                $settings = json_encode([
                    'run_interval' => $_POST['run_interval'],
                    'run_interval_type' => $_POST['run_interval_type'],
                    'run_interval_grace' => $_POST['run_interval_grace'],
                    'run_interval_grace_type' => $_POST['run_interval_grace_type'],
                ]);

                $notifications = json_encode([
                    'is_ok' => $_POST['is_ok_notifications'],
                ]);

                /* Prepare the statement and execute query */
                $heartbeat_id = db()->insert('heartbeats', [
                    'project_id' => $_POST['project_id'],
                    'user_id' => $main_user->user_id,
                    'name' => $_POST['name'],
                    'code' => $code,
                    'settings' => $settings,
                    'notifications' => $notifications,
                    'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
                    'email_reports_last_datetime' => \Altum\Date::$date,
                    'next_run_datetime' => $next_run_datetime,
                    'datetime' => \Altum\Date::$date,
                    'team_id' => $main_user->team_id,
                ]);

                /* Set a nice success message */
                Alerts::add_success(l('heartbeat_create.success_message'));

                redirect('heartbeat/' . $heartbeat_id);
            }

        }

        /* Set default values */
        $values = [
            'name' => $_POST['name'] ?? '',
            'run_interval' => $_POST['run_interval'] ?? 1,
            'run_interval_type' => $_POST['run_interval_type'] ?? 'hours',
            'run_interval_grace' => $_POST['run_interval_grace'] ?? 5,
            'run_interval_grace_type' => $_POST['run_interval_grace_type'] ?? 'minutes',
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'] ?? 0,
            'project_id' => $_POST['project_id'] ?? '',
        ];

        /* Prepare the View */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'values' => $values
        ];

        $view = new \Altum\View('heartbeat-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
