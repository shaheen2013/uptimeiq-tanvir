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
use Altum\Models\User;

class SubAdminTeamMemberInvite extends Controller
{

    public function index()
    {
        Alerts::clear_field_errors();

        if (\Altum\Teams::get_main_user()->type == 1) {
            redirect('admin/team-invite-member');
        } elseif (\Altum\Teams::get_main_user()->type == 0 || \Altum\Teams::get_main_user()->type == 3) {
            redirect('teams');
        }
        $joined_teams = db()->where('user_id', \Altum\Teams::get_main_user()->user_id)->get('teams_members', null, ['team_id']);

        $teams_id = [];
        foreach ($joined_teams as $key => $value) {
            $teams_id[$key] = $value->team_id;
        };

        $teams = [];
        foreach ($teams_id as $k => $val) {
            $team = db()->where('team_id', $val)->getOne('teams');
            $teams[$k] = $team;
        }

        $team_created = db()->where('user_id', \Altum\Teams::get_main_user()->user_id)->get('teams', null, ['team_id', 'name']);
        $teams += $team_created;

        if (!empty($_POST)) {

            $user_email = trim(filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL));



            //ALTUMCODE:DEMO if(DEMO) if(\Altum\Teams::get_main_user()->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['user_email', 'team_id', 'access'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if (!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                Alerts::add_field_error('user_email', l('global.error_message.invalid_email'));
            }

            if ($user_email == \Altum\Teams::get_main_user()->email) {
                Alerts::add_field_error('user_email', 'Type other user e-mail!');
            }

            if (db()->where('user_email', $user_email)->where('team_id', $_POST['team_id'])->has('teams_members')) {
                Alerts::add_field_error('user_email', l('team_members.error_message.email_exists'));
            }

            if (!Alerts::has_field_errors() && !Alerts::has_errors()) {
                /* Generate the access variable for the database */
                $access = [];

                if ($_POST['access'] == 'admin') {
                    $role = 2;
                    $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => true, "create.notification_handlers" => true, "create.status_pages" => true, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => true, "update.notification_handlers" => true, "update.status_pages" => true, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => true, "delete.notification_handlers" => true, "delete.status_pages" => true, "delete.projects" => true];
                } elseif ($_POST['access'] == 'operator') {
                    $role = 3;
                    $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => false, "create.notification_handlers" => false, "create.status_pages" => false, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => false, "update.notification_handlers" => false, "update.status_pages" => false, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => false, "delete.notification_handlers" => false, "delete.status_pages" => false, "delete.projects" => true];
                } elseif ($_POST['access'] == 'author') {
                    $role = 0;
                    $access = ["read.all" => true, "create.monitors" => false, "create.heartbeats" => false, "create.thresholds" => true, "create.domain_names" => false, "create.notification_handlers" => false, "create.status_pages" => false, "create.projects" => false, "update.monitors" => false, "update.heartbeats" => false, "update.thresholds" => true, "update.domain_names" => false, "update.notification_handlers" => false, "update.status_pages" => false, "update.projects" => false, "delete.monitors" => false, "delete.heartbeats" => false, "delete.thresholds" => true, "delete.domain_names" => false, "delete.notification_handlers" => false, "delete.status_pages" => false, "delete.projects" => false];
                }

                /* Prepare the statement and execute query */
                $team_member_data = [
                    'team_id' => $_POST['team_id'],
                    'user_email' => $user_email,
                    'access' => json_encode($access),
                    'datetime' => \Altum\Date::$date,
                ];

                // check if the user exists
                $user_exists = db()->where('email', $user_email)->getOne('users', ['user_id']);
                if ($user_exists) {
                    $team_member_data['user_id'] = $user_exists->user_id;
                    $team_member_data['status'] = 0;
                } else {
                    $registered_user = (new User())->create(
                        $user_email,
                        null,
                        'Invited User',
                        $role,
                        2,
                        'site_admin_create',
                        null,
                        null,
                        'free',
                        json_encode(settings()->plan_free->settings),
                        null,
                        settings()->main->default_timezone,
                        true
                    );
                    $team_member_data['user_id'] = $registered_user['user_id'];
                    $team_member_data['default_member'] = 1;
                    $team_member_data['status'] = 1;
                    // set user token
                    $user_id = $registered_user['user_id'];
                    $token = \Altum\Csrf::get();
                    db()->where('user_id', $user_id)->update('users', ['token_code' => $token]);
                }
                // insert team member
                $team_member_id = db()->insert('teams_members', $team_member_data);

                /* Prepare the email */
                $user_role = $_POST['access'];
                $email_subject = "You've been invited to a team - UptimeIQ";
                $main_user_name = \Altum\Teams::get_main_user()->name;
                $main_user_email = \Altum\Teams::get_main_user()->email;
                if ($user_exists) {
                    $sign_in_link = url("login");
                    $email_body = "<p>Hey there,</p><p>You have been invited by $main_user_name ($main_user_email) to join a team as an $user_role on UptimeIQ.</p><p>Link : <a href=" . $sign_in_link . ">Click to login.</a></p><p>Thank You.</p>";
                } else {
                    $sign_up_link = url("register?token=$token");
                    $email_body = "<p>Hey there,</p><p>You have been invited by $main_user_name ($main_user_email) to join a team as an $user_role on UptimeIQ.</p><p>Link : <a href=" . $sign_up_link . ">Click to register.</a></p><p>Greetings from UptimeIQ.</p>";
                }

                send_mail($user_email, $email_subject, $email_body);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('team_member_create.success_message'), '<strong>' . $user_email . '</strong>'));

                redirect('sub-admin/teams');
            }
        }

        /* Set default values */
        $values = [
            'user_email' => $_POST['user_email'] ?? '',
            'access' => $_POST['access'] ?? ['read.all'],
            'teams' => $teams
        ];
        /* Prepare the View */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('sub-admin/team-member-invite/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }
}
