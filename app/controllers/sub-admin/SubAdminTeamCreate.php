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

class SubAdminTeamCreate extends Controller
{

    public function index()
    { 
        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('teams')) {
            redirect('dashboard');
        }
        $main_user_id=\Altum\Teams::get_main_user()->user_id;
        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `teams` WHERE `user_id` = {$main_user_id}")->fetch_object()->total ?? 0;

        if(\Altum\Teams::get_main_user()->plan_settings->teams_limit != -1 && $total_rows >= \Altum\Teams::get_main_user()->plan_settings->teams_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('teams');
        }

        if(!empty($_POST)) {
            $_POST['name'] = trim(input_clean($_POST['name']));

            //ALTUMCODE:DEMO if(DEMO) if(\Altum\Teams::get_main_user()->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Prepare the statement and execute query */
                $team_id = db()->insert('teams', [
                    'user_id' => \Altum\Teams::get_main_user()->user_id,
                    'name' => $_POST['name'],
                    'datetime' => \Altum\Date::$date,
                ]);

                // team member create
                $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => true, "create.notification_handlers" => true, "create.status_pages" => true, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => true, "update.notification_handlers" => true, "update.status_pages" => true, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => true, "delete.notification_handlers" => true, "delete.status_pages" => true, "delete.projects" => true];
                $team_member_data = [
                    'team_id' => $team_id,
                    'user_email' => \Altum\Teams::get_main_user()->email,
                    'access' => json_encode($access),
                    'datetime' => \Altum\Date::$date,
                    'user_id' => \Altum\Teams::get_main_user()->user_id,
                    'default_member' => 0,
                    'status'=>1
                ];
                $team_member_id = db()->insert('teams_members', $team_member_data);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('sub-admin/teams');
            }
        }

        /* Set default values */
        $values = [
            'name' => $_POST['name'] ?? '',
        ];

        /* Prepare the View */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('sub-admin/team-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }
}
