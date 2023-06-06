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

class Heartbeat extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats')) {
            redirect('heartbeats');
        }
        $heartbeat->settings = json_decode($heartbeat->settings);

        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

        /* Get the required statistics */
        $heartbeat_logs = [];
        $heartbeat_logs_chart = [];

        $heartbeat_logs_result = database()->query("
            SELECT
                `is_ok`,
                `datetime`
            FROM
                `heartbeats_logs`
            WHERE
                `heartbeat_id` = {$heartbeat->heartbeat_id}
                AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
        ");

        $total_runs = 0;
        $total_missed_runs = 0;

        /* Get heartbeat logs to calculate data and display charts */
        while($heartbeat_log = $heartbeat_logs_result->fetch_object()) {

            $heartbeat_logs[] = $heartbeat_log;

            $label = $start_date == $end_date ? \Altum\Date::get($heartbeat_log->datetime, 3) : \Altum\Date::get($heartbeat_log->datetime, 1);

            $heartbeat_logs_chart[$label] = [
                'is_ok' => $heartbeat_log->is_ok,
            ];

            $total_runs = $heartbeat_log->is_ok ? $total_runs + 1 : $total_runs;
            $total_missed_runs = !$heartbeat_log->is_ok ? $total_missed_runs + 1 : $total_missed_runs;
        }

        /* Export handler */
        process_export_csv($heartbeat_logs, 'include', ['is_ok', 'datetime'], sprintf(l('heartbeat.title'), $heartbeat->name));
        process_export_json($heartbeat_logs, 'include', ['is_ok', 'datetime'], sprintf(l('heartbeat.title'), $heartbeat->name));

        $heartbeat_logs_chart = get_chart_data($heartbeat_logs_chart);

        /* Get the available incidents */
        $heartbeat_incidents = [];

        $heartbeat_incidents_result = database()->query("
            SELECT
                `start_datetime`,
                `end_datetime`
            FROM
                 `incidents`
            WHERE
                `heartbeat_id` = {$heartbeat->heartbeat_id}
                AND `start_datetime` >= '{$date->start_date_query}' 
                AND (`end_datetime` <= '{$date->end_date_query}' OR `end_datetime` IS NULL)
        ");

        while($row = $heartbeat_incidents_result->fetch_object()) {
            $heartbeat_incidents[] = $row;
        }

        /* calculate some data */
        $total_heartbeat_logs = count($heartbeat_logs);
        $uptime = $total_runs > 0 ? $total_runs / ($total_runs + $total_missed_runs) * 100 : 0;
        $downtime = 100 - $uptime;

        /* Set a custom title */
        Title::set(sprintf(l('heartbeat.title'), $heartbeat->name));

	$projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Prepare the View */
        $data = [
            'projects' => $projects,
            'heartbeat' => $heartbeat,
            'heartbeat_logs_chart' => $heartbeat_logs_chart,
            'heartbeat_logs' => $heartbeat_logs,
            'total_heartbeat_logs' => $total_heartbeat_logs,
            'heartbeat_logs_data' => [
                'uptime' => $uptime,
                'downtime' => $downtime,
                'total_runs' => $total_runs,
                'total_missed_runs' => $total_missed_runs
            ],
            'date' => $date,
            'heartbeat_incidents' => $heartbeat_incidents,
        ];

        $view = new \Altum\View('heartbeat/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.heartbeats')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('heartbeats');
        }

        if(empty($_POST)) {
            redirect('heartbeats');
        }

        $heartbeat_id = (int) query_clean($_POST['heartbeat_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('heartbeats');
        }

        /* Make sure the heartbeat id is created by the logged in user */
        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->where('user_id', $this->user->user_id)->getOne('heartbeats', ['heartbeat_id', 'name'])) {
            redirect('heartbeats');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the heartbeat */
            db()->where('heartbeat_id', $heartbeat->heartbeat_id)->delete('heartbeats');

            /* Clear cache */
            \Altum\Cache::$adapter->deleteItemsByTag('heartbeat_id=' . $heartbeat->heartbeat_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $heartbeat->name . '</strong>'));

            redirect('heartbeats');

        }

        redirect('heartbeats');
    }
}
