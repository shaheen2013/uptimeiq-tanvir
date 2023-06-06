<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;


class DomainNames extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $main_user=\Altum\Teams::get_main_user();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name', 'target'], ['last_datetime', 'datetime', 'name', 'target']));
        $filters->set_default_order_by('domain_name_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE `team_id` = {$main_user->team_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('domain-names?' . $filters->get_get() . '&page=%d')));

        /* Get the domain names */
        $domain_names = [];
        $domain_names_result = database()->query("
            SELECT
                *
            FROM
                `domain_names`
            WHERE
                `team_id` = {$main_user->team_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $domain_names_result->fetch_object()) {
            $row->whois = json_decode($row->whois);
            $row->ssl = json_decode($row->ssl);
            $domain_names[] = $row;
        }

        /* Export handler */
        process_export_csv($domain_names, 'include', ['domain_name_id', 'project_id', 'name', 'target', 'total_checks', 'last_check_datetime', 'next_check_datetime', 'is_enabled', 'datetime'], sprintf(l('domain_names.title')));
        process_export_json($domain_names, 'include', ['domain_name_id', 'project_id', 'name', 'target', 'total_checks', 'last_check_datetime', 'next_check_datetime', 'is_enabled', 'datetime'], sprintf(l('domain_names.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($main_user->user_id);

        /* Prepare the View */
        $data = [
            'projects' => $projects,
            'domain_names' => $domain_names,
            'total_domain_names' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('domain-names/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
