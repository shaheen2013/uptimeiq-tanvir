<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

class SubAdminIndex extends Controller
{

    public function index()
    {
        if (\Altum\Teams::get_main_user()->type != 2) {
            redirect('dashboard');
        }

        $main_user = \Altum\Teams::get_main_user();

        $monitors = db()->where("team_id", $main_user->team_id)->getValue('monitors', 'count(`monitor_id`)');
        $heartbeats = db()->where("team_id", $main_user->team_id)->getValue('heartbeats', 'count(`heartbeat_id`)');
        $status_pages = db()->where("team_id", $main_user->team_id)->getValue('status_pages', 'count(`status_page_id`)');
        $projects = db()->where("team_id", $main_user->team_id)->getValue('projects', 'count(`project_id`)');
        $domains = db()->getValue('domains', 'count(`domain_id`)');

        // get team member users
        $team_id = db()->where('user_id', $main_user->user_id)->get('teams_members', null, ['team_id']);

        $users_id = [];
        foreach ($team_id as $key => $value) {
            $team_users = db()->where('team_id', $value->team_id)->get('teams_members', null, ['user_id']);
            foreach ($team_users as $k => $val) {
                array_push($users_id, $val->user_id);
            }   
        }
        $users = count(array_unique($users_id));

        $letest_user=array_unique($users_id);

        if (in_array(settings()->license->type, ['Extended License', 'extended'])) {
            $payments = db()->getValue('payments', 'count(`id`)');
            $payments_total_amount = db()->getValue('payments', 'sum(`total_amount`)');
        } else {
            $payments = $payments_total_amount = 0;
        }

        /* Requested plan details */
        $plans = [];
        $plans_result = database()->query("SELECT `plan_id`, `name` FROM `plans`");
        while ($row = $plans_result->fetch_object()) {
            $plans[$row->plan_id] = $row;
        }

        /* Main View */
        $data = [
            'monitors' => $monitors,
            'heartbeats' => $heartbeats,
            'status_pages' => $status_pages,
            'projects' => $projects,
            'domains' => $domains,
            'users' => $users,
            'payments' => $payments,
            'payments_total_amount' => $payments_total_amount,
            'plans' => $plans,
            'letest_user' => $letest_user
        ];

        $view = new \Altum\View('sub-admin/index/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }
}
