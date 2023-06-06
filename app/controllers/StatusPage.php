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

class StatusPage extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        redirect('status-pages');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.status_pages')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('status-pages');
        }

        if(empty($_POST)) {
            redirect('status-pages');
        }

        $status_page_id = (int) query_clean($_POST['status_page_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('status-pages');
        }

        /* Make sure the status_page id is created by the logged in user */
        if(!$status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages', ['status_page_id', 'name'])) {
            redirect('status-pages');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\StatusPage())->delete($status_page->status_page_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $status_page->name . '</strong>'));

            redirect('status-pages');

        }

        redirect('status-pages');
    }
}
