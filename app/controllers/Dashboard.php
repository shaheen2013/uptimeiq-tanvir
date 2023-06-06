<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;


class Dashboard extends Controller
{

    public function index()
    {

        \Altum\Authentication::guard();

        $main_user = \Altum\Teams::get_main_user();

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($main_user->user_id);

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($main_user, false);

        /* Get the monitors */
        $monitors = [];
        $monitors_result = database()->query("
            SELECT
                *
            FROM
                `monitors`
            WHERE
                `team_id` = {$main_user->team_id}
            LIMIT
                5
        ");
        while ($row = $monitors_result->fetch_object()) {
            $monitors[] = $row;
        }
        $monitors_total = database()->query("SELECT COUNT(*) AS `total` FROM `monitors` WHERE `team_id` = {$main_user->team_id}")->fetch_object()->total ?? 0;
        $monitors_down = database()->query("SELECT COUNT(*) AS `total` FROM `monitors` WHERE `is_ok` = 0 AND `team_id` = {$main_user->team_id}")->fetch_object()->total ?? 0;

        /* Get the heartbeats */
        $heartbeats = [];
        $heartbeats_result = database()->query("
            SELECT
                *
            FROM
                `heartbeats`
            WHERE
            `team_id` = {$main_user->team_id}
            LIMIT
                5
        ");
        while ($row = $heartbeats_result->fetch_object()) {
            $heartbeats[] = $row;
        }
        $heartbeats_total = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE `team_id` = {$main_user->team_id}")->fetch_object()->total ?? 0;
        $heartbeats_down = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE `is_ok` = 0 AND `team_id` = {$main_user->team_id}")->fetch_object()->total ?? 0;

        /* Get the thresholds */
        $thresholds = [];
        $thresholds_result = database()->query("
            SELECT
                *
            FROM
                `thresholds`
            WHERE
            `team_id` = {$main_user->team_id}
            LIMIT
                5
        ");
        while ($row = $thresholds_result->fetch_object()) {
            $thresholds[] = $row;
        }
        $thresholds_total = database()->query("SELECT COUNT(*) AS `total` FROM `thresholds` WHERE `team_id` = {$main_user->team_id}")->fetch_object()->total ?? 0;
        $thresholds_down = database()->query("SELECT COUNT(*) AS `total` FROM `thresholds` WHERE `is_ok` = 0 AND `team_id` = {$main_user->team_id}")->fetch_object()->total ?? 0;

        /* Get the status_pages */
        $status_pages = [];
        $status_pages_result = database()->query("
            SELECT
                *
            FROM
                `status_pages`
            WHERE
            `team_id` = {$main_user->team_id}
            LIMIT
                5
        ");
        while ($row = $status_pages_result->fetch_object()) {

            /* Genereate the status page full URL base */
            $row->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($row, $main_user, $domains);

            $status_pages[] = $row;
        }
        $status_pages_total = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE `team_id` = {$main_user->team_id}")->fetch_object()->total ?? 0;

        $unattached_ebs = database()->query("
            SELECT
                `thresholds_logs`.`threshold_value` AS `total`
            FROM
                `thresholds_logs`
            INNER JOIN
                `thresholds`
            ON
                `thresholds_logs`.`threshold_id` = `thresholds`.`threshold_id`
            WHERE
                `thresholds`.`team_id` = {$main_user->team_id}
            AND
                `thresholds`.`name` = 'UnattachedEBSVolumes'
            ORDER BY
                `threshold_log_id` DESC
            LIMIT
                1
        ")->fetch_object()->total ?? 0;

        $daily_cost = database()->query("
            SELECT
                `thresholds_logs`.`threshold_value` AS `total`
            FROM
                `thresholds_logs`
            INNER JOIN
                `thresholds`
            ON
                `thresholds_logs`.`threshold_id` = `thresholds`.`threshold_id`
            WHERE
                `thresholds`.`team_id` = {$main_user->team_id}
            AND
                `thresholds`.`name` = 'DailyCost'
            ORDER BY
                `threshold_log_id` DESC
            LIMIT
                1
        ")->fetch_object()->total ?? 0;

        $monthly_forecast = database()->query("
            SELECT
                `thresholds_logs`.`threshold_value` AS `total`
            FROM
                `thresholds_logs`
            INNER JOIN
                `thresholds`
            ON
                `thresholds_logs`.`threshold_id` = `thresholds`.`threshold_id`
            WHERE
                `thresholds`.`team_id` = {$main_user->team_id}
            AND
                `thresholds`.`name` = 'ForecastMonthlyCost'
            ORDER BY
                `threshold_log_id` DESC
            LIMIT
                1
        ")->fetch_object()->total ?? 0;

        $running_ec2 = database()->query("
            SELECT
                `thresholds_logs`.`threshold_value` AS `total`
            FROM
                `thresholds_logs`
            INNER JOIN
                `thresholds`
            ON
                `thresholds_logs`.`threshold_id` = `thresholds`.`threshold_id`
            WHERE
                `thresholds`.`team_id` = {$main_user->team_id}
            AND
                `thresholds`.`name` = 'RunningEC2'
            ORDER BY
                `threshold_log_id` DESC
            LIMIT
                1
        ")->fetch_object()->total ?? 0;

        $all_ec2 = database()->query("
            SELECT
                `thresholds_logs`.`threshold_value` AS `total`
            FROM
                `thresholds_logs`
            INNER JOIN
                `thresholds`
            ON
                `thresholds_logs`.`threshold_id` = `thresholds`.`threshold_id`
            WHERE
                `thresholds`.`team_id` = {$main_user->team_id}
            AND
                `thresholds`.`name` = 'AllEC2'
            ORDER BY
                `threshold_log_id` DESC
            LIMIT
                1
        ")->fetch_object()->total ?? 0;

        $all_rds = database()->query("
            SELECT
                `thresholds_logs`.`threshold_value` AS `total`
            FROM
                `thresholds_logs`
            INNER JOIN
                `thresholds`
            ON
                `thresholds_logs`.`threshold_id` = `thresholds`.`threshold_id`
            WHERE
                `thresholds`.`team_id` = {$main_user->team_id}
            AND
                `thresholds`.`name` = 'AllRDS'
            ORDER BY
                `threshold_log_id` DESC
            LIMIT
                1
        ")->fetch_object()->total ?? 0;

        /* Prepare the View */
        $data = [
            'monitors' => $monitors,
            'monitors_total' => $monitors_total,
            'monitors_down' => $monitors_down,
            'heartbeats' => $heartbeats,
            'heartbeats_total' => $heartbeats_total,
            'heartbeats_down' => $heartbeats_down,
            'thresholds' => $thresholds,
            'thresholds_total' => $thresholds_total,
            'thresholds_down' => $thresholds_down,
            'status_pages' => $status_pages,
            'status_pages_total' => $status_pages_total,
            'unattached_ebs' => $unattached_ebs,
            'daily_cost' => $daily_cost,
            'monthly_forecast' => $monthly_forecast,
            'running_ec2' => $running_ec2,
            'all_ec2' => $all_ec2,
            'all_rds' => $all_rds,
            'projects' => $projects
        ];

        $view = new \Altum\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }
}
