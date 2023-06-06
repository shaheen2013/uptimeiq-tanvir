<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;


class StatusPages extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $main_user=\Altum\Teams::get_main_user();

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($main_user, false);

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id'], ['name'], ['last_datetime', 'datetime', 'name']));
        $filters->set_default_order_by('status_page_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE `team_id` = {$main_user->team_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('status-pages?' . $filters->get_get() . '&page=%d')));

        /* Get the status_pages */
        $status_pages = [];
        $status_pages_result = database()->query("
            SELECT
                *
            FROM
                `status_pages`
            WHERE
                `team_id` = {$main_user->team_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}

            {$paginator->get_sql_limit()}
        ");
        while($row = $status_pages_result->fetch_object()) {

            /* Genereate the status page full URL base */
            $row->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($row, $main_user, $domains);

            $status_pages[] = $row;
        }

        /* Export handler */
        process_export_csv($status_pages, 'include', ['status_page_id', 'domain_id', 'project_id', 'url', 'name', 'description', 'pageviews', 'is_se_visible', 'is_removed_branding', 'is_enabled', 'datetime'], sprintf(l('status_pages.title')));
        process_export_json($status_pages, 'include', ['status_page_id', 'domain_id', 'project_id', 'url', 'name', 'description', 'pageviews', 'is_se_visible', 'is_removed_branding', 'is_enabled', 'datetime'], sprintf(l('status_pages.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($main_user->user_id);

        /* Prepare the View */
        $data = [
            'projects' => $projects,
            'status_pages' => $status_pages,
            'total_status_pages' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('status-pages/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
