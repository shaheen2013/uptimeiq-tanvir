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
use Altum\Captcha;
use Altum\Logger;
use Altum\Models\User;
use MaxMind\Db\Reader;

class Register extends Controller
{

    public function index()
    {

        \Altum\Authentication::guard('guest');

        /* Check for a special registration identifier */
        $unique_registration_identifier = isset($_GET['unique_registration_identifier'], $_GET['email']) && $_GET['unique_registration_identifier'] == md5($_GET['email'] . $_GET['email']) ? query_clean($_GET['unique_registration_identifier']) : null;

        /* Check if Registration is enabled first */
        if (!settings()->users->register_is_enabled && (!\Altum\Plugin::is_active('teams') || (\Altum\Plugin::is_active('teams') && !$unique_registration_identifier))) {
            redirect();
        }

        $redirect = process_and_get_redirect_params() ?? 'dashboard';
        $redirect_append = $redirect ? '?redirect=' . $redirect : null;

        /* Default variables */
        $values = [
            'name' => isset($_GET['name']) ? query_clean($_GET['name']) : '',
            'email' => isset($_GET['email']) ? query_clean($_GET['email']) : '',
            'password' => '',
            'organization' => '',
            'type' => 2
        ];

        // check if the user is invited or not
        if (isset($_GET['token'])) {
            $user_data = db()->where('token_code', $_GET['token'])->getOne('users');
            if ($user_data != null) {
                $values['name'] = $user_data->name;
                $values['email'] = $user_data->email;
                $values['invited'] = true;
                $values['type'] = $user_data->type;
                $values['token'] = $user_data->token_code;
            } else {
                Alerts::add_error('Invalid invitation link!');
            }
        }

        /* Initiate captcha */
        $captcha = new Captcha();

        if (!empty($_POST)) {
            /* Clean some posted variables */
            $_POST['name'] = mb_substr(trim(input_clean($_POST['name'])), 0, 64);
            $_POST['email'] = mb_substr(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL), 0, 320);

            /* Default variables */
            $values['name'] = $_POST['name'];
            $values['email'] = $_POST['email'];
            $values['password'] = $_POST['password'];

            /* Check for any errors */
            $required_fields = ['name', 'email', 'password'];

            if (!isset($values['token'])) {
                $required_fields += ['organization'];
            }

            $user_invited = db()->where('email', $_POST['email'])->getOne('users', ['token_code', 'type']);
            if ($user_invited != null && $user_invited->token_code != null && $user_invited->type != 2) {
                unset($required_fields[3]);
            }

            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if (settings()->captcha->register_is_enabled && !$captcha->is_valid()) {
                Alerts::add_field_error('captcha', l('global.error_message.invalid_captcha'));
            }
            if (mb_strlen($_POST['name']) < 1 || mb_strlen($_POST['name']) > 64) {
                Alerts::add_field_error('name', l('register.error_message.name_length'));
            }

            if (!isset($_GET['token'])) {
                if (db()->where('email', $_POST['email'])->has('users')) {
                    Alerts::add_field_error('email', l('register.error_message.email_exists'));
                }
            }

            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                Alerts::add_field_error('email', l('global.error_message.invalid_email'));
            }
            if (mb_strlen($_POST['password']) < 6 || mb_strlen($_POST['password']) > 64) {
                Alerts::add_field_error('password', l('global.error_message.password_length'));
            }



            /* Make sure the domain is not blacklisted */
            // $email_domain = get_domain_from_email($_POST['email']);
            // if (settings()->users->blacklisted_domains && in_array($email_domain, explode(',', settings()->users->blacklisted_domains))) {
            //     Alerts::add_field_error('email', l('register.error_message.blacklisted_domain'));
            // }

            /* Detect the location */
            try {
                $maxmind = (new Reader(APP_PATH . 'includes/GeoLite2-Country.mmdb'))->get(get_ip());
            } catch (\Exception $exception) { /* :) */
            }
            $country = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;

            /* Make sure the country is not blacklisted */
            if ($country && in_array($country, settings()->users->blacklisted_countries ?? [])) {
                Alerts::add_error(l('register.error_message.blacklisted_country'));
            }

            /* If there are no errors continue the registering process */
            if (!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $user_invited = db()->where('email', $_POST['email'])->getOne('users');
                // check if the user is invited
                if ($user_invited != null && $user_invited->token_code != null) {

                    // if ($user_invited->type == 2) {
                    /* Prepare the statement to create team */
                    // $team_id = db()->insert('teams', [
                    //     'user_id' => $user_invited->user_id,
                    //     'name' => $_POST['organization'],
                    //     'datetime' => \Altum\Date::$date,
                    //     'default_team' => 1
                    // ]);

                    /* Prepare the statement to create member */
                    // $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.domain_names" => true, "create.notification_handlers" => true, "create.status_pages" => true, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.domain_names" => true, "update.notification_handlers" => true, "update.status_pages" => true, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.domain_names" => true, "delete.notification_handlers" => true, "delete.status_pages" => true, "delete.projects" => true];
                    // $team_member_data = [
                    //     'team_id' => $team_id,
                    //     'user_email' => $user_invited->email,
                    //     'access' => json_encode($access),
                    //     'datetime' => \Altum\Date::$date,
                    //     'user_id' => $user_invited->user_id,
                    //     'default_member' => 1,
                    //     'status'=> 1
                    // ];
                    // $team_member_id = db()->insert('teams_members', $team_member_data);
                    // }

                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $name = $_POST['name'];
                    db()->where('email', $_POST['email'])->update('users', ['token_code' => null, 'status' => 1, 'password' => $password, 'name' => $name]);

                    /* Set a nice success message */
                    Alerts::add_success(l('register.success_message.login'));

                    $_SESSION['user_id'] = $user_invited->user_id;
                    $_SESSION['user_password_hash'] = md5($password);

                    Logger::users($user_invited->user_id, 'login.success');

                    // check if has only one team then log into team
                    $logged_in_user = db()->where('user_id', $user_invited->user_id)->getOne('users', 'type');

                    $total_teams = db()->where('user_id', $user_invited->user_id)->where('status', 1)->where('default_member', 1)->get('teams_members', null, 'team_member_id');

                    $token = \Altum\Csrf::get();
                    $team_member_id = $total_teams[0]->team_member_id;
                    $redirect = "teams-members/login?team_member_id=$team_member_id&token=$token";

                    redirect($redirect);
                }


                $values = [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                ];

                /* Define some needed variables */
                $active                     = (int) !settings()->users->email_confirmation;
                $email_code                 = md5($_POST['email'] . microtime());

                /* Determine what plan is set by default */
                $plan_id                    = 'free';
                $plan_settings              = json_encode(settings()->plan_free->settings);
                $plan_expiration_date       = \Altum\Date::$date;

                $registered_user = (new User())->create(
                    $_POST['email'],
                    $_POST['password'],
                    $_POST['name'],
                    2,
                    (int) !settings()->users->email_confirmation,
                    'direct',
                    $email_code,
                    null,
                    $plan_id,
                    $plan_settings,
                    $plan_expiration_date,
                    settings()->main->default_timezone
                );

                /* Prepare the statement to create team */
                $team_id = db()->insert('teams', [
                    'user_id' => $registered_user['user_id'],
                    'name' => $_POST['organization'],
                    'datetime' => \Altum\Date::$date,
                ]);

                /* Prepare the statement to create member */
                $access = ["read.all" => true, "create.monitors" => true, "create.heartbeats" => true, "create.domain_names" => true, "create.notification_handlers" => true, "create.status_pages" => true, "create.projects" => true, "update.monitors" => true, "update.heartbeats" => true, "update.domain_names" => true, "update.notification_handlers" => true, "update.status_pages" => true, "update.projects" => true, "delete.monitors" => true, "delete.heartbeats" => true, "delete.domain_names" => true, "delete.notification_handlers" => true, "delete.status_pages" => true, "delete.projects" => true];
                $team_member_data = [
                    'team_id' => $team_id,
                    'user_email' => $_POST['email'],
                    'access' => json_encode($access),
                    'datetime' => \Altum\Date::$date,
                    'user_id' => $registered_user['user_id'],
                    'default_member' => 1,
                    'status' => 1
                ];
                $team_member_id = db()->insert('teams_members', $team_member_data);

                $token = \Altum\Csrf::get();
                $redirect = "teams-members/login?team_member_id=$team_member_id&token=$token";

                /* Log the action */
                Logger::users($registered_user['user_id'], 'register.success');

                /* If active = 1 then login the user, else send the user an activation email */
                if ($active == '1') {

                    /* Send notification to admin if needed */
                    if (settings()->email_notifications->new_user && !empty(settings()->email_notifications->emails)) {
                        /* Prepare the email */
                        $email_template = get_email_template(
                            [],
                            l('global.emails.admin_new_user_notification.subject'),
                            [
                                '{{NAME}}' => str_replace('.', '. ', $_POST['name']),
                                '{{EMAIL}}' => $_POST['email'],
                            ],
                            l('global.emails.admin_new_user_notification.body')
                        );

                        send_mail(explode(',', settings()->email_notifications->emails), $email_template->subject, $email_template->body);
                    }

                    /* Send webhook notification if needed */
                    if (settings()->webhooks->user_new) {
                        \Unirest\Request::post(settings()->webhooks->user_new, [], [
                            'user_id' => $registered_user['user_id'],
                            'email' => $_POST['email'],
                            'name' => $_POST['name'],
                            'source' => 'direct'
                        ]);
                    }

                    /* Set a nice success message */
                    Alerts::add_success(l('register.success_message.login'));

                    $_SESSION['user_id'] = $registered_user['user_id'];
                    $_SESSION['user_password_hash'] = md5($registered_user['password']);

                    Logger::users($registered_user['user_id'], 'login.success');

                    redirect($redirect);
                } else {

                    /* Prepare the email */
                    $email_template = get_email_template(
                        [
                            '{{NAME}}' => str_replace('.', '. ', $_POST['name']),
                        ],
                        l('global.emails.user_activation.subject'),
                        [
                            '{{ACTIVATION_LINK}}' => url('activate-user?email=' . md5($_POST['email']) . '&email_activation_code=' . $email_code . '&type=user_activation' . '&redirect=' . $redirect),
                            '{{NAME}}' => str_replace('.', '. ', $_POST['name']),
                        ],
                        l('global.emails.user_activation.body')
                    );

                    send_mail($_POST['email'], $email_template->subject, $email_template->body);

                    /* Set a nice success message */
                    Alerts::add_success(l('register.success_message.registration'));
                }
            }
        }

        /* Main View */
        $data = [
            'values' => $values,
            'captcha' => $captcha,
            'redirect_append' => $redirect_append,
        ];

        $view = new \Altum\View('register/index', (array) $this);

        $this->add_view_content('content', $view->run($data));
    }
}
