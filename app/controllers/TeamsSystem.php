<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;


class TeamsSystem extends Controller {

    public function index() {

        \Altum\Authentication::guard();
        redirect('teams');
        if(!\Altum\Plugin::is_active('teams')) {
            redirect('dashboard');
        }

        /* Prepare the View */
        $data = [];

        $view = new \Altum\View('teams-system/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
