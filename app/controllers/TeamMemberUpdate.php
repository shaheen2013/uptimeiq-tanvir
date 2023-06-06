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

class TeamMemberUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('teams')) {
            redirect('dashboard');
        }

        $team_member_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$team_member = db()->where('team_member_id', $team_member_id)->getOne('teams_members')) {
            redirect('teams');
        }
        $team_member->access = json_decode($team_member->access);

        if(!$team = db()->where('team_id', $team_member->team_id)->where('user_id', $this->user->user_id)->getOne('teams')) {
            redirect('teams');
        }

        $teams_access = require APP_PATH . 'includes/teams_access.php';

        if(!empty($_POST)) {
            /* Generate the access variable for the database */
            $access = [];

            // foreach($teams_access as $key => $value) {
            //     foreach($value as $access_key => $access_translation) {
            //         $access[$access_key] = in_array($access_key, $_POST['access'] ?? []);
            //     }
            // }

            /* Force read access */
            $access['read.all'] = true;

            if ($_POST['access']=='admin') {
                $access=["create.monitors"=>true,"create.heartbeats"=>true,"create.domain_names"=>true,"create.notification_handlers"=>true,"create.status_pages"=>true,"create.projects"=>true,"update.monitors"=>true,"update.heartbeats"=>true,"update.domain_names"=>true,"update.notification_handlers"=>true,"update.status_pages"=>true,"update.projects"=>true,"delete.monitors"=>true,"delete.heartbeats"=>true,"delete.domain_names"=>true,"delete.notification_handlers"=>true,"delete.status_pages"=>true,"delete.projects"=>true];
            }elseif ($_POST['access']=='operator') {
                $access=["create.monitors"=>true,"create.heartbeats"=>true,"create.domain_names"=>false,"create.notification_handlers"=>false,"create.status_pages"=>false,"create.projects"=>true,"update.monitors"=>true,"update.heartbeats"=>true,"update.domain_names"=>false,"update.notification_handlers"=>false,"update.status_pages"=>false,"update.projects"=>true,"delete.monitors"=>true,"delete.heartbeats"=>true,"delete.domain_names"=>false,"delete.notification_handlers"=>false,"delete.status_pages"=>false,"delete.projects"=>true];
            }elseif ($_POST['access']=='author') {
                $access=["create.monitors"=>false,"create.heartbeats"=>false,"create.domain_names"=>false,"create.notification_handlers"=>false,"create.status_pages"=>false,"create.projects"=>false,"update.monitors"=>false,"update.heartbeats"=>false,"update.domain_names"=>false,"update.notification_handlers"=>false,"update.status_pages"=>false,"update.projects"=>false,"delete.monitors"=>false,"delete.heartbeats"=>false,"delete.domain_names"=>false,"delete.notification_handlers"=>false,"delete.status_pages"=>false,"delete.projects"=>false];
            }
            
            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = [];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                db()->where('team_member_id', $team_member->team_member_id)->update('teams_members', [
                    'access' => json_encode($access),
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItem('team_member?team_id=' . $team_member->team_id . '&user_id=' . $team_member->user_id);

                /* Set a nice success message */
                Alerts::add_success(l('global.success_message.update2'));

                redirect('team-member-update/' . $team_member_id);
            }
        }

        /* Set a custom title */
        Title::set(sprintf(l('team_member_update.title'), $team->name));

        /* Prepare the View */
        $data = [
            'team' => $team,
            'team_member' => $team_member,
            'teams_access' => $teams_access
        ];

        $view = new \Altum\View('team-member-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
