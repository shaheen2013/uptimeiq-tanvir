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
use Altum\Date;
use Altum\Title;

class Monitor extends Controller {

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

        /* Get the required statistics */
        $monitor_logs = [];
        $monitor_logs_chart = [];

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
        ");

        $total_ok_checks = 0;
        $total_not_ok_checks = 0;
        $total_response_time = 0;
        $ping_servers_checks = [];

        /* Get monitor logs to calculate data and display charts */
        while($monitor_log = $monitor_logs_result->fetch_object()) {

            /* Process for the ping server average */
            if(isset($ping_servers[$monitor_log->ping_server_id])) {
                $ping_server = $ping_servers[$monitor_log->ping_server_id];

                if(!isset($ping_servers_checks[$ping_server->ping_server_id])) {
                    $ping_servers_checks[$ping_server->ping_server_id] = [
                        'total_response_time' => $monitor_log->response_time,
                        'total_ok_checks' => $monitor_log->is_ok ? 1 : 0,
                        'total_not_ok_checks' => !$monitor_log->is_ok ? 1: 0,
                        'lowest_response_time' => $monitor_log->response_time,
                        'highest_response_time' => $monitor_log->response_time,
                    ];
                } else {
                    if($monitor_log->is_ok) $ping_servers_checks[$ping_server->ping_server_id]['total_ok_checks']++;
                    if(!$monitor_log->is_ok) $ping_servers_checks[$ping_server->ping_server_id]['total_not_ok_checks']++;
                    $ping_servers_checks[$ping_server->ping_server_id]['total_response_time'] += $monitor_log->response_time;

                    if($monitor_log->response_time < $ping_servers_checks[$ping_server->ping_server_id]['lowest_response_time']) {
                        $ping_servers_checks[$ping_server->ping_server_id]['lowest_response_time'] = $monitor_log->response_time;
                    }

                    if($monitor_log->response_time > $ping_servers_checks[$ping_server->ping_server_id]['highest_response_time']) {
                        $ping_servers_checks[$ping_server->ping_server_id]['highest_response_time'] = $monitor_log->response_time;
                    }

                }
            }

            /* Save the location as well */
            //$monitor_log->ping_server_country_code = isset($ping_servers[$monitor_log->ping_server_id]) ? $ping_servers[$monitor_log->ping_server_id]->country_code : null;

            $monitor_logs[] = $monitor_log;

            $label = $start_date == $end_date ? \Altum\Date::get($monitor_log->datetime, 3) : \Altum\Date::get($monitor_log->datetime, 1);

            $monitor_logs_chart[$label] = [
                'is_ok' => $monitor_log->is_ok,
                'response_time' => $monitor_log->response_time,
            ];

            $total_ok_checks = $monitor_log->is_ok ? $total_ok_checks + 1 : $total_ok_checks;
            $total_not_ok_checks = !$monitor_log->is_ok ? $total_not_ok_checks + 1 : $total_not_ok_checks;
            $total_response_time += $monitor_log->is_ok ? $monitor_log->response_time : 0;
        }

        /* Export handler */
        process_export_csv($monitor_logs, 'include', ['is_ok', 'response_time', 'response_status_code', 'datetime'], sprintf(l('monitor.title'), $monitor->name));
        process_export_json($monitor_logs, 'include', ['is_ok', 'response_time', 'response_status_code', 'datetime'], sprintf(l('monitor.title'), $monitor->name));

        $monitor_logs_chart = get_chart_data($monitor_logs_chart);

        /* Get the available incidents */
        $monitor_incidents = [];

        $monitor_incidents_result = database()->query("
            SELECT
                `start_datetime`,
                `end_datetime`
            FROM
                 `incidents`
            WHERE
                `monitor_id` = {$monitor->monitor_id}
                AND `start_datetime` >= '{$date->start_date_query}' 
                AND (`end_datetime` <= '{$date->end_date_query}' OR `end_datetime` IS NULL)
        ");

        while($row = $monitor_incidents_result->fetch_object()) {
            $monitor_incidents[] = $row;
        }

        /* calculate some data */
        $total_monitor_logs = count($monitor_logs);
        $uptime = $total_ok_checks > 0 ? $total_ok_checks / ($total_ok_checks + $total_not_ok_checks) * 100 : 0;
        $downtime = 100 - $uptime;
        $average_response_time = $total_ok_checks > 0 ? $total_response_time / $total_ok_checks : 0;

        /* Set a custom title */
        Title::set(sprintf(l('monitor.title'), $monitor->name));

        /* Prepare the View */
        $data = [
            'monitor' => $monitor,
            'monitor_logs_chart' => $monitor_logs_chart,
            'monitor_logs' => $monitor_logs,
            'total_monitor_logs' => $total_monitor_logs,
            'monitor_logs_data' => [
                'uptime' => $uptime,
                'downtime' => $downtime,
                'average_response_time' => $average_response_time,
                'total_ok_checks' => $total_ok_checks,
                'total_not_ok_checks' => $total_not_ok_checks
            ],
            'date' => $date,
            'monitor_incidents' => $monitor_incidents,
            'ping_servers_checks' => $ping_servers_checks,
            'ping_servers' => $ping_servers,
        ];

        $view = new \Altum\View('monitor/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.monitors')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('monitors');
        }

        if(empty($_POST)) {
            redirect('monitors');
        }

        $monitor_id = (int) query_clean($_POST['monitor_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('monitors');
        }

        /* Make sure the monitor id is created by the logged in user */
        if(!$monitor = db()->where('monitor_id', $monitor_id)->where('user_id', $this->user->user_id)->getOne('monitors', ['monitor_id', 'name'])) {
            redirect('monitors');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the monitor */
            db()->where('monitor_id', $monitor->monitor_id)->delete('monitors');

            /* Clear cache */
            \Altum\Cache::$adapter->deleteItemsByTag('monitor_id=' . $monitor->monitor_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $monitor->name . '</strong>'));

            redirect('monitors');

        }

        redirect('monitors');
    }
}
