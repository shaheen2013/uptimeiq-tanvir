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

class AdminMonitors extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'type', 'user_id', 'project_id'], ['name', 'target'], ['last_datetime', 'datetime', 'name', 'uptime', 'total_checks', 'last_check_datetime', 'average_response_time']));
        $filters->set_default_order_by('monitor_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `monitors` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/monitors?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $monitors = [];
        $monitors_result = database()->query("
            SELECT
                `monitors`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`
            FROM
                `monitors`
            LEFT JOIN
                `users` ON `monitors`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('monitors')}
                {$filters->get_sql_order_by('monitors')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $monitors_result->fetch_object()) {
            $monitors[] = $row;
        }

        /* Export handler */
        process_export_csv($monitors, 'include', ['monitor_id', 'user_id', 'project_id', 'name', 'type', 'target', 'port', 'ping_servers_ids', 'is_ok', 'uptime', 'downtime', 'average_response_time', 'total_checks', 'total_ok_checks', 'total_not_ok_checks', 'last_check_datetime', 'is_enabled', 'datetime'], sprintf(l('monitors.title')));
        process_export_json($monitors, 'include', ['monitor_id', 'user_id', 'project_id', 'name', 'type', 'target', 'port', 'settings', 'ping_servers_ids', 'is_ok', 'uptime', 'downtime', 'average_response_time', 'total_checks', 'total_ok_checks', 'total_not_ok_checks', 'last_check_datetime', 'is_enabled', 'datetime'], sprintf(l('monitors.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'monitors' => $monitors,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/monitors/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/monitors');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/monitors');
        }

        if(!isset($_POST['type']) || (isset($_POST['type']) && !in_array($_POST['type'], ['delete']))) {
            redirect('admin/monitors');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $monitor_id) {

                        /* Delete the monitor */
                        db()->where('monitor_id', $monitor_id)->delete('monitors');

                        /* Clear the cache */
                        \Altum\Cache::$adapter->deleteItemsByTag('monitor_id=' . $monitor_id);

                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('admin_bulk_delete_modal.success_message'));

        }

        redirect('admin/monitors');
    }

    public function delete() {

        $monitor_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$monitor = db()->where('monitor_id', $monitor_id)->getOne('monitors', ['monitor_id', 'name'])) {
            redirect('admin/monitors');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the monitor */
            db()->where('monitor_id', $monitor_id)->delete('monitors');

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('monitor_id=' . $monitor_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $monitor->name . '</strong>'));

        }

        redirect('admin/monitors');
    }

}
