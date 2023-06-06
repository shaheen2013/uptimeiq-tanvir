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

class AdminHeartbeats extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type', 'user_id', 'project_id'], ['name', 'target'], ['last_datetime', 'datetime', 'name', 'uptime', 'total_checks', 'last_check_datetime', 'average_response_time']));
        $filters->set_default_order_by('heartbeat_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/heartbeats?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $heartbeats = [];
        $heartbeats_result = database()->query("
            SELECT
                `heartbeats`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`
            FROM
                `heartbeats`
            LEFT JOIN
                `users` ON `heartbeats`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('heartbeats')}
                {$filters->get_sql_order_by('heartbeats')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $heartbeats_result->fetch_object()) {
            $heartbeats[] = $row;
        }

        /* Export handler */
        process_export_csv($heartbeats, 'include', ['heartbeat_id', 'project_id', 'name', 'code', 'is_ok', 'uptime', 'downtime', 'total_runs', 'total_missed_runs', 'last_run_datetime', 'next_run_datetime', 'is_enabled', 'datetime'], sprintf(l('heartbeats.title')));
        process_export_json($heartbeats, 'include', ['heartbeat_id', 'project_id', 'name', 'code', 'is_ok', 'uptime', 'downtime', 'total_runs', 'total_missed_runs', 'last_run_datetime', 'next_run_datetime', 'is_enabled', 'datetime'], sprintf(l('heartbeats.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'heartbeats' => $heartbeats,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/heartbeats/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/heartbeats');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/heartbeats');
        }

        if(!isset($_POST['type']) || (isset($_POST['type']) && !in_array($_POST['type'], ['delete']))) {
            redirect('admin/heartbeats');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $heartbeat_id) {

                        /* Delete the heartbeat */
                        db()->where('heartbeat_id', $heartbeat_id)->delete('heartbeats');

                        /* Clear the cache */
                        \Altum\Cache::$adapter->deleteItemsByTag('heartbeat_id=' . $heartbeat_id);

                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('admin_bulk_delete_modal.success_message'));

        }

        redirect('admin/heartbeats');
    }

    public function delete() {

        $heartbeat_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$heartbeat = db()->where('heartbeat_id', $heartbeat_id)->getOne('heartbeats', ['heartbeat_id', 'name'])) {
            redirect('admin/heartbeats');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the heartbeat */
            db()->where('heartbeat_id', $heartbeat->heartbeat_id)->delete('heartbeats');

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('heartbeat_id=' . $heartbeat_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $heartbeat->name . '</strong>'));

        }

        redirect('admin/heartbeats');
    }

}
