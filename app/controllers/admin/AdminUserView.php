<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

use Altum\Models\Plan;

class AdminUserView extends Controller {

    public function index() {

        $user_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        /* Check if user exists */
        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('admin/users');
        }

        /* Get widget stats */
        $monitors = db()->where('user_id', $user_id)->getValue('monitors', 'count(`monitor_id`)');
        $heartbeats = db()->where('user_id', $user_id)->getValue('heartbeats', 'count(`heartbeat_id`)');
        $thresholds = db()->where('user_id', $user_id)->getValue('thresholds', 'count(`threshold_id`)');
        $domain_names = db()->where('user_id', $user_id)->getValue('domain_names', 'count(`domain_name_id`)');
        $status_pages = db()->where('user_id', $user_id)->getValue('status_pages', 'count(`status_page_id`)');
        $projects = db()->where('user_id', $user_id)->getValue('projects', 'count(`project_id`)');
        $domains = db()->where('user_id', $user_id)->getValue('domains', 'count(`domain_id`)');
        $notification_handlers = db()->where('user_id', $user_id)->getValue('notification_handlers', 'count(`notification_handler_id`)');
        $payments = in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('user_id', $user_id)->getValue('payments', 'count(`id`)') : 0;

        /* Get the current plan details */
        $user->plan = (new Plan())->get_plan_by_id($user->plan_id);

        /* Check if its a custom plan */
        if($user->plan_id == 'custom') {
            $user->plan->settings = $user->plan_settings;
        }

        $user->billing = json_decode($user->billing);

        /* Main View */
        $data = [
            'user' => $user,
            'monitors' => $monitors,
            'heartbeats' => $heartbeats,
            'thresholds' => $thresholds,
            'domain_names' => $domain_names,
            'status_pages' => $status_pages,
            'projects' => $projects,
            'domains' => $domains,
            'notification_handlers' => $notification_handlers,
            'payments' => $payments,
        ];

        $view = new \Altum\View('admin/user-view/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
