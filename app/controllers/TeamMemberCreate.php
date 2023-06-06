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

class TeamMemberCreate extends Controller
{

    public function index()
    {

        \Altum\Authentication::guard();

        if (!\Altum\Plugin::is_active('teams')) {
            redirect('dashboard');
        }

        $main_user_id=\Altum\Teams::get_main_user()->user_id;
        $main_user_email=\Altum\Teams::get_main_user()->email;


        $team_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if (!$team = db()->where('team_id', $team_id)->where('user_id', $main_user_id)->getOne('teams')) {
            redirect('teams');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `teams_members` WHERE `team_id` = {$team->team_id}")->fetch_object()->total ?? 0;

        if (\Altum\Teams::get_main_user()->plan_settings->team_members_limit != -1 && $total_rows >= \Altum\Teams::get_main_user()->plan_settings->team_members_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('team/' . $team->team_id);
        }

        $teams_access = require APP_PATH . 'includes/teams_access.php';

        if (!empty($_POST) || !empty($_GET)) {
            if (!empty($_POST)) {
                $user_email = trim(filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL));
            } else {
                $user_email = trim(filter_var($_GET['user_email'], FILTER_SANITIZE_EMAIL));
            }

            /* Generate the access variable for the database */
            $access = [];

            if ($_POST['access'] == 'admin' || $_GET['role'] == 2) {
                $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => true, "create.notification_handlers" => true, "create.status_pages" => true, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => true, "update.notification_handlers" => true, "update.status_pages" => true, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => true, "delete.notification_handlers" => true, "delete.status_pages" => true, "delete.projects" => true];
            } elseif ($_POST['access'] == 'operator' || $_GET['role'] == 3) {
                $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => false, "create.notification_handlers" => false, "create.status_pages" => false, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => false, "update.notification_handlers" => false, "update.status_pages" => false, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => false, "delete.notification_handlers" => false, "delete.status_pages" => false, "delete.projects" => true];
            } elseif ($_POST['access'] == 'author' || $_GET['role'] == 0) {
                $access = ["read.all" => true, "create.monitors" => false, "create.heartbeats" => false, "create.thresholds" => true, "create.domain_names" => false, "create.notification_handlers" => false, "create.status_pages" => false, "create.projects" => false, "update.monitors" => false, "update.heartbeats" => false, "update.thresholds" => true, "update.domain_names" => false, "update.notification_handlers" => false, "update.status_pages" => false, "update.projects" => false, "delete.monitors" => false, "delete.heartbeats" => false, "delete.thresholds" => true, "delete.domain_names" => false, "delete.notification_handlers" => false, "delete.status_pages" => false, "delete.projects" => false];
            }

            //ALTUMCODE:DEMO if(DEMO) if(\Altum\Teams::get_main_user()->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['user_email'];
            foreach ($required_fields as $field) {
                if (!empty($_POST)) {
                    if (!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                        Alerts::add_field_error($field, l('global.error_message.empty_field'));
                    }
                } else {
                    if (!isset($_GET[$field]) || (isset($_GET[$field]) && empty($_GET[$field]) && $_GET[$field] != '0')) {
                        Alerts::add_field_error($field, l('global.error_message.empty_field'));
                    }
                }
            }

            if (!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                Alerts::add_field_error('user_email', l('global.error_message.invalid_email'));
            }

            if ($user_email == $main_user_email) {
                Alerts::add_field_error('user_email', '');
            }

            if (db()->where('user_email', $user_email)->where('team_id', $team->team_id)->has('teams_members')) {
                Alerts::add_field_error('user_email', l('team_members.error_message.email_exists'));
            }

            if (!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Prepare the statement and execute query */
                $team_member_data = [
                    'team_id' => $team->team_id,
                    'user_email' => $user_email,
                    'access' => json_encode($access),
                    'datetime' => \Altum\Date::$date,
                ];
                if (isset($_GET['user_id'])) {
                    $team_member_data['user_id'] = $_GET['user_id'];
                }
                $team_member_id = db()->insert('teams_members', $team_member_data);

                /* Is the invited user already registered on the platform? */
                $user_exists = db()->where('email', $user_email)->has('users');

                /* Prepare the email */
                $email_template = get_email_template(
                    [
                        '{{TEAM_NAME}}' => $team->name,
                    ],
                    l('global.emails.team_member_create.subject'),
                    [
                        '{{TEAM_NAME}}' => $team->name,
                        '{{USER_NAME}}' => str_replace('.', '. ', \Altum\Teams::get_main_user()->name),
                        '{{USER_EMAIL}}' => \Altum\Teams::get_main_user()->email,
                        '{{LOGIN_LINK}}' => url('login?redirect=teams-system&email=' . $user_email),
                        '{{REGISTER_LINK}}' => url('register?redirect=teams-system&email=' . $user_email) . '&unique_registration_identifier=' . md5($user_email . $user_email),
                    ],
                    $user_exists ? l('global.emails.team_member_create.body_login') : l('global.emails.team_member_create.body_register')
                );

                send_mail($user_email, $email_template->subject, $email_template->body);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('team_member_create.success_message'), '<strong>' . $user_email . '</strong>'));

                redirect('team/' . $team_id);
            }
        }

        /* Set default values */
        $values = [
            'user_email' => $_POST['user_email'] ?? '',
            'access' => $_POST['access'] ?? ['read.all'],
        ];

        /* Set a custom title */
        Title::set(sprintf(l('team_member_create.title'), $team->name));

        /* Prepare the View */
        $data = [
            'values' => $values,
            'team' => $team,
            'teams_access' => $teams_access,
        ];

        $view = new \Altum\View('team-member-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

    public function invite()
    {
        if (\Altum\Teams::get_main_user()->type !=1 && \Altum\Teams::get_main_user()->type !=2) {
            redirect('teams');
        }
        $joined_teams = db()->where('user_id', $main_user_id)->get('teams_members', null, ['team_id']);

        $teams_id = [];
        foreach ($joined_teams as $key => $value) {
            $teams_id[$key] = $value->team_id;
        };

        $teams = [];
        foreach ($teams_id as $k => $val) {
            $team = db()->where('team_id', $val)->getOne('teams');
            $teams[$k] = $team;
        }

        if (\Altum\Teams::get_main_user()->type ==1) {
            $team_created = db()->where('user_id', $main_user_id)->get('teams', null, ['team_id','name']);
            $teams+=$team_created;
        }

        /* Set default values */
        $values = [
            'user_email' => $_POST['user_email'] ?? '',
            'access' => $_POST['access'] ?? ['read.all'],
            'teams' => $teams
        ];
        /* Set a custom title */
        Title::set('Team member create');
        /* Prepare the View */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('team-member-create/invite', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }

    public function memberInvite()
    {
        if (\Altum\Teams::get_main_user()->type !=1 && \Altum\Teams::get_main_user()->type !=2) {
            redirect('teams');
        }

        if (!$user = db()->where('email', $_POST['user_email'])->getOne('users')) {
            echo json_encode('invalid_user');
            exit;
        }

        $access = [];

        if ($_POST['access'] == 'admin') {
            $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => true, "create.notification_handlers" => true, "create.status_pages" => true, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => true, "update.notification_handlers" => true, "update.status_pages" => true, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => true, "delete.notification_handlers" => true, "delete.status_pages" => true, "delete.projects" => true];
        } elseif ($_POST['access'] == 'operator') {
            $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => false, "create.notification_handlers" => false, "create.status_pages" => false, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => false, "update.notification_handlers" => false, "update.status_pages" => false, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => false, "delete.notification_handlers" => false, "delete.status_pages" => false, "delete.projects" => true];
        } elseif ($_POST['access'] == 'author') {
            $access = ["read.all" => true, "create.monitors" => false, "create.heartbeats" => false, "create.thresholds" => true, "create.domain_names" => false, "create.notification_handlers" => false, "create.status_pages" => false, "create.projects" => false, "update.monitors" => false, "update.heartbeats" => false, "update.thresholds" => true, "update.domain_names" => false, "update.notification_handlers" => false, "update.status_pages" => false, "update.projects" => false, "delete.monitors" => false, "delete.heartbeats" => false, "delete.thresholds" => true, "delete.domain_names" => false, "delete.notification_handlers" => false, "delete.status_pages" => false, "delete.projects" => false];
        }

        $team_member_data = [
            'team_id' => $_POST['team_id'],
            'user_email' => $_POST['user_email'],
            'access' => json_encode($access),
            'datetime' => \Altum\Date::$date,
            'user_id' => $user->user_id,
        ];
        $team_member_id = db()->insert('teams_members', $team_member_data);

        $login_link=url('login');
        $email_subject = 'Team Invitation! - UptimeIQ';
        $email_body = "<p>Dear $$user->name,</p><p>Please log in to accept the invitation.</p><p>Link : <a href=" . $login_link . ">Click to Log in</a></p><p>Thank You.</p>";
        send_mail($_POST['user_email'], $email_subject, $email_body);

        echo json_encode($team_member_id);
        exit;
    }
}
