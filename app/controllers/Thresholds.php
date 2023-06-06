<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;


class Thresholds extends Controller {

    public function index() {
        
        \Altum\Authentication::guard();

        $main_user=\Altum\Teams::get_main_user();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name'], ['datetime', 'last_datetime', 'last_run_datetime', 'name', 'uptime']));
        $filters->set_default_order_by('threshold_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `thresholds` WHERE `team_id` = {$main_user->team_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('thresholds?' . $filters->get_get() . '&page=%d')));
        
        /* get tags */  
        $all_tags=db()->where('user_id', $main_user->user_id)->where('threshold_id', 'null',"!=")->get('tags', null, ['name']);
        $tags=[];
        foreach ($all_tags as $key => $val) {
                array_push($tags,$val->name);
        }

        /* Get the thresholds */
        $thresholds = [];
        $thresholds_result = database()->query("
            SELECT
                *
            FROM
                `thresholds`
            WHERE
                `team_id` = {$main_user->team_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $thresholds_result->fetch_object()) {
            $thresholds[] = $row;
        }

        /* Export handler */
        process_export_csv($thresholds, 'include', ['threshold_id', 'project_id', 'name', 'code', 'is_ok', 'uptime', 'downtime', 'total_runs', 'total_missed_runs', 'last_run_datetime', 'next_run_datetime', 'is_enabled', 'datetime'], sprintf(l('thresholds.title')));
        process_export_json($thresholds, 'include', ['threshold_id', 'project_id', 'name', 'code', 'is_ok', 'uptime', 'downtime', 'total_runs', 'total_missed_runs', 'last_run_datetime', 'next_run_datetime', 'notifications', 'is_enabled', 'datetime'], sprintf(l('thresholds.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($main_user->user_id);

        /* Prepare the View */
        $data = [
            'projects' => $projects,
            'thresholds' => $thresholds,
            'total_thresholds' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'tags' => array_unique($tags),
        ];

        $view = new \Altum\View('thresholds/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
