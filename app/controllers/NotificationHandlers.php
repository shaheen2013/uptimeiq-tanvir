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

class NotificationHandlers extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $main_user=\Altum\Teams::get_main_user();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['type'], ['name'], ['datetime', 'last_datetime', 'name']));
        $filters->set_default_order_by('notification_handler_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `notification_handlers` WHERE `team_id` = {$main_user->team_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('notification-handlers?' . $filters->get_get() . '&page=%d')));

        /* Get the notification handlers list for the user */
        $notification_handlers = [];
        $notification_handlers_result = database()->query("SELECT * FROM `notification_handlers` WHERE `team_id` = {$main_user->team_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $notification_handlers_result->fetch_object()) $notification_handlers[] = $row;

        /* Export handler */
        process_export_csv($notification_handlers, 'include', ['notification_handler_id', 'user_id', 'type', 'name', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('notification_handlers.title')));
        process_export_json($notification_handlers, 'include', ['notification_handler_id', 'user_id', 'type', 'name', 'settings', 'is_enabled', 'last_datetime', 'datetime'], sprintf(l('notification_handlers.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the View */
        $data = [
            'notification_handlers' => $notification_handlers,
            'total_notification_handlers' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('notification-handlers/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function delete() {

        \Altum\Authentication::guard();

        $main_user=\Altum\Teams::get_main_user();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.notification_handlers')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('notification-handlers');
        }

        if(empty($_POST)) {
            redirect('notification-handlers');
        }

        $notification_handler_id = (int) query_clean($_POST['notification_handler_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('notification-handlers');
        }

        if(!$notification_handler = db()->where('notification_handler_id', $notification_handler_id)->where('team_id', $main_user->team_id)->getOne('notification_handlers', ['notification_handler_id', 'name'])) {
            redirect('notification-handlers');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the resource */
            db()->where('notification_handler_id', $notification_handler_id)->delete('notification_handlers');

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItem('notification_handlers?user_id=' . $main_user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $notification_handler->name . '</strong>'));

            redirect('notification-handlers');
        }

        redirect('notification-handlers');
    }
}
