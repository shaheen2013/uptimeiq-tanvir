<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

use Altum\Date;
use Altum\Title;

class MonitorLogs extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors')) {
            redirect('monitors');
        }
        $monitor->details = json_decode($monitor->details);
        $monitor->settings = json_decode($monitor->settings);

        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_ok', 'ping_server_id'], ['response_status_code'], ['response_time', 'datetime']));
        $filters->set_default_order_by('monitor_log_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `monitors_logs` WHERE `monitor_id` = {$monitor->monitor_id} AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}') {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('monitor-logs/' . $monitor->monitor_id . '?' . $filters->get_get() . '&start_date=' . $start_date . '&end_date=' . $end_date . '&page=%d')));

        /* Get the required logs */
        $monitor_logs = [];
        $monitor_logs_result = database()->query("
            SELECT
                `ping_server_id`,
                `is_ok`,
                `response_time`,
                `response_status_code`,
                `error`,
                `datetime`
            FROM
                 `monitors_logs`
            WHERE
                `monitor_id` = {$monitor->monitor_id}
                AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");

        /* Get monitor logs to calculate data and display charts */
        while($monitor_log = $monitor_logs_result->fetch_object()) {
            $monitor_logs[] = $monitor_log;
        }

        /* Export handler */
        process_export_csv($monitor_logs, 'include', ['is_ok', 'response_time', 'response_status_code', 'datetime'], sprintf(l('monitor_logs.title'), $monitor->name));
        process_export_json($monitor_logs, 'include', ['is_ok', 'response_time', 'response_status_code', 'datetime'], sprintf(l('monitor_logs.title'), $monitor->name));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Set a custom title */
        Title::set(sprintf(l('monitor_logs.title'), $monitor->name));

        /* Prepare the View */
        $data = [
            'monitor' => $monitor,
            'monitor_logs' => $monitor_logs,
            'date' => $date,
            'ping_servers' => $ping_servers,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('monitor-logs/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }
}
