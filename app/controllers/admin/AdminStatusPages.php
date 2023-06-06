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

class AdminStatusPages extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'user_id', 'domain_id', 'project_id'], ['name'], ['last_datetime', 'datetime', 'name', 'pageviews']));
        $filters->set_default_order_by('status_page_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/status-pages?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $status_pages = [];
        $status_pages_result = database()->query("
            SELECT
                `status_pages`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`
            FROM
                `status_pages`
            LEFT JOIN
                `users` ON `status_pages`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('status_pages')}
                {$filters->get_sql_order_by('status_pages')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $status_pages_result->fetch_object()) {
            $status_pages[] = $row;
        }

        /* Export handler */
        process_export_csv($status_pages, 'include', ['status_page_id', 'user_id', 'domain_id', 'project_id', 'url', 'name', 'description', 'pageviews', 'is_se_visible', 'is_removed_branding', 'is_enabled', 'datetime'], sprintf(l('status_pages.title')));
        process_export_json($status_pages, 'include', ['status_page_id', 'user_id', 'domain_id', 'project_id', 'url', 'name', 'description', 'pageviews', 'is_se_visible', 'is_removed_branding', 'is_enabled', 'datetime'], sprintf(l('status_pages.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'status_pages' => $status_pages,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/status-pages/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/status-pages');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/status-pages');
        }

        if(!isset($_POST['type']) || (isset($_POST['type']) && !in_array($_POST['type'], ['delete']))) {
            redirect('admin/status-pages');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $status_page_id) {

                        /* Delete the domain_name */
                        db()->where('status_page_id', $status_page_id)->delete('status_pages');

                        /* Clear the cache */
                        \Altum\Cache::$adapter->deleteItemsByTag('status_page_id=' . $status_page_id);

                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('admin_bulk_delete_modal.success_message'));

        }

        redirect('admin/status-pages');
    }

    public function delete() {

        $status_page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$status_page = db()->where('status_page_id', $status_page_id)->getOne('status_pages', ['status_page_id', 'name'])) {
            redirect('admin/status-pages');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\StatusPage())->delete($status_page->status_page_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $status_page->name . '</strong>'));

        }

        redirect('admin/status-pages');
    }

}
