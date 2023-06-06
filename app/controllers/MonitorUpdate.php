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
use MaxMind\Db\Reader;

class MonitorUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.monitors')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('monitors');
        }

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors')) {
            redirect('monitors');
        }

        $monitor->settings = json_decode($monitor->settings);
        $monitor->ping_servers_ids = json_decode($monitor->ping_servers_ids);
        $monitor->notifications = json_decode($monitor->notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Monitors vars */
        $monitor_check_intervals = require APP_PATH . 'includes/monitor_check_intervals.php';
        $monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

        if(!empty($_POST)) {
            $_POST['name'] = query_clean($_POST['name']);
            $_POST['type'] = in_array($_POST['type'], ['website', 'ping', 'port']) ? query_clean($_POST['type']) : 'website';
            $_POST['target'] = query_clean($_POST['target']);
            $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : 0;
            $_POST['is_enabled'] = (int) (bool) $_POST['is_enabled'];

            $_POST['check_interval_seconds'] = in_array($_POST['check_interval_seconds'], $this->user->plan_settings->monitors_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : array_key_last($monitor_check_intervals);
            $_POST['timeout_seconds'] = array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : 3;

            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['ping_servers_ids'] = array_map(
                function($ping_server_id) {
                    return (int) $ping_server_id;
                },
                array_filter($_POST['ping_servers_ids'], function($ping_server_id) use($ping_servers) {
                    return array_key_exists($ping_server_id, $ping_servers);
                })
            );
            $_POST['is_ok_notifications'] = array_map(
                function($notification_handler_id) {
                    return (int) $notification_handler_id;
                },
                array_filter($_POST['is_ok_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );

            $_POST['email_reports_is_enabled'] = (int) (bool) $_POST['email_reports_is_enabled'];

            /* Request */
            $_POST['request_method'] = in_array($_POST['request_method'], ['HEAD', 'GET', 'POST', 'PUT', 'PATCH']) ? query_clean($_POST['request_method']) : 'HEAD';
            $_POST['request_body'] = mb_substr(query_clean($_POST['request_body']), 0, 8192);
            $_POST['request_basic_auth_username'] = mb_substr(query_clean($_POST['request_basic_auth_username']), 0, 256);
            $_POST['request_basic_auth_password'] = mb_substr(query_clean($_POST['request_basic_auth_password']), 0, 256);

            if(!isset($_POST['request_header_name'])) {
                $_POST['request_header_name'] = [];
                $_POST['request_header_value'] = [];
            }

            $request_headers = [];
            foreach($_POST['request_header_name'] as $key => $value) {
                if(empty(trim($value))) continue;


                $request_headers[] = [
                    'name' => mb_substr(query_clean($value), 0, 128),
                    'value' => mb_substr(trim(query_clean($_POST['request_header_value'][$key])), 0, 256),
                ];
            }

            /* Response */
            $_POST['response_status_code'] = $_POST['response_status_code'] < 0 || $_POST['response_status_code'] > 1000 ? 0 : (int) $_POST['response_status_code'];
            $_POST['response_body'] = mb_substr(query_clean($_POST['response_body']), 0, 8192);

            if(!isset($_POST['response_header_name'])) {
                $_POST['response_header_name'] = [];
                $_POST['response_header_value'] = [];
            }

            $response_headers = [];
            foreach($_POST['response_header_name'] as $key => $value) {
                if(empty(trim($value))) continue;

                $response_headers[] = [
                    'name' => mb_substr(query_clean($value), 0, 128),
                    'value' => mb_substr(query_clean($_POST['response_header_value'][$key]), 0, 256),
                ];
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

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

            switch($_POST['type']) {
                case 'website':
                    $ip = '';

                    if(!filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
                        Alerts::add_field_error('target', l('monitor.error_message.invalid_target_url'));
                    } else {
                        $host = parse_url($_POST['target'])['host'];
                        $ip = gethostbyname($host);
                    }
                break;

                case 'ping':
                case 'port':

                    $ip = $_POST['target'];

                    if(filter_var($_POST['target'], FILTER_VALIDATE_DOMAIN)) {
                        $ip = gethostbyname($_POST['target']);
                    }
                    break;
            }

            /* Detect the location */
            try {
                $maxmind = (new Reader(APP_PATH . 'includes/GeoLite2-City.mmdb'))->get($ip);
            } catch(\Exception $exception) {
                if(in_array($_POST['type'], ['ping', 'port'])) {
                    Alerts::add_error($exception->getMessage());
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $ping_servers_ids = json_encode($_POST['ping_servers_ids']);
                $settings = json_encode([
                    'check_interval_seconds' => $_POST['check_interval_seconds'],
                    'timeout_seconds' => $_POST['timeout_seconds'],
                    'request_method' => $_POST['request_method'],
                    'request_body' => $_POST['request_body'],
                    'request_basic_auth_username' => $_POST['request_basic_auth_username'],
                    'request_basic_auth_password' => $_POST['request_basic_auth_password'],
                    'request_headers' => $request_headers,
                    'response_status_code' => $_POST['response_status_code'],
                    'response_body' => $_POST['response_body'],
                    'response_headers' => $response_headers,
                ]);

                /* Detect the location */
                $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
                $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;
                $continent_name = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['names']['en'] : null;

                $details = json_encode([
                    'country_code' => $country_code,
                    'city_name' => $city_name,
                    'continent_name' => $continent_name
                ]);

                $notifications = json_encode([
                    'is_ok' => $_POST['is_ok_notifications'],
                ]);

                /* Prepare the statement and execute query */
                db()->where('monitor_id', $monitor->monitor_id)->update('monitors', [
                    'project_id' => $_POST['project_id'],
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'target' => $_POST['target'],
                    'port' => $_POST['port'],
                    'ping_servers_ids' => $ping_servers_ids,
                    'settings' => $settings,
                    'details' => $details,
                    'notifications' => $notifications,
                    'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItemsByTag('monitor_id=' . $monitor_id);
                \Altum\Cache::$adapter->deleteItem('s_monitors?user_id=' . $this->user->user_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('monitor-update/' . $monitor_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('monitor_update.title'), $monitor->name));

        /* Prepare the View */
        $data = [
            'ping_servers' => $ping_servers,
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'monitor_check_intervals' => $monitor_check_intervals,
            'monitor_timeouts' => $monitor_timeouts,
            'monitor' => $monitor
        ];

        $view = new \Altum\View('monitor-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
