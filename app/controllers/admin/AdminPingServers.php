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

class AdminPingServers extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'country_code'], ['name'], ['last_datetime', 'datetime', 'name']));
        $filters->set_default_order_by('ping_server_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `ping_servers` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/ping-servers?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $ping_servers = [];
        $ping_servers_result = database()->query("
            SELECT
                *
            FROM
                `ping_servers`
            WHERE
                1 = 1
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $ping_servers_result->fetch_object()) {
            $row->monitors = database()->query("SELECT COUNT(`monitor_id`) AS `total` FROM `monitors` WHERE JSON_CONTAINS(`ping_servers_ids`, '{$row->ping_server_id}', '$');")->fetch_object()->total ?? 0;
            $ping_servers[] = $row;
        }

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'ping_servers' => $ping_servers,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/ping-servers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }


    public function delete() {

        $ping_server_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if($ping_server_id == 1) {
            redirect('admin/ping-servers');
        }

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$ping_server = db()->where('ping_server_id', $ping_server_id)->getOne('ping_servers', ['ping_server_id', 'name'])) {
            redirect('admin/ping-servers');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the ping server */
            db()->where('ping_server_id', $ping_server_id)->delete('ping_servers');

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItem('ping_servers');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $ping_server->name . '</strong>'));

        }

        redirect('admin/ping-servers');
    }

}
