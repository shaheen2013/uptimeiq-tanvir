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

class HeartbeatUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.heartbeats')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('heartbeats');
        }

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats')) {
            redirect('heartbeats');
        }
        $heartbeat->settings = json_decode($heartbeat->settings);
        $heartbeat->notifications = json_decode($heartbeat->notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

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
            $_POST['is_enabled'] = (int) (bool) $_POST['is_enabled'];

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

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
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
                db()->where('heartbeat_id', $heartbeat->heartbeat_id)->update('heartbeats', [
                    'project_id' => $_POST['project_id'],
                    'name' => $_POST['name'],
                    'settings' => $settings,
                    'notifications' => $notifications,
                    'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItemsByTag('heartbeat_id=' . $heartbeat_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('heartbeat-update/' . $heartbeat_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('heartbeat_update.title'), $heartbeat->name));

        /* Prepare the View */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'heartbeat' => $heartbeat
        ];

        $view = new \Altum\View('heartbeat-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
