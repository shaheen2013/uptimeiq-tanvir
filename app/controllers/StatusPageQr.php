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
use Altum\Title;

class StatusPageQr extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->user->plan_settings->qr_is_enabled) {
            Alerts::add_info(l('global.info_message.plan_feature_no_access'));
            redirect('status-pages');
        }

        $status_page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages')) {
            redirect('status-pages');
        }

        /* Genereate the status_page full URL base */
        $status_page->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($status_page, $this->user);

        /* Set a custom title */
        Title::set(sprintf(l('status_page_qr.title'), $status_page->name));

        /* Prepare the View */
        $data = [
            'status_page' => $status_page
        ];

        $view = new \Altum\View('status-page-qr/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
