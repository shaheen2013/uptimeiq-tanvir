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

class SubAdminTeamInvitation extends Controller
{
    public function index() {
        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('teams')) {
            redirect('dashboard');
        }
        $main_user_id=\Altum\Teams::get_main_user()->user_id;
        $main_user_email=\Altum\Teams::get_main_user()->email;

        // prepare joind team and invited team data
        /* Prepare the filtering system */
        $team_filters = (new \Altum\Filters([], ['name'], ['datetime']));
        $team_filters->set_default_order_by('team_member_id', settings()->main->default_order_type);
        $team_filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $team_total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `teams_members` WHERE (`user_id` = {$main_user_id} OR `user_email` = '{$main_user_email}') {$team_filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $team_paginator = (new \Altum\Paginator($team_total_rows, $team_filters->get_results_per_page(), $_GET['page'] ?? 1, url('teams-member?' . $team_filters->get_get() . 'page=%d')));

        /* Get the teams list for the user */
        $teams_member = [];
        $teams_member_result = database()->query("
            SELECT `teams`.`name`, `teams_members`.*
            FROM `teams_members` 
            LEFT JOIN `teams` ON `teams`.`team_id` = `teams_members`.`team_id` 
            WHERE 
                  (`teams_members`.`user_id` = {$main_user_id} AND `teams_members`.`status` = 0 
                  OR `teams_members`.`user_email` = '{$main_user_email}' AND `teams_members`.`status` = 0 )
                  {$team_filters->get_sql_where('teams_members')} 
            {$team_filters->get_sql_order_by('teams_members')} 
            {$team_paginator->get_sql_limit()}
        ");
        while ($row = $teams_member_result->fetch_object()) {
            $row->access = json_decode($row->access);
            $teams_member[] = $row;
        }

        /* Export handler */
        process_export_json($teams_member, 'include', ['team_member_id', 'team_id', 'user_id', 'name', 'status', 'access', 'datetime', 'last_datetime']);
        process_export_csv($teams_member, 'include', ['team_member_id', 'team_id', 'user_id', 'name', 'status', 'datetime', 'last_datetime']);

        /* Prepare the pagination view */
        $team_pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $team_paginator]);

        /* Prepare the View */
        $data['teams_member']=$teams_member;
        $data['team_filters']=$team_filters;
        $data['team_pagination']=$team_pagination;
        $data['teams_access'] = require APP_PATH . 'includes/teams_access.php';

        $view = new \Altum\View('sub-admin/team-invitation/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }
}