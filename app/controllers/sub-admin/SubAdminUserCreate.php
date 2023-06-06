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
use Altum\Logger;
use Altum\Models\User;

class SubAdminUserCreate extends Controller
{

    public function index()
    {
        if (\Altum\Teams::get_main_user()->type != 2) {
            redirect('dashboard');
        }

        // get_teams
        $team = db()->where('user_id', \Altum\Teams::get_main_user()->user_id)->get('teams');

        /* Default variables */
        $values = [
            'name' => '',
            'email' => '',
            'team' => $team
        ];

        if (!empty($_POST)) {

            /* Clean some posted variables */
            $_POST['name']        = input_clean($_POST['name']);
            $_POST['email']        = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

            /* Default variables */
            $values['name'] = $_POST['name'];
            $values['email'] = $_POST['email'];
            $values['role'] = $_POST['role'];

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            /* Check for any errors */
            $required_fields = ['name', 'email', 'role', 'team'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if (!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }
            if (mb_strlen($_POST['name']) < 1 || mb_strlen($_POST['name']) > 64) {
                Alerts::add_field_error('name', l('admin_users.error_message.name_length'));
            }
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                Alerts::add_field_error('email', l('global.error_message.invalid_email'));
            }
            if (!in_array($_POST['role'], [0, 2, 3])) {
                Alerts::add_field_error('role', 'Choose given option!');
            }

            /* If there are no errors, continue */
            if (!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $registered_user = (new User())->create(
                    $_POST['email'],
                    null,
                    $_POST['name'],
                    $_POST['role'],
                    2,
                    'admin_create',
                    null,
                    null,
                    'free',
                    json_encode(settings()->plan_free->settings),
                    null,
                    settings()->main->default_timezone,
                    true
                );

                // set user token
                $user_id = $registered_user['user_id'];
                $token = \Altum\Csrf::get();
                db()->where('user_id', $user_id)->update('users', ['token_code' => $token]);

                // send user a mail to register
                $user_name = $_POST['name'];
                $user_email = $_POST['email'];
                $user_role = $_POST['role'];
                $team_id = $_POST['team'];
                $email_subject = 'Register to active user! - UptimeIQ';
                $sign_up_link = url("register?token=$token");
                $email_body = "<p>Dear $user_name,</p><p>You are added as a user. Register in the link below to active your account. </p><p>Link : <a href=" . $sign_up_link . ">Click to register</a></p><p>Thank You.</p>";
                send_mail($user_email, $email_subject, $email_body);

                // team member invitation for created user
                $access = [];

                if ($_POST['role'] == 2) {
                    $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => true, "create.notification_handlers" => true, "create.status_pages" => true, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => true, "update.notification_handlers" => true, "update.status_pages" => true, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => true, "delete.notification_handlers" => true, "delete.status_pages" => true, "delete.projects" => true];
                } elseif ($_POST['role'] == 3) {
                    $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.thresholds" => true, "create.domain_names" => false, "create.notification_handlers" => false, "create.status_pages" => false, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.thresholds" => true, "update.domain_names" => false, "update.notification_handlers" => false, "update.status_pages" => false, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.thresholds" => true, "delete.domain_names" => false, "delete.notification_handlers" => false, "delete.status_pages" => false, "delete.projects" => true];
                } elseif ($_POST['role'] == 0) {
                    $access = ["read.all" => true, "create.monitors" => false, "create.heartbeats" => false, "create.thresholds" => true, "create.domain_names" => false, "create.notification_handlers" => false, "create.status_pages" => false, "create.projects" => false, "update.monitors" => false, "update.heartbeats" => false, "update.thresholds" => true, "update.domain_names" => false, "update.notification_handlers" => false, "update.status_pages" => false, "update.projects" => false, "delete.monitors" => false, "delete.heartbeats" => false, "delete.thresholds" => true, "delete.domain_names" => false, "delete.notification_handlers" => false, "delete.status_pages" => false, "delete.projects" => false];
                }

                $team_member_data = [
                    'team_id' => $team_id,
                    'user_email' => $user_email,
                    'access' => json_encode($access),
                    'datetime' => \Altum\Date::$date,
                    'user_id' => $user_id,
                    'default_member' => 1,
                    'status'=>1
                ];
                $team_member_id = db()->insert('teams_members', $team_member_data);

                /* Send webhook notification if needed */
                if (settings()->webhooks->user_new) {

                    \Unirest\Request::post(settings()->webhooks->user_new, [], [
                        'user_id' => $registered_user['user_id'],
                        'email' => $_POST['email'],
                        'name' => $_POST['name'],
                        'type' => $_POST['role'],
                        'source' => 'admin_create',
                    ]);
                }

                /* Log the action */
                Logger::users($registered_user['user_id'], 'register.success');

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Redirect */
                redirect('sub-admin/user-update/' . $registered_user['user_id']);
            }
        }

        /* Main View */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('sub-admin/user-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }
}
