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

class ApiThresholds extends Controller {
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

        // dd(settings());

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], [], []));
        $filters->set_default_order_by('threshold_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `thresholds` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/thresholds?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `thresholds`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->threshold_id,
                'project_id' => (int) $row->project_id,
                'name' => $row->name,
                'code' => $row->code,
                'settings' => json_decode($row->settings),
                'is_ok' => (int) $row->is_ok,
                'uptime' => (float) $row->uptime,
                'downtime' => (float) $row->downtime,
                'total_runs' => (int) $row->total_runs,
                'total_missed_runs' => (int) $row->total_missed_runs,
                'last_run_datetime' => $row->last_run_datetime,
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

        $threshold_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $threshold = db()->where('threshold_id', $threshold_id)->where('user_id', $this->api_user->user_id)->getOne('thresholds');

        /* We haven't found the resource */
        if(!$threshold) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $threshold->threshold_id,
            'project_id' => (int) $threshold->project_id,
            'name' => $threshold->name,
            'code' => $threshold->code,
            'settings' => json_decode($threshold->settings),
            'is_ok' => (int) $threshold->is_ok,
            'uptime' => (float) $threshold->uptime,
            'downtime' => (float) $threshold->downtime,
            'total_runs' => (int) $threshold->total_runs,
            'total_missed_runs' => (int) $threshold->total_missed_runs,
            'last_run_datetime' => $threshold->last_run_datetime,
            'notifications' => json_decode($threshold->notifications),
            'is_enabled' => (bool) $threshold->is_enabled,
            'datetime' => $threshold->datetime
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('thresholds', 'count(`threshold_id`)');

        if($this->api_user->plan_settings->thresholds_limit != -1 && $total_rows >= $this->api_user->plan_settings->thresholds_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->api_user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->api_user->user_id);

        /* Check for any errors */
        $required_fields = ['name'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['name'] = query_clean($_POST['name']);
        $_POST['run_interval'] = (int) ($_POST['run_interval'] ?? 1);
        $_POST['run_interval_type'] = isset($_POST['run_interval_type']) && in_array($_POST['run_interval_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_type'] : 'hours';
        $_POST['run_interval_grace'] = (int) ($_POST['run_interval_grace'] ?? 5);
        $_POST['run_interval_grace_type'] = isset($_POST['run_interval_grace_type']) && in_array($_POST['run_interval_grace_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_grace_type'] : 'minutes';
        $_POST['threshold_breach_value'] = (int) ($_POST['threshold_breach_value'] ?? 5); 
        $_POST['threshold_breach_value_type'] = isset($_POST['threshold_breach_value_type']) && in_array($_POST['threshold_breach_value_type'], ['above', 'below']) ? $_POST['threshold_breach_value_type'] : 'above';
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
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

        /* Prepare */
        $code = md5(time() . $_POST['name'] . $this->api_user->user_id . microtime());
        $next_run_datetime = (new \DateTime())->modify('+5 years')->format('Y-m-d H:i:s');
        $settings = json_encode([
            'run_interval' => $_POST['run_interval'],
            'run_interval_type' => $_POST['run_interval_type'],
            'run_interval_grace' => $_POST['run_interval_grace'],
            'run_interval_grace_type' => $_POST['run_interval_grace_type'],
	    'threshold_breach_value' => $_POST['threshold_breach_value'],
	    'threshold_breach_value_type' => $_POST['threshold_breach_value_type'],
        ]);

        $notifications = json_encode([
            'is_ok' => $_POST['is_ok_notifications'],
        ]);

        /* Prepare the statement and execute query */
        $threshold_id = db()->insert('thresholds', [
            'user_id' => $this->api_user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'code' => $code,
            'settings' => $settings,
            'notifications' => $notifications,
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
            'email_reports_last_datetime' => \Altum\Date::$date,
            'next_run_datetime' => $next_run_datetime,
            'is_enabled' => $_POST['is_enabled'],
            'datetime' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItem('thresholds?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $threshold_id
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $threshold_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $threshold = db()->where('threshold_id', $threshold_id)->where('user_id', $this->api_user->user_id)->getOne('thresholds');

        /* We haven't found the resource */
        if(!$threshold) {
            $this->return_404();
        }
        $threshold->notifications = json_decode($threshold->notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->api_user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->api_user->user_id);

        $_POST['name'] = query_clean($_POST['name'] ?? $threshold->name);
        $_POST['run_interval'] = (int) ($_POST['run_interval'] ?? $threshold->run_interval);
        $_POST['run_interval_type'] = isset($_POST['run_interval_type']) && in_array($_POST['run_interval_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_type'] : $threshold->run_interval_type;
        $_POST['run_interval_grace'] = (int) ($_POST['run_interval_grace'] ?? $threshold->run_interval_grace);
        $_POST['run_interval_grace_type'] = isset($_POST['run_interval_grace_type']) && in_array($_POST['run_interval_grace_type'], ['seconds', 'minutes', 'hours', 'days']) ? $_POST['run_interval_grace_type'] : $threshold->run_interval_grace_type;
        $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : $threshold->project_id;
        $_POST['is_ok_notifications'] = array_map(
            function($notification_handler_id) {
                return (int) $notification_handler_id;
            },
            array_filter($_POST['is_ok_notifications'] ?? $threshold->notifications->is_ok, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        $_POST['email_reports_is_enabled'] = (int) (bool) ($_POST['email_reports_is_enabled'] ?? $threshold->email_reports_is_enabled);
        $_POST['is_enabled'] = (int) (bool) ($_POST['is_enabled'] ?? $threshold->is_enabled);

        /* Prepare */
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
        db()->where('threshold_id', $threshold->threshold_id)->update('thresholds', [
            'user_id' => $this->api_user->user_id,
            'project_id' => $_POST['project_id'],
            'name' => $_POST['name'],
            'settings' => $settings,
            'notifications' => $notifications,
            'email_reports_is_enabled' => $_POST['email_reports_is_enabled'],
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => \Altum\Date::$date,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItem('thresholds?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $threshold->threshold_id
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $threshold_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $threshold = db()->where('threshold_id', $threshold_id)->where('user_id', $this->api_user->user_id)->getOne('thresholds');

        /* We haven't found the resource */
        if(!$threshold) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('threshold_id', $threshold_id)->delete('thresholds');

        /* Clear cache */
        \Altum\Cache::$adapter->deleteItemsByTag('threshold_id=' . $threshold->threshold_id);

        http_response_code(200);
        die();

    }

}
