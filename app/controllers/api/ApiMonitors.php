<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

use Altum\Response;
use Altum\Traits\Apiable;
use MaxMind\Db\Reader;

class ApiMonitors extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {
                    $this->patch();
                } else {
                    $this->post();
                }

                break;

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], [], []));
        $filters->set_default_order_by('monitor_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `monitors` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/monitors?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `monitors`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->monitor_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'type' => $row->type,
                'target' => $row->target,
                'port' => (int) $row->port,
                'settings' => json_decode($row->settings),
                'ping_servers_ids' => json_decode($row->ping_servers_ids),
                'is_ok' => (int) $row->is_ok,
                'uptime' => (float) $row->uptime,
                'downtime' => (float) $row->downtime,
                'average_response_time' => (float) $row->average_response_time,
                'total_checks' => (int) $row->total_checks,
                'total_ok_checks' => (int) $row->total_ok_checks,
                'total_not_ok_checks' => (int) $row->total_not_ok_checks,
                'last_check_datetime' => $row->last_check_datetime,
                'notifications' => json_decode($row->notifications),
                'is_enabled' => (bool) $row->is_enabled,
                'datetime' => $row->datetime
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $_GET['page'] ?? 1,
            'total_pages' => $paginator->getNumPages(),
            'results_per_page' => $filters->get_results_per_page(),
            'total_results' => (int) $total_rows,
        ];

        /* Prepare the pagination links */
        $others = ['links' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->api_user->user_id)->getOne('monitors');

        /* We haven't found the resource */
        if(!$monitor) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $monitor->monitor_id,
            'project_id' => (int) $monitor->project_id,
            'name' => $monitor->name,
            'type' => $monitor->type,
            'target' => $monitor->target,
            'port' => (int) $monitor->port,
            'settings' => json_decode($monitor->settings),
            'ping_servers_ids' => json_decode($monitor->ping_servers_ids),
            'is_ok' => (int) $monitor->is_ok,
            'uptime' => (float) $monitor->uptime,
            'downtime' => (float) $monitor->downtime,
            'average_response_time' => (float) $monitor->average_response_time,
            'total_checks' => (int) $monitor->total_checks,
            'total_ok_checks' => (int) $monitor->total_ok_checks,
            'total_not_ok_checks' => (int) $monitor->total_not_ok_checks,
            'last_check_datetime' => $monitor->last_check_datetime,
            'notifications' => json_decode($monitor->notifications),
            'is_enabled' => (bool) $monitor->is_enabled,
            'datetime' => $monitor->datetime
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('monitors', 'count(`monitor_id`)');

        if($this->api_user->plan_settings->monitors_limit != -1 && $total_rows >= $this->api_user->plan_settings->monitors_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->api_user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->api_user->user_id);

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Monitors vars */
        $monitor_check_intervals = require APP_PATH . 'includes/monitor_check_intervals.php';
        $monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

        /* Check for any errors */
        $required_fields = ['name', 'target'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['name'] = query_clean($_POST['name']);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['website', 'ping', 'port']) ? query_clean($_POST['type']) : 'website';
        $_POST['target'] = query_clean($_POST['target']);
        $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : 0;

        $_POST['check_interval_seconds'] = isset($_POST['check_interval_seconds']) && in_array($_POST['check_interval_seconds'], $this->api_user->plan_settings->monitors_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : array_key_last($monitor_check_intervals);
        $_POST['timeout_seconds'] = isset($_POST['timeout_seconds']) && array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : 5;

        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['ping_servers_ids'] = array_map(
            function($ping_server_id) {
                return (int) $ping_server_id;
            },
            array_filter($_POST['ping_servers_ids'] ?? [], function($ping_server_id) use($ping_servers) {
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
        $_POST['email_reports_is_enabled'] = (int) (bool) ($_POST['email_reports_is_enabled'] ?? 0);
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? 1);

        /* Request */
        $_POST['request_method'] = isset($_POST['request_method']) && in_array($_POST['request_method'], ['HEAD', 'GET', 'POST', 'PUT', 'PATCH']) ? query_clean($_POST['request_method']) : 'HEAD';
        $_POST['request_body'] = mb_substr(query_clean($_POST['request_body'] ?? null), 0, 8192);
        $_POST['request_basic_auth_username'] = mb_substr(query_clean($_POST['request_basic_auth_username'] ?? null), 0, 256);
        $_POST['request_basic_auth_password'] = mb_substr(query_clean($_POST['request_basic_auth_password'] ?? null), 0, 256);

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
        $_POST['response_status_code'] = $_POST['response_status_code'] ?? 200;
        $_POST['response_status_code'] = $_POST['response_status_code'] < 0 || $_POST['response_status_code'] > 1000 ? 0 : (int) $_POST['response_status_code'];
        $_POST['response_body'] = mb_substr(query_clean($_POST['response_body'] ?? null), 0, 8192);

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

        switch($_POST['type']) {
            case 'website':
                $ip = '';

                if(!filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
                    $this->response_error(l('monitor.error_message.invalid_target_url'), 401);
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
                $this->response_error($exception->getMessage(), 401);
            }
        }

        /* Prepare */
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
        $monitor_id = db()->insert('monitors', [
            'user_id' => $this->api_user->user_id,
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
            'email_reports_last_datetime' => \Altum\Date::$date,
            'next_check_datetime' => \Altum\Date::$date,
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItem('s_monitors?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $monitor_id
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->api_user->user_id)->getOne('monitors');

        /* We haven't found the resource */
        if(!$monitor) {
            $this->return_404();
        }
        $monitor->settings = json_decode($monitor->settings);
        $monitor->notifications = json_decode($monitor->notifications);
        $monitor->ping_servers_ids = json_decode($monitor->ping_servers_ids);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->api_user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->api_user->user_id);

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Monitors vars */
        $monitor_check_intervals = require APP_PATH . 'includes/monitor_check_intervals.php';
        $monitor_timeouts = require APP_PATH . 'includes/monitor_timeouts.php';

        $_POST['name'] = query_clean($_POST['name'] ?? $monitor->name);
        $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['website', 'ping', 'port']) ? query_clean($_POST['type']) : $monitor->type;
        $_POST['target'] = query_clean($_POST['target'] ?? $monitor->target);
        $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : $monitor->port;

        $_POST['check_interval_seconds'] = isset($_POST['check_interval_seconds']) && in_array($_POST['check_interval_seconds'], $this->api_user->plan_settings->monitors_check_intervals ?? []) ? (int) $_POST['check_interval_seconds'] : $monitor->settings->check_interval_seconds;
        $_POST['timeout_seconds'] = isset($_POST['timeout_seconds']) && array_key_exists($_POST['timeout_seconds'], $monitor_timeouts) ? (int) $_POST['timeout_seconds'] : $monitor->settings->timeout_seconds;

        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
        $_POST['ping_servers_ids'] = array_map(
            function($ping_server_id) {
                return (int) $ping_server_id;
            },
            array_filter($_POST['ping_servers_ids'] ?? $monitor->ping_servers_ids, function($ping_server_id) use($ping_servers) {
                return array_key_exists($ping_server_id, $ping_servers);
            })
        );
        $_POST['is_ok_notifications'] = array_map(
            function($notification_handler_id) {
                return (int) $notification_handler_id;
            },
            array_filter($_POST['is_ok_notifications'] ?? $monitor->notifications->is_ok, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['email_reports_is_enabled'] = (int) (bool) ($_POST['email_reports_is_enabled'] ?? $monitor->email_reports_is_enabled);
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? $monitor->is_enabled);

        /* Request */
        $_POST['request_method'] = isset($_POST['request_method']) && in_array($_POST['request_method'], ['HEAD', 'GET', 'POST', 'PUT', 'PATCH']) ? query_clean($_POST['request_method']) : $monitor->settings->request_method;
        $_POST['request_body'] = mb_substr(query_clean($_POST['request_body'] ?? $monitor->settings->request_body), 0, 8192);
        $_POST['request_basic_auth_username'] = mb_substr(query_clean($_POST['request_basic_auth_username'] ?? $monitor->settings->request_basic_auth_username), 0, 256);
        $_POST['request_basic_auth_password'] = mb_substr(query_clean($_POST['request_basic_auth_password'] ?? $monitor->settings->request_basic_auth_password), 0, 256);

        $request_headers = $monitor->settings->request_headers;

        if(isset($_POST['request_header_name'])) {
            $request_headers = [];
            foreach ($_POST['request_header_name'] as $key => $value) {
                if (empty(trim($value))) continue;

                $request_headers[] = [
                    'name' => mb_substr(query_clean($value), 0, 128),
                    'value' => mb_substr(trim(query_clean($_POST['request_header_value'][$key])), 0, 256),
                ];
            }
        }

        /* Response */
        $_POST['response_status_code'] = $_POST['response_status_code'] ?? $monitor->settings->response_status_code;
        $_POST['response_status_code'] = $_POST['response_status_code'] < 0 || $_POST['response_status_code'] > 1000 ? 0 : (int) $_POST['response_status_code'];
        $_POST['response_body'] = mb_substr(query_clean($_POST['response_body'] ?? $monitor->settings->response_body), 0, 8192);

        $response_headers = $monitor->settings->response_headers;

        if(isset($_POST['response_header_name'])) {
            $response_headers = [];
            foreach ($_POST['response_header_name'] as $key => $value) {
                if (empty(trim($value))) continue;

                $response_headers[] = [
                    'name' => mb_substr(query_clean($value), 0, 128),
                    'value' => mb_substr(query_clean($_POST['response_header_value'][$key]), 0, 256),
                ];
            }
        }

        switch($_POST['type']) {
            case 'website':
                $ip = '';

                if(!filter_var($_POST['target'], FILTER_VALIDATE_URL)) {
                    $this->response_error(l('monitor.error_message.invalid_target_url'), 401);
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
                $this->response_error($exception->getMessage(), 401);
            }
        }

        /* Prepare */
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
            'user_id' => $this->api_user->user_id,
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
        \Altum\Cache::$adapter->deleteItem('s_monitors?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $monitor->monitor_id
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->api_user->user_id)->getOne('monitors');

        /* We haven't found the resource */
        if(!$monitor) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('monitor_id', $monitor_id)->delete('monitors');

        /* Clear cache */
        \Altum\Cache::$adapter->deleteItemsByTag('monitor_id=' . $monitor->monitor_id);

        http_response_code(200);
        die();

    }

}
