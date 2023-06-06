<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

use Altum\Meta;

class Index extends Controller {

    public function index() {

        /* Custom index redirect if set */
        if(!empty(settings()->main->index_url)) {
            header('Location: ' . settings()->main->index_url); die();
        }

        /* Get some stats */
        $total_monitors = database()->query("SELECT MAX(`monitor_id`) AS `total` FROM `monitors`")->fetch_object()->total ?? 0;
        $total_status_pages = database()->query("SELECT MAX(`status_page_id`) AS `total` FROM `status_pages`")->fetch_object()->total ?? 0;
        $total_monitors_logs = database()->query("SELECT MAX(`monitor_log_id`) AS `total` FROM `monitors_logs`")->fetch_object()->total ?? 0;

        /* Plans View */
        $view = new \Altum\View('partials/plans', (array) $this);
        $this->add_view_content('plans', $view->run());

        /* Opengraph image */
        if(settings()->main->opengraph) {
            Meta::set_social_url(SITE_URL);
            Meta::set_social_description(l('index.meta_description'));
            Meta::set_social_image(UPLOADS_FULL_URL . 'main/' . settings()->main->opengraph);
        }

        /* Main View */
        $data = [
            'total_monitors' => $total_monitors,
            'total_status_pages' => $total_status_pages,
            'total_monitors_logs' => $total_monitors_logs,
        ];

        $view = new \Altum\View('index/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
