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

class AdminDomainNames extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id', 'user_id',], ['name', 'target'], ['last_datetime', 'datetime', 'name', 'target']));
        $filters->set_default_order_by('domain_name_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `domain_names` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/domain-names?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $domain_names = [];
        $domain_names_result = database()->query("
            SELECT
                `domain_names`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`
            FROM
                `domain_names`
            LEFT JOIN
                `users` ON `domain_names`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('domain_names')}
                {$filters->get_sql_order_by('domain_names')}

            {$paginator->get_sql_limit()}
        ");
        while($row = $domain_names_result->fetch_object()) {
            $domain_names[] = $row;
        }

        /* Export handler */
        process_export_csv($domain_names, 'include', ['domain_name_id', 'project_id', 'name', 'target', 'total_checks', 'last_check_datetime', 'next_check_datetime', 'is_enabled', 'datetime'], sprintf(l('domain_names.title')));
        process_export_json($domain_names, 'include', ['domain_name_id', 'project_id', 'name', 'target', 'total_checks', 'last_check_datetime', 'next_check_datetime', 'is_enabled', 'datetime'], sprintf(l('domain_names.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'domain_names' => $domain_names,
            'filters' => $filters,
            'pagination' => $pagination
        ];

        $view = new \Altum\View('admin/domain-names/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/domain-names');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/domain-names');
        }

        if(!isset($_POST['type']) || (isset($_POST['type']) && !in_array($_POST['type'], ['delete']))) {
            redirect('admin/domain-names');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $domain_name_id) {

                        /* Delete the domain_name */
                        db()->where('domain_name_id', $domain_name_id)->delete('domain_names');

                        /* Clear the cache */
                        \Altum\Cache::$adapter->deleteItemsByTag('domain_name_id=' . $domain_name_id);

                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('admin_bulk_delete_modal.success_message'));

        }

        redirect('admin/domain-names');
    }

    public function delete() {

        $domain_name_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$domain_name = db()->where('domain_name_id', $domain_name_id)->getOne('domain_names', ['domain_name_id', 'name'])) {
            redirect('admin/domain-names');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the domain_name */
            db()->where('domain_name_id', $domain_name->domain_name_id)->delete('domain_names');

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('domain_name_id=' . $domain_name_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $domain_name->name . '</strong>'));

        }

        redirect('admin/domain-names');
    }

}
