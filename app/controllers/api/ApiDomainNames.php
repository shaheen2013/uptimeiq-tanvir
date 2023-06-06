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

class ApiDomainNames extends Controller {
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
        $filters->set_default_order_by('domain_name_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/domain-names?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `domain_names`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->domain_name_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'target' => $row->target,
                'whois' => json_decode($row->whois),
                'whois_notifications' => json_decode($row->whois_notifications),
                'ssl' => json_decode($row->ssl),
                'ssl_notifications' => json_decode($row->ssl_notifications),
                'total_checks' => (int) $row->total_checks,
                'last_check_datetime' => $row->last_check_datetime,
                'next_check_datetime' => $row->next_check_datetime,
                'is_enabled' => (bool) $row->is_enabled,
                'last_datetime' => $row->last_datetime,
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

        $domain_name_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->api_user->user_id)->getOne('domain_names');

        /* We haven't found the resource */
        if(!$domain_name) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $domain_name->domain_name_id,
            'project_id' => (int) $domain_name->project_id,
            'name' => $domain_name->name,
            'target' => $domain_name->target,
            'whois' => json_decode($domain_name->whois),
            'whois_notifications' => json_decode($domain_name->whois_notifications),
            'ssl' => json_decode($domain_name->ssl),
            'ssl_notifications' => json_decode($domain_name->ssl_notifications),
            'total_checks' => (int) $domain_name->total_checks,
            'last_check_datetime' => $domain_name->last_check_datetime,
            'next_check_datetime' => $domain_name->next_check_datetime,
            'is_enabled' => (bool) $domain_name->is_enabled,
            'last_datetime' => $domain_name->last_datetime,
            'datetime' => $domain_name->datetime
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('domain_names', 'count(`domain_name_id`)');

        if($this->api_user->plan_settings->domain_names_limit != -1 && $total_rows >= $this->api_user->plan_settings->domain_names_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->api_user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->api_user->user_id);

        $domain_name_timings = require APP_PATH . 'includes/domain_name_timings.php';

        /* Check for any errors */
        $required_fields = ['name', 'target'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

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
        $_POST['whois_notifications_timing'] = array_key_exists($_POST['whois_notifications_timing'] ?? null, $domain_name_timings) ? $_POST['whois_notifications_timing'] : array_key_first($domain_name_timings);
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
        $_POST['ssl_notifications_timing'] = array_key_exists($_POST['ssl_notifications_timing'] ?? null, $domain_name_timings) ? $_POST['ssl_notifications_timing'] : array_key_first($domain_name_timings);
        $ssl_notifications = json_encode([
            'ssl_notifications' => $_POST['ssl_notifications'],
            'ssl_notifications_timing' => $_POST['ssl_notifications_timing'],
        ]);
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? 1);

        /* Prepare the statement and execute query */
        $domain_name_id = db()->insert('domain_names', [
            'user_id' => $this->api_user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'target' => $_POST['target'],
            'whois_notifications' => $whois_notifications,
            'ssl_notifications' => $ssl_notifications,
            'next_check_datetime' => \Altum\Date::$date,
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItem('domain_names?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $domain_name_id
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $domain_name_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->api_user->user_id)->getOne('domain_names');

        /* We haven't found the resource */
        if(!$domain_name) {
            $this->return_404();
        }
        $domain_name->whois_notifications = json_decode($domain_name->whois_notifications);
        $domain_name->ssl_notifications = json_decode($domain_name->ssl_notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->api_user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->api_user->user_id);

        $domain_name_timings = require APP_PATH . 'includes/domain_name_timings.php';

        $_POST['name'] = query_clean($_POST['name'] ?? $domain_name->name);
        $_POST['target'] = query_clean($_POST['target']?? $domain_name->target);
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : $domain_name->project_id;
        $_POST['whois_notifications'] = array_map(
            function($notification_handler_id) {
                return (int) $notification_handler_id;
            },
            array_filter($_POST['whois_notifications'] ?? $domain_name->whois_notifications->whois_notifications, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['whois_notifications_timing'] = array_key_exists($_POST['whois_notifications_timing'] ?? $domain_name->whois_notifications->whois_notifications_timing, $domain_name_timings) ? $_POST['whois_notifications_timing'] ?? $domain_name->whois_notifications->whois_notifications_timing : array_key_first($domain_name_timings);
        $whois_notifications = json_encode([
            'whois_notifications' => $_POST['whois_notifications'],
            'whois_notifications_timing' => $_POST['whois_notifications_timing'],
        ]);
        $_POST['ssl_notifications'] = array_map(
            function($notification_handler_id) {
                return (int) $notification_handler_id;
            },
            array_filter($_POST['ssl_notifications'] ?? $domain_name->ssl_notifications->ssl_notifications, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['ssl_notifications_timing'] = array_key_exists($_POST['ssl_notifications_timing'] ?? $domain_name->ssl_notifications->ssl_notifications_timing, $domain_name_timings) ? $_POST['ssl_notifications_timing'] ?? $domain_name->ssl_notifications->ssl_notifications_timing : array_key_first($domain_name_timings);
        $ssl_notifications = json_encode([
            'ssl_notifications' => $_POST['ssl_notifications'],
            'ssl_notifications_timing' => $_POST['ssl_notifications_timing'],
        ]);
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? $domain_name->is_enabled);

        /* Prepare the statement and execute query */
        db()->where('domain_name_id', $domain_name->domain_name_id)->update('domain_names', [
            'user_id' => $this->api_user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'target' => $_POST['target'],
            'whois_notifications' => $whois_notifications,
            'ssl_notifications' => $ssl_notifications,
            'next_check_datetime' => \Altum\Date::$date,
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItem('domain_names?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $domain_name->domain_name_id
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $domain_name_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->api_user->user_id)->getOne('domain_names');

        /* We haven't found the resource */
        if(!$domain_name) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('domain_name_id', $domain_name_id)->delete('domain_names');

        /* Clear cache */
        \Altum\Cache::$adapter->deleteItemsByTag('domain_name_id=' . $domain_name->domain_name_id);

        http_response_code(200);
        die();

    }

}
