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

class Threshold extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $threshold_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$threshold = db()->where('threshold_id', $threshold_id)->where('user_id', $this->user->user_id)->getOne('thresholds')) {
            redirect('thresholds');
        }
        $threshold->settings = json_decode($threshold->settings);

        $start_date = isset($_GET['start_date']) ? query_clean($_GET['start_date']) : Date::get('', 4);
        $end_date = isset($_GET['end_date']) ? query_clean($_GET['end_date']) : Date::get('', 4);
        $date = \Altum\Date::get_start_end_dates($start_date, $end_date);

        /* Get the required statistics */
        $threshold_logs = [];
        $threshold_logs_chart = [];

        $threshold_logs_result = database()->query("
            SELECT
                `threshold_value`,
                `is_ok`,
                `datetime`
            FROM
                `thresholds_logs`
            WHERE
                `threshold_id` = {$threshold->threshold_id}
                AND (`datetime` BETWEEN '{$date->start_date_query}' AND '{$date->end_date_query}')
        ");

        $total_runs = 0;
        $total_missed_runs = 0;

        /* Get threshold logs to calculate data and display charts */
        while($threshold_log = $threshold_logs_result->fetch_object()) {

            $threshold_logs[] = $threshold_log;

            $label = $start_date == $end_date ? \Altum\Date::get($threshold_log->datetime, 3) : \Altum\Date::get($threshold_log->datetime, 1);
            
            $threshold_logs_chart[$label] = [
                'threshold_value' => $threshold_log->threshold_value,
            ];

            $total_runs = $threshold_log->is_ok ? $total_runs + 1 : $total_runs;
            $total_missed_runs = !$threshold_log->is_ok ? $total_missed_runs + 1 : $total_missed_runs;
        }


        /* Export handler */
        process_export_csv($threshold_logs, 'include', ['threshold_value','is_ok', 'datetime'], sprintf(l('threshold.title'), $threshold->name));
        process_export_json($threshold_logs, 'include', ['threshold_value','is_ok', 'datetime'], sprintf(l('threshold.title'), $threshold->name));

        $threshold_logs_chart = get_chart_data($threshold_logs_chart);

        /* Get the available incidents */
        $threshold_incidents = [];

        $threshold_incidents_result = database()->query("
            SELECT
                `start_datetime`,
                `end_datetime`
            FROM
                 `incidents`
            WHERE
                `threshold_id` = {$threshold->threshold_id}
                AND `start_datetime` >= '{$date->start_date_query}' 
                AND (`end_datetime` <= '{$date->end_date_query}' OR `end_datetime` IS NULL)
        ");

        while($row = $threshold_incidents_result->fetch_object()) {
            $threshold_incidents[] = $row;
        }

        /* calculate some data */
        $total_threshold_logs = count($threshold_logs);
        $uptime = $total_runs > 0 ? $total_runs / ($total_runs + $total_missed_runs) * 100 : 0;
        $downtime = 100 - $uptime;

        /* Set a custom title */
        Title::set(sprintf(l('threshold.title'), $threshold->name));

        /* Prepare the View */
        $data = [
            'threshold' => $threshold,
            'threshold_logs_chart' => $threshold_logs_chart,
            'threshold_logs' => $threshold_logs,
            'total_threshold_logs' => $total_threshold_logs,
            'threshold_logs_data' => [
                'uptime' => $uptime,
                'downtime' => $downtime,
                'total_runs' => $total_runs,
                'total_missed_runs' => $total_missed_runs
            ],
            'date' => $date,
            'threshold_incidents' => $threshold_incidents,
        ];

        $view = new \Altum\View('threshold/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.thresholds')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('thresholds');
        }

        if(empty($_POST)) {
            redirect('thresholds');
        }

        $threshold_id = (int) query_clean($_POST['threshold_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('thresholds');
        }

        /* Make sure the threshold id is created by the logged in user */
        if(!$threshold = db()->where('threshold_id', $threshold_id)->where('user_id', $this->user->user_id)->getOne('thresholds', ['threshold_id', 'name'])) {
            redirect('thresholds');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the threshold */
            db()->where('threshold_id', $threshold->threshold_id)->delete('thresholds');

            /* Clear cache */
            \Altum\Cache::$adapter->deleteItemsByTag('threshold_id=' . $threshold->threshold_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $threshold->name . '</strong>'));

            redirect('thresholds');

        }

        redirect('thresholds');
    }
}
