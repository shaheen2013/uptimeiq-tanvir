<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

use Altum\Models\User;

class Cron extends Controller {

    public function index() {
        die();
    }

    private function initiate() {
        /* Initiation */
        set_time_limit(0);

        /* Make sure the key is correct */
        if(!isset($_GET['key']) || (isset($_GET['key']) && $_GET['key'] != settings()->cron->key)) {
            die();
        }
    }

    private function update_cron_execution_datetimes($key) {
        $date = \Altum\Date::$date;

        /* Database query */
        database()->query("UPDATE `settings` SET `value` = JSON_SET(`value`, '$.{$key}', '{$date}') WHERE `key` = 'cron'");
    }

    public function reset() {

        $this->initiate();

        $this->users_deletion_reminder();

        $this->auto_delete_inactive_users();

        $this->auto_delete_unconfirmed_users();

        $this->users_plan_expiry_reminder();

        $this->update_cron_execution_datetimes('reset_datetime');

        /* Make sure the reset date month is different than the current one to avoid double resetting */
        $reset_date = (new \DateTime(settings()->cron->reset_date))->format('m');
        $current_date = (new \DateTime())->format('m');

        if($reset_date != $current_date) {
            $this->users_logs_cleanup();

            $this->statistics_cleanup();

            $this->update_cron_execution_datetimes('reset_date');

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItem('settings');
        }
    }

    private function users_deletion_reminder() {
        if(!settings()->users->auto_delete_inactive_users) {
            return;
        }

        /* Determine when to send the email reminder */
        $days_until_deletion = settings()->users->user_deletion_reminder;
        $days = settings()->users->auto_delete_inactive_users - $days_until_deletion;
        $past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

        /* Get the users that need to be reminded */
        $result = database()->query("
            SELECT `user_id`, `name`, `email`, `language`, `anti_phishing_code` FROM `users` WHERE `plan_id` = 'free' AND `last_activity` < '{$past_date}' AND `user_deletion_reminder` = 0 AND `type` = 0 LIMIT 25
        ");

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Prepare the email */
            $email_template = get_email_template(
                [
                    '{{DAYS_UNTIL_DELETION}}' => $days_until_deletion,
                ],
                l('global.emails.user_deletion_reminder.subject', $user->language),
                [
                    '{{DAYS_UNTIL_DELETION}}' => $days_until_deletion,
                    '{{LOGIN_LINK}}' => url('login'),
                    '{{NAME}}' => $user->name,
                ],
                l('global.emails.user_deletion_reminder.body', $user->language),
            );

            if(settings()->users->user_deletion_reminder) {
                send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);
            }

            /* Update user */
            db()->where('user_id', $user->user_id)->update('users', ['user_deletion_reminder' => 1]);

            if(DEBUG) {
                if(settings()->users->user_deletion_reminder) echo sprintf('User deletion reminder email sent for user_id %s', $user->user_id);
            }
        }

    }

    private function auto_delete_inactive_users() {
        if(!settings()->users->auto_delete_inactive_users) {
            return;
        }

        /* Determine what users to delete */
        $days = settings()->users->auto_delete_inactive_users;
        $past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

        /* Get the users that need to be reminded */
        $result = database()->query("
            SELECT `user_id`, `name`, `email`, `language`, `anti_phishing_code` FROM `users` WHERE `plan_id` = 'free' AND `last_activity` < '{$past_date}' AND `user_deletion_reminder` = 1 AND `type` = 0 LIMIT 25
        ");

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Prepare the email */
            $email_template = get_email_template(
                [],
                l('global.emails.auto_delete_inactive_users.subject', $user->language),
                [
                    '{{INACTIVITY_DAYS}}' => settings()->users->auto_delete_inactive_users,
                    '{{REGISTER_LINK}}' => url('register'),
                    '{{NAME}}' => $user->name,
                ],
                l('global.emails.auto_delete_inactive_users.body', $user->language)
            );

            send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

            /* Delete user */
            (new User())->delete($user->user_id);

            if(DEBUG) {
                echo sprintf('User deletion for inactivity user_id %s', $user->user_id);
            }
        }

    }

    private function auto_delete_unconfirmed_users() {
        if(!settings()->users->auto_delete_unconfirmed_users) {
            return;
        }

        /* Determine what users to delete */
        $days = settings()->users->auto_delete_unconfirmed_users;
        $past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

        /* Get the users that need to be reminded */
        $result = database()->query("SELECT `user_id` FROM `users` WHERE `status` = '0' AND `datetime` < '{$past_date}' LIMIT 100");

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Delete user */
            (new User())->delete($user->user_id);

            if(DEBUG) {
                echo sprintf('User deleted for unconfirmed account user_id %s', $user->user_id);
            }
        }
    }

    private function users_logs_cleanup() {
        /* Delete old users logs */
        $ninety_days_ago_datetime = (new \DateTime())->modify('-90 days')->format('Y-m-d H:i:s');
        db()->where('datetime', $ninety_days_ago_datetime, '<')->delete('users_logs');
    }

    private function statistics_cleanup() {

        /* Clean the track notifications table based on the users plan */
        $result = database()->query("SELECT `user_id`, `plan_settings` FROM `users` WHERE `status` = 1");

        /* Go through each result */
        while($user = $result->fetch_object()) {
            $user->plan_settings = json_decode($user->plan_settings);

            if($user->plan_settings->statistics_retention == -1) continue;

            /* Clear out old notification statistics logs */
            $x_days_ago_datetime = (new \DateTime())->modify('-' . ($row->plan_settings->statistics_retention ?? 90) . ' days')->format('Y-m-d H:i:s');
            database()->query("DELETE FROM `statistics` WHERE `datetime` < '{$x_days_ago_datetime}'");

            if(DEBUG) {
                echo sprintf('Status pages statistics cleanup done for user_id %s', $user->user_id);
            }
        }

    }

    private function users_plan_expiry_reminder() {

        /* Determine when to send the email reminder */
        $days = 5;
        $future_date = (new \DateTime())->modify('+' . $days . ' days')->format('Y-m-d H:i:s');

        /* Get potential monitors from users that have almost all the conditions to get an email report right now */
        $result = database()->query("
            SELECT
                `user_id`,
                `name`,
                `email`,
                `plan_id`,
                `plan_expiration_date`,
                `language`,
                `anti_phishing_code`
            FROM 
                `users`
            WHERE 
                `status` = 1
                AND `plan_id` <> 'free'
                AND `plan_expiry_reminder` = '0'
                AND (`payment_subscription_id` IS NULL OR `payment_subscription_id` = '')
				AND '{$future_date}' > `plan_expiration_date`
            LIMIT 25
        ");

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Determine the exact days until expiration */
            $days_until_expiration = (new \DateTime($user->plan_expiration_date))->diff((new \DateTime()))->days;

            /* Prepare the email */
            $email_template = get_email_template(
                [
                    '{{DAYS_UNTIL_EXPIRATION}}' => $days_until_expiration,
                ],
                l('global.emails.user_plan_expiry_reminder.subject', $user->language),
                [
                    '{{DAYS_UNTIL_EXPIRATION}}' => $days_until_expiration,
                    '{{USER_PLAN_RENEW_LINK}}' => url('pay/' . $user->plan_id),
                    '{{NAME}}' => $user->name,
                    '{{PLAN_NAME}}' => (new \Altum\Models\Plan())->get_plan_by_id($user->plan_id)->name,
                ],
                l('global.emails.user_plan_expiry_reminder.body', $user->language)
            );

            send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

            /* Update user */
            db()->where('user_id', $user->user_id)->update('users', ['plan_expiry_reminder' => 1]);

            if(DEBUG) {
                echo sprintf('Email sent for user_id %s', $user->user_id);
            }
        }

    }

    public function monitors() {

        $this->initiate();

        $date = \Altum\Date::$date;

        /* Update cron job last run date */
        $this->update_cron_execution_datetimes('monitors_datetime');

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();

        /* Determine how many checks to do */
        $foreach_loops = php_sapi_name() == 'cli' ? 50 : 35;
        $checks_limit = php_sapi_name() == 'cli' ? 5 : 5;
        $query_limit = $checks_limit * 3;

        for($i = 1; $i <= $foreach_loops; $i++) {
            $result = database()->query("
                SELECT
                    `monitors`.*,
                    `users`.`email`,
                    `users`.`plan_settings`,
                    `users`.`language`,
                    `users`.`timezone`
                FROM 
                    `monitors`
                LEFT JOIN 
                    `users` ON `monitors`.`user_id` = `users`.`user_id` 
                WHERE 
                    `monitors`.`is_enabled` = 1
                    AND `monitors`.`next_check_datetime` <= '{$date}' 
                    AND `users`.`status` = 1
                ORDER BY `monitors`.`next_check_datetime`
                LIMIT {$query_limit}
            ");

            /* Break if no results */
            if(!$result->num_rows) break;

            $callables = [];

            while($row = $result->fetch_object()) {
                $row->plan_settings = json_decode($row->plan_settings);
                $row->settings = json_decode($row->settings);
                $row->ping_servers_ids = json_decode($row->ping_servers_ids);
                $row->notifications = json_decode($row->notifications);

                $callables[] = function () use ($row, $ping_servers)  {
                    /* Get available notification handlers */
                    $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);

                    if(DEBUG) printf("Starting to check %s (%s) monitor...\n", $row->name, $row->target);

                    $check = \Altum\Monitor::check($row, $ping_servers);

                    /* If the monitor is down, double check to be sure */
                    if(!$check['is_ok']) {
                        sleep(3);
                        $check = \Altum\Monitor::check($row, $ping_servers, $check['ping_server_id']);
                    }

                    $vars = \Altum\Monitor::vars($row, $check);

                    /* Insert the history log */
                    $monitor_log_id = db()->insert('monitors_logs', [
                        'monitor_id' => $row->monitor_id,
                        'ping_server_id' => $check['ping_server_id'],
                        'user_id' => $row->user_id,
                        'is_ok' => $check['is_ok'],
                        'response_time' => $check['response_time'],
                        'response_status_code' => $check['response_status_code'],
                        'error' => isset($check['error']) ? json_encode($check['error']) : null,
                        'datetime' => \Altum\Date::$date
                    ]);

                    /* Create / update an incident if needed */
                    $incident_id = $row->incident_id;

                    if(!$check['is_ok'] && !$row->incident_id) {

                        /* Get the language for the user and set the timezone */
                        \Altum\Date::$timezone = $row->timezone;

                        /* Database query */
                        $incident_id = db()->insert('incidents', [
                            'monitor_id' => $row->monitor_id,
                            'start_monitor_log_id' => $monitor_log_id,
                            'start_datetime' => \Altum\Date::$date
                        ]);

                        /* Processing the notification handlers */
                        foreach($notification_handlers as $notification_handler) {
                            if(!$notification_handler->is_enabled) continue;
                            if(!in_array($notification_handler->notification_handler_id, $row->notifications->is_ok)) continue;

                            switch($notification_handler->type) {
                                case 'email':

                                    /* Prepare the email title */
                                    $email_title = sprintf(l('cron.is_not_ok.title', $row->language), $row->name);

                                    /* Prepare the View for the email content */
                                    $data = [
                                        'row' => $row
                                    ];

                                    $email_content = (new \Altum\View('partials/cron/monitor_is_not_ok', (array) $this))->run($data);

                                    /* Send the email */
                                    send_mail($notification_handler->settings->email, $email_title, $email_content);

                                    break;

                                case 'webhook';

                                    try {
                                        \Unirest\Request::post($notification_handler->settings->webhook, [], [
                                            'monitor_id' => $row->monitor_id,
                                            'name' => $row->name,
                                            'is_ok' => $check['is_ok'],
                                        ]);
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    break;

                                case 'slack';

                                    try {
                                        \Unirest\Request::post(
                                            $notification_handler->settings->slack,
                                            ['Accept' => 'application/json'],
                                            \Unirest\Request\Body::json([
                                                'text' => sprintf(l('monitor.slack_notifications.is_not_ok', $row->language), $row->name, $row->target . ($row->port ? ':' . $row->port : null)),
                                                'username' => settings()->main->title,
                                                'icon_emoji' => ':large_red_square:'
                                            ])
                                        );
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    break;

                                case 'discord';

                                    try {
                                        \Unirest\Request::post(
                                            $notification_handler->settings->discord,
                                            [
                                                'Accept' => 'application/json',
                                                'Content-Type' => 'application/json',
                                            ],
                                            \Unirest\Request\Body::json([
                                                'embeds' => [
                                                    [
                                                        'title' => sprintf(
                                                            l('monitor.discord_notifications.is_not_ok', $row->language),
                                                            $row->name,
                                                            $row->target . ($row->port ? ':' . $row->port : null)
                                                        ),
                                                        'color' => '14431557',
                                                        'url' => url('monitor/' . $row->monitor_id)
                                                    ]
                                                ],
                                            ])
                                        );
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    break;

                                case 'telegram';

                                    try {
                                        \Unirest\Request::get(
                                            sprintf(
                                                'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
                                                $notification_handler->settings->telegram,
                                                $notification_handler->settings->telegram_chat_id,
                                                sprintf(
                                                    l('monitor.telegram_notifications.is_not_ok', $row->language),
                                                    $row->name,
                                                    $row->target . ($row->port ? ':' . $row->port : null)
                                                )
                                            )
                                        );
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    break;

                                case 'twilio';

                                    try {
                                        \Unirest\Request::auth(settings()->monitors_heartbeats->twilio_sid, settings()->monitors_heartbeats->twilio_token);

                                        \Unirest\Request::post(
                                            sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', settings()->monitors_heartbeats->twilio_sid),
                                            [],
                                            [
                                                'From' => settings()->monitors_heartbeats->twilio_number,
                                                'To' => $notification_handler->settings->twilio,
                                                'Body' => sprintf(l('monitor.twilio_notifications.is_not_ok', $row->language), $row->name, $row->target . ($row->port ? ':' . $row->port : null)),
                                            ]
                                        );
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    \Unirest\Request::auth('', '');

                                    break;
                            }
                        }
                    }

                    /* Close incident */
                    if($check['is_ok'] && $row->incident_id) {

                        /* Get the language for the user and set the timezone */
                        \Altum\Date::$timezone = $row->timezone;

                        /* Database query */
                        db()->where('incident_id', $row->incident_id)->update('incidents', [
                            'monitor_id' => $row->monitor_id,
                            'end_monitor_log_id' => $monitor_log_id,
                            'end_datetime' => \Altum\Date::$date
                        ]);

                        $incident_id = null;

                        /* Get details about the incident */
                        $monitor_incident = db()->where('incident_id', $row->incident_id)->getOne('incidents', ['start_datetime', 'end_datetime']);

                        /* Processing the notification handlers */
                        foreach($notification_handlers as $notification_handler) {
                            if(!$notification_handler->is_enabled) continue;
                            if(!in_array($notification_handler->notification_handler_id, $row->notifications->is_ok)) continue;

                            switch($notification_handler->type) {
                                case 'email':

                                    /* Prepare the email title */
                                    $email_title = sprintf(l('cron.is_ok.title', $row->language), $row->name);

                                    /* Prepare the View for the email content */
                                    $data = [
                                        'monitor_incident' => $monitor_incident,
                                        'row' => $row
                                    ];

                                    $email_content = (new \Altum\View('partials/cron/monitor_is_ok', (array)$this))->run($data);

                                    /* Send the email */
                                    send_mail($notification_handler->settings->email, $email_title, $email_content);

                                    break;

                                case 'webhook';

                                    try {
                                        \Unirest\Request::post($notification_handler->settings->webhook, [], [
                                            'monitor_id' => $row->monitor_id,
                                            'name' => $row->name,
                                            'is_ok' => $check['is_ok'],
                                        ]);
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    break;

                                case 'slack';

                                    try {
                                        \Unirest\Request::post(
                                            $notification_handler->settings->slack,
                                            ['Accept' => 'application/json'],
                                            \Unirest\Request\Body::json([
                                                'text' => sprintf(l('monitor.slack_notifications.is_ok', $row->language), $row->name, $row->target . ($row->port ? ':' . $row->port : null)),
                                                'username' => settings()->main->title,
                                                'icon_emoji' => ':large_green_circle:'
                                            ])
                                        );
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    break;

                                case 'discord';

                                    try {
                                        \Unirest\Request::post(
                                            $notification_handler->settings->discord,
                                            [
                                                'Accept' => 'application/json',
                                                'Content-Type' => 'application/json',
                                            ],
                                            \Unirest\Request\Body::json([
                                                'embeds' => [
                                                    [
                                                        'title' => sprintf(
                                                            l('monitor.discord_notifications.is_ok', $row->language),
                                                            $row->name,
                                                            $row->target . ($row->port ? ':' . $row->port : null)
                                                        ),
                                                        'color' => '2664261',
                                                        'url' => url('monitor/' . $row->monitor_id)
                                                    ]
                                                ],
                                            ])
                                        );
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    break;

                                case 'telegram';

                                    try {
                                        \Unirest\Request::get(
                                            sprintf(
                                                'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
                                                $notification_handler->settings->telegram,
                                                $notification_handler->settings->telegram_chat_id,
                                                sprintf(
                                                    l('monitor.telegram_notifications.is_ok', $row->language),
                                                    $row->name,
                                                    $row->target . ($row->port ? ':' . $row->port : null)
                                                )
                                            )
                                        );
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    break;

                                case 'twilio';

                                    try {
                                        \Unirest\Request::auth(settings()->monitors_heartbeats->twilio_sid, settings()->monitors_heartbeats->twilio_token);

                                        \Unirest\Request::post(
                                            sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', settings()->monitors_heartbeats->twilio_sid),
                                            [],
                                            [
                                                'From' => settings()->monitors_heartbeats->twilio_number,
                                                'To' => $notification_handler->settings->twilio,
                                                'Body' => sprintf(l('monitor.twilio_notifications.is_ok', $row->language), $row->name, $row->target . ($row->port ? ':' . $row->port : null)),
                                            ]
                                        );
                                    } catch (\Exception $exception) {
                                        // :)
                                    }

                                    \Unirest\Request::auth('', '');

                                    break;
                            }
                        }
                    }

                    /* Update the monitor */
                    db()->where('monitor_id', $row->monitor_id)->update('monitors', [
                        'incident_id' => $incident_id,
                        'is_ok' => $check['is_ok'],
                        'uptime' => $vars['uptime'],
                        'uptime_seconds' => $vars['uptime_seconds'],
                        'downtime' => $vars['downtime'],
                        'downtime_seconds' => $vars['downtime_seconds'],
                        'average_response_time' => $vars['average_response_time'],
                        'total_checks' => db()->inc(),
                        'total_ok_checks' => $vars['total_ok_checks'],
                        'total_not_ok_checks' => $vars['total_not_ok_checks'],
                        'last_check_datetime' => $vars['last_check_datetime'],
                        'next_check_datetime' => $vars['next_check_datetime'],
                        'main_ok_datetime' => $vars['main_ok_datetime'],
                        'last_ok_datetime' => $vars['last_ok_datetime'],
                        'main_not_ok_datetime' => $vars['main_not_ok_datetime'],
                        'last_not_ok_datetime' => $vars['last_not_ok_datetime'],
                    ]);

                    /* Clear out old monitor logs */
                    if($row->plan_settings->logs_retention != -1) {
                        $x_days_ago_datetime = (new \DateTime())->modify('-' . ($row->plan_settings->logs_retention ?? 90) . ' days')->format('Y-m-d H:i:s');
                        database()->query("DELETE FROM `monitors_logs` WHERE `datetime` < '{$x_days_ago_datetime}' AND `user_id` = {$row->user_id}");
                    }

                    /* Clear the cache */
                    \Altum\Cache::$adapter->deleteItemsByTag('monitor_id=' . $row->monitor_id);

                    return $row->monitor_id;
                };
            }

            /* Randomize the callables */
            shuffle($callables);

            /* Only allow the maximum checks for this run */
            $callables = array_slice($callables, 0, $checks_limit);

            $time_start = microtime(true);

            if(php_sapi_name() == 'cli') {
                $results = \Spatie\Fork\Fork::new()
                    ->before(function () { \Altum\Database::initialize(); })
                    ->after(function () { \Altum\Database::close(); })
                    ->run(...$callables);
            } else {
                foreach($callables as $callable) {
                    $callable();
                }
            }

            echo 'Checks finished in ' . (microtime(true) - $time_start) . ' seconds.';
            \Altum\Database::close();
            \Altum\Database::initialize();
        }

    }

    public function heartbeats() {

        $this->initiate();

        $date = \Altum\Date::$date;

        /* Update cron job last run date */
        $this->update_cron_execution_datetimes('heartbeats_datetime');

        for($i = 1; $i <= 1000; $i++) {
            $row = database()->query("
                SELECT
                    `heartbeats`.*,
                       
                    `users`.`email`,
                    `users`.`plan_settings`,
                    `users`.`language`,
                    `users`.`timezone`
                FROM 
                    `heartbeats`
                LEFT JOIN 
                    `users` ON `heartbeats`.`user_id` = `users`.`user_id` 
                WHERE 
                    `heartbeats`.`is_enabled` = 1
                    AND `heartbeats`.`next_run_datetime` <= '{$date}' 
                    AND `users`.`status` = 1
                LIMIT 1
            ")->fetch_object();

            /* Break if no results */
            if(!$row) break;

            if(DEBUG) printf('Going through %s heartbeat..<br />', $row->name);

            $row->plan_settings = json_decode($row->plan_settings);
            $row->settings = json_decode($row->settings);
            $row->notifications = json_decode($row->notifications);

            /* Get available notification handlers */
            $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);

            /* Since the result is here, the cron is not working */
            $is_ok = 0;

            /* Insert the history log */
            $heartbeat_log_id = db()->insert('heartbeats_logs', [
                'heartbeat_id' => $row->heartbeat_id,
                'user_id' => $row->user_id,
                'is_ok' => $is_ok,
                'datetime' => \Altum\Date::$date,
            ]);

            /* Assuming, based on the run interval */
            $downtime_seconds_to_add = 0;
            switch($row->settings->run_interval_type) {
                case 'minutes':
                    $downtime_seconds_to_add = $row->settings->run_interval * 60;
                    break;

                case 'hours':
                    $downtime_seconds_to_add = $row->settings->run_interval * 60 * 60;
                    break;

                case 'days':
                    $downtime_seconds_to_add = $row->settings->run_interval * 60 * 60 * 24;
                    break;
            }
            $uptime_seconds = $row->uptime_seconds;
            $downtime_seconds = $row->downtime_seconds + $downtime_seconds_to_add;

            /* ^_^ */
            $uptime = $uptime_seconds > 0 ? $uptime_seconds / ($uptime_seconds + $downtime_seconds) * 100 : 0;
            $downtime = 100 - $uptime;
            $main_missed_datetime = $row->is_ok && !$is_ok ? \Altum\Date::$date : $row->main_missed_datetime;
            $last_missed_datetime = \Altum\Date::$date;

            /* Calculate expected next run */
            $next_run_datetime = (new \DateTime())
                ->modify('+' . $row->settings->run_interval . ' ' . $row->settings->run_interval_type)
                ->modify('+' . $row->settings->run_interval_grace . ' ' . $row->settings->run_interval_grace_type)
                ->format('Y-m-d H:i:s');

            /* Create / update an incident if needed */
            $incident_id = $row->incident_id;

            if(!$is_ok && !$row->incident_id) {

                /* Database query */
                $incident_id = db()->insert('incidents', [
                    'heartbeat_id' => $row->heartbeat_id,
                    'start_heartbeat_log_id' => $heartbeat_log_id,
                    'start_datetime' => \Altum\Date::$date,
                ]);

                /* Get the language for the user and set the timezone */
                \Altum\Date::$timezone = $row->timezone;

                /* Processing the notification handlers */
                foreach($notification_handlers as $notification_handler) {
                    if(!$notification_handler->is_enabled) continue;
                    if(!in_array($notification_handler->notification_handler_id, $row->notifications->is_ok)) continue;

                    switch($notification_handler->type) {
                        case 'email':

                            /* Prepare the email title */
                            $email_title = sprintf(l('cron.is_not_ok.title', $row->language), $row->name);

                            /* Prepare the View for the email content */
                            $data = [
                                'row' => $row
                            ];

                            $email_content = (new \Altum\View('partials/cron/heartbeat_is_not_ok', (array)$this))->run($data);

                            /* Send the email */
                            send_mail($notification_handler->settings->email, $email_title, $email_content);

                            break;

                        case 'webhook';

                            try {
                                \Unirest\Request::post($notification_handler->settings->webhook, [], [
                                    'heartbeat_id' => $row->heartbeat_id,
                                    'name' => $row->name,
                                    'is_ok' => $is_ok,
                                ]);
                            } catch (\Exception $exception) {
                                // :)
                            }

                            break;

                        case 'slack';

                            try {
                                \Unirest\Request::post(
                                    $notification_handler->settings->slack,
                                    ['Accept' => 'application/json'],
                                    \Unirest\Request\Body::json([
                                        'text' => sprintf(l('heartbeat.slack_notifications.is_not_ok', $row->language), $row->name),
                                        'username' => settings()->main->title,
                                        'icon_emoji' => ':large_red_square:'
                                    ])
                                );
                            } catch (\Exception $exception) {
                                // :)
                            }

                            break;

                        case 'discord';

                            try {
                                \Unirest\Request::post(
                                    $notification_handler->settings->discord,
                                    [
                                        'Accept' => 'application/json',
                                        'Content-Type' => 'application/json',
                                    ],
                                    \Unirest\Request\Body::json([
                                        'embeds' => [
                                            [
                                                'title' => sprintf(l('heartbeat.discord_notifications.is_not_ok', $row->language), $row->name),
                                                'color' => '14431557',
                                                'url' => url('heartbeat/' . $row->heartbeat_id)
                                            ]
                                        ],
                                    ])
                                );
                            } catch (\Exception $exception) {
                                // :)
                            }

                            break;

                        case 'telegram';

                            try {
                                \Unirest\Request::get(
                                    sprintf(
                                        'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
                                        $notification_handler->settings->telegram,
                                        $notification_handler->settings->telegram_chat_id,
                                        sprintf(
                                            l('heartbeat.telegram_notifications.is_not_ok', $row->language),
                                            $row->name
                                        )
                                    )
                                );
                            } catch (\Exception $exception) {
                                // :)
                            }

                            break;

                        case 'twilio';

                            try {
                                \Unirest\Request::auth(settings()->monitors_heartbeats->twilio_sid, settings()->monitors_heartbeats->twilio_token);

                                \Unirest\Request::post(
                                    sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', settings()->monitors_heartbeats->twilio_sid),
                                    [],
                                    [
                                        'From' => settings()->monitors_heartbeats->twilio_number,
                                        'To' => $notification_handler->settings->twilio,
                                        'Body' => sprintf(l('heartbeat.twilio_notifications.is_not_ok', $row->language), $row->name),
                                    ]
                                );
                            } catch (\Exception $exception) {
                                // :)
                            }

                            \Unirest\Request::auth('', '');

                            break;
                    }
                }
            }

            /* Update the heartbeat */
            db()->where('heartbeat_id', $row->heartbeat_id)->update('heartbeats', [
                'incident_id' => $incident_id,
                'is_ok' => $is_ok,
                'uptime' => $uptime,
                'uptime_seconds' => $uptime_seconds,
                'downtime' => $downtime,
                'downtime_seconds' => $downtime_seconds,
                'total_missed_runs' => db()->inc(),
                'main_missed_datetime' => $main_missed_datetime,
                'last_missed_datetime' => $last_missed_datetime,
                'next_run_datetime' => $next_run_datetime,
            ]);

            /* Clear out old heartbeats logs */
            if($row->plan_settings->logs_retention != -1) {
                $x_days_ago_datetime = (new \DateTime())->modify('-' . ($row->plan_settings->logs_retention ?? 90) . ' days')->format('Y-m-d H:i:s');
                database()->query("DELETE FROM `heartbeats_logs` WHERE `datetime` < '{$x_days_ago_datetime}' AND `user_id` = {$row->user_id}");
            }

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('heartbeat_id=' . $row->heartbeat_id);

        }

    }

    public function domain_names() {

        $this->initiate();

        $date = \Altum\Date::$date;

        /* Update cron job last run date */
        $this->update_cron_execution_datetimes('domain_names_datetime');

        for($i = 1; $i <= 1000; $i++) {
            $row = database()->query("
                SELECT
                    `domain_names`.*,
                    `users`.`email`,
                    `users`.`plan_settings`,
                    `users`.`language`,
                    `users`.`timezone`
                FROM 
                    `domain_names`
                LEFT JOIN 
                    `users` ON `domain_names`.`user_id` = `users`.`user_id` 
                WHERE 
                    `domain_names`.`is_enabled` = 1
                    AND `domain_names`.`next_check_datetime` <= '{$date}' 
                    AND `users`.`status` = 1
                ORDER BY `domain_names`.`next_check_datetime`
                LIMIT 1
            ")->fetch_object();

            /* Break if no results */
            if(!$row) break;

            if(DEBUG) printf('Going through %s (%s) domain name..<br />', $row->name, $row->target);

            $row->plan_settings = json_decode($row->plan_settings);
            $row->whois_notifications = json_decode($row->whois_notifications);
            $row->ssl_notifications = json_decode($row->ssl_notifications);

            /* Get available notification handlers */
            $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($row->user_id);

            /* Check the domain name whois */
            try {
                $get_whois = \Iodev\Whois\Factory::get()->createWhois();
                $whois_info = $get_whois->loadDomainInfo($row->target);
            } catch (\Exception $e) {
                //
            }

            $whois = isset($whois_info) && $whois_info ? [
                'start_datetime' => $whois_info->creationDate ? (new \DateTime())->setTimestamp($whois_info->creationDate)->format('Y-m-d H:i:s') : null,
                'updated_datetime' => $whois_info->updatedDate ? (new \DateTime())->setTimestamp($whois_info->updatedDate)->format('Y-m-d H:i:s') : null,
                'end_datetime' => $whois_info->expirationDate ? (new \DateTime())->setTimestamp($whois_info->expirationDate)->format('Y-m-d H:i:s') : null,
                'registrar' => $whois_info->registrar,
                'nameservers' => $whois_info->nameServers,
            ] : [];

            /* Check for an SSL certificate */
            $certificate = get_website_certificate('https://' . $row->target);

            /* Create the new SSL object */
            $ssl = [];
            if($certificate) {
                $ssl = [
                    'issuer_name' => $certificate['issuer']['O'],
                    'issuer_full' => $certificate['issuer']['CN'],
                    'start_datetime' => (new \DateTime())->setTimestamp($certificate['validFrom_time_t'])->format('Y-m-d H:i:s'),
                    'end_datetime' => (new \DateTime())->setTimestamp($certificate['validTo_time_t'])->format('Y-m-d H:i:s'),
                ];
            }

            /* Get the language for the user and set the timezone */
            \Altum\Date::$timezone = $row->timezone;

            /* Processing the notification handlers */
            foreach($notification_handlers as $notification_handler) {
                if(!$notification_handler->is_enabled) continue;

                /* :) */
                $whois_expires_in_days = isset($whois['end_datetime']) ? (new \DateTime($whois['end_datetime']))->diff(new \DateTime())->days : null;
                $ssl_expires_in_days = isset($ssl['end_datetime']) ? (new \DateTime($ssl['end_datetime']))->diff(new \DateTime())->days : null;

                switch($notification_handler->type) {
                    case 'email':

                        if(in_array($notification_handler->notification_handler_id, $row->whois_notifications->whois_notifications) && $whois) {
                            if($whois_expires_in_days && $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing && (!isset($row->whois->last_notification_datetime) || ($row->whois->last_notification_datetime && (new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                /* Prepare the email title */
                                $email_title = sprintf(l('domain_name.email_notifications.whois.title', $row->language), $row->name, $row->target, $whois_expires_in_days);

                                /* Prepare the View for the email content */
                                $data = [
                                    'row' => $row,
                                    'whois_expires_in_days' => $whois_expires_in_days,
                                    'whois_end_datetime' => \Altum\Date::get($whois['end_datetime']),
                                    'timezone' => $row->timezone,
                                ];

                                $email_content = (new \Altum\View('domain-name/domain_name_whois_notification', (array) $this))->run($data);

                                /* Send the email */
                                send_mail($notification_handler->settings->email, $email_title, $email_content);

                            }
                        }

                        if(in_array($notification_handler->notification_handler_id, $row->ssl_notifications->ssl_notifications) && $ssl) {
                            if($ssl_expires_in_days && $ssl_expires_in_days <= $row->ssl_notifications->ssl_notifications_timing && (!isset($row->ssl->last_notification_datetime) || ($row->ssl->last_notification_datetime && (new \DateTime($row->ssl->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                /* Prepare the email title */
                                $email_title = sprintf(l('domain_name.email_notifications.ssl.title', $row->language), $row->name, $row->target, $ssl_expires_in_days);

                                /* Prepare the View for the email content */
                                $data = [
                                    'row' => $row,
                                    'ssl_expires_in_days' => $ssl_expires_in_days,
                                    'ssl_end_datetime' => $ssl['end_datetime'],
                                    'timezone' => $row->timezone,
                                ];

                                $email_content = (new \Altum\View('domain-name/domain_name_ssl_notification', (array) $this))->run($data);

                                /* Send the email */
                                send_mail($notification_handler->settings->email, $email_title, $email_content);

                            }
                        }


                        break;

                    case 'webhook';

                        if(in_array($notification_handler->notification_handler_id, $row->whois_notifications->whois_notifications) && $whois) {
                            if($whois_expires_in_days && $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing && (!isset($row->whois->last_notification_datetime) || ($row->whois->last_notification_datetime && (new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::post($notification_handler->settings->webhook, [], [
                                        'domain_name_id' => $row->domain_name_id,
                                        'name' => $row->name,
                                        'target' => $row->target,
                                        'whois_end_datetime' => \Altum\Date::get($whois['end_datetime']),
                                        'timezone' => $row->timezone
                                    ]);
                                } catch (\Exception $exception) {
                                    // :)
                                }

                            }
                        }

                        if(in_array($notification_handler->notification_handler_id, $row->ssl_notifications->ssl_notifications) && $ssl) {
                            if($ssl_expires_in_days && $ssl_expires_in_days <= $row->ssl_notifications->ssl_notifications_timing && (!isset($row->ssl->last_notification_datetime) || ($row->ssl->last_notification_datetime && (new \DateTime($row->ssl->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::post($notification_handler->settings->webhook, [], [
                                        'domain_name_id' => $row->domain_name_id,
                                        'name' => $row->name,
                                        'target' => $row->target,
                                        'ssl_end_datetime' => \Altum\Date::get($ssl['end_datetime']),
                                        'timezone' => $row->timezone
                                    ]);
                                } catch (\Exception $exception) {
                                    // :)
                                }

                            }
                        }

                        break;

                    case 'slack';

                        if(in_array($notification_handler->notification_handler_id, $row->whois_notifications->whois_notifications) && $whois) {
                            if($whois_expires_in_days && $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing && (!isset($row->whois->last_notification_datetime) || ($row->whois->last_notification_datetime && (new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::post(
                                        $notification_handler->settings->slack,
                                        ['Accept' => 'application/json'],
                                        \Unirest\Request\Body::json([
                                            'text' => sprintf(l('domain_name.slack_notifications.whois', $row->language), $row->name, $row->target, $whois_expires_in_days, \Altum\Date::get($whois['end_datetime']), $row->timezone),
                                            'username' => settings()->main->title
                                        ])
                                    );
                                } catch (\Exception $exception) {
                                    // :)
                                }

                            }
                        }

                        if(in_array($notification_handler->notification_handler_id, $row->ssl_notifications->ssl_notifications) && $ssl) {
                            if($ssl_expires_in_days && $ssl_expires_in_days <= $row->ssl_notifications->ssl_notifications_timing && (!isset($row->ssl->last_notification_datetime) || ($row->ssl->last_notification_datetime && (new \DateTime($row->ssl->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::post(
                                        $notification_handler->settings->slack,
                                        ['Accept' => 'application/json'],
                                        \Unirest\Request\Body::json([
                                            'text' => sprintf(l('domain_name.slack_notifications.ssl', $row->language), $row->name, $row->target, $ssl_expires_in_days, \Altum\Date::get($ssl['end_datetime']), $row->timezone),
                                            'username' => settings()->main->title
                                        ])
                                    );
                                } catch (\Exception $exception) {
                                    // :)
                                }

                            }
                        }

                        break;

                    case 'discord';

                        if(in_array($notification_handler->notification_handler_id, $row->whois_notifications->whois_notifications) && $whois) {
                            if($whois_expires_in_days && $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing && (!isset($row->whois->last_notification_datetime) || ($row->whois->last_notification_datetime && (new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::post(
                                        $notification_handler->settings->discord,
                                        [
                                            'Accept' => 'application/json',
                                            'Content-Type' => 'application/json',
                                        ],
                                        \Unirest\Request\Body::json([
                                            'embeds' => [
                                                [
                                                    'title' => sprintf(l('domain_name.discord_notifications.whois', $row->language), $row->name, $row->target, $whois_expires_in_days, \Altum\Date::get($whois['end_datetime']), $row->timezone),
                                                    'url' => url('domain-names')
                                                ]
                                            ],
                                        ])
                                    );
                                } catch (\Exception $exception) {
                                    // :)
                                }

                            }
                        }

                        if(in_array($notification_handler->notification_handler_id, $row->ssl_notifications->ssl_notifications) && $ssl) {
                            if($ssl_expires_in_days && $ssl_expires_in_days <= $row->ssl_notifications->ssl_notifications_timing && (!isset($row->ssl->last_notification_datetime) || ($row->ssl->last_notification_datetime && (new \DateTime($row->ssl->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::post(
                                        $notification_handler->settings->discord,
                                        [
                                            'Accept' => 'application/json',
                                            'Content-Type' => 'application/json',
                                        ],
                                        \Unirest\Request\Body::json([
                                            'embeds' => [
                                                [
                                                    'title' => sprintf(l('domain_name.discord_notifications.ssl', $row->language), $row->name, $row->target, $ssl_expires_in_days, \Altum\Date::get($ssl['end_datetime']), $row->timezone),
                                                    'url' => url('domain-names')
                                                ]
                                            ],
                                        ])
                                    );
                                } catch (\Exception $exception) {
                                    // :)
                                }

                            }
                        }

                        break;

                    case 'telegram';

                        if(in_array($notification_handler->notification_handler_id, $row->whois_notifications->whois_notifications) && $whois) {
                            if($whois_expires_in_days && $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing && (!isset($row->whois->last_notification_datetime) || ($row->whois->last_notification_datetime && (new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::get(
                                        sprintf(
                                            'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
                                            $notification_handler->settings->telegram,
                                            $notification_handler->settings->telegram_chat_id,
                                            sprintf(l('domain_name.telegram_notifications.whois', $row->language), $row->name, $row->target, $whois_expires_in_days, \Altum\Date::get($whois['end_datetime']), $row->timezone)
                                        )
                                    );
                                } catch (\Exception $exception) {
                                    // :)
                                }

                            }
                        }

                        if(in_array($notification_handler->notification_handler_id, $row->ssl_notifications->ssl_notifications) && $ssl) {
                            if($ssl_expires_in_days && $ssl_expires_in_days <= $row->ssl_notifications->ssl_notifications_timing && (!isset($row->ssl->last_notification_datetime) || ($row->ssl->last_notification_datetime && (new \DateTime($row->ssl->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::get(
                                        sprintf(
                                            'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
                                            $notification_handler->settings->telegram,
                                            $notification_handler->settings->telegram_chat_id,
                                            sprintf(l('domain_name.telegram_notifications.ssl', $row->language), $row->name, $row->target, $ssl_expires_in_days, \Altum\Date::get($ssl['end_datetime']), $row->timezone)
                                        )
                                    );
                                } catch (\Exception $exception) {
                                    // :)
                                }

                            }
                        }

                        break;

                    case 'twilio';

                        if(in_array($notification_handler->notification_handler_id, $row->whois_notifications->whois_notifications) && $whois) {
                            if($whois_expires_in_days && $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing && (!isset($row->whois->last_notification_datetime) || ($row->whois->last_notification_datetime && (new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::auth(settings()->monitors_heartbeats->twilio_sid, settings()->monitors_heartbeats->twilio_token);

                                    \Unirest\Request::post(
                                        sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', settings()->monitors_heartbeats->twilio_sid),
                                        [],
                                        [
                                            'From' => settings()->monitors_heartbeats->twilio_number,
                                            'To' => $notification_handler->settings->twilio,
                                            'Body' => sprintf(l('domain_name.twilio_notifications.whois', $row->language), $row->name, $row->target, $whois_expires_in_days, \Altum\Date::get($whois['end_datetime']), $row->timezone),
                                        ]
                                    );
                                } catch (\Exception $exception) {
                                    // :)
                                }

                                \Unirest\Request::auth('', '');

                            }
                        }

                        if(in_array($notification_handler->notification_handler_id, $row->ssl_notifications->ssl_notifications) && $ssl) {
                            if($ssl_expires_in_days && $ssl_expires_in_days <= $row->ssl_notifications->ssl_notifications_timing && (!isset($row->ssl->last_notification_datetime) || ($row->ssl->last_notification_datetime && (new \DateTime($row->ssl->last_notification_datetime))->diff(new \DateTime())->days > 30))) {

                                try {
                                    \Unirest\Request::auth(settings()->monitors_heartbeats->twilio_sid, settings()->monitors_heartbeats->twilio_token);

                                    \Unirest\Request::post(
                                        sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', settings()->monitors_heartbeats->twilio_sid),
                                        [],
                                        [
                                            'From' => settings()->monitors_heartbeats->twilio_number,
                                            'To' => $notification_handler->settings->twilio,
                                            'Body' => sprintf(l('domain_name.twilio_notifications.ssl', $row->language), $row->name, $row->target, $ssl_expires_in_days, \Altum\Date::get($ssl['end_datetime']), $row->timezone),
                                        ]
                                    );
                                } catch (\Exception $exception) {
                                    // :)
                                }

                                \Unirest\Request::auth('', '');

                            }
                        }

                        break;
                }
            }

            if($whois_expires_in_days && $whois_expires_in_days <= $row->whois_notifications->whois_notifications_timing && (!isset($row->whois->last_notification_datetime) || ($row->whois->last_notification_datetime && (new \DateTime($row->whois->last_notification_datetime))->diff(new \DateTime())->days > 30))) {
                $whois['last_notification_datetime'] = \Altum\Date::$date;
                $ssl['last_notification_datetime'] = \Altum\Date::$date;
            }

            $whois = json_encode(empty($whois) ? (object) [] : $whois);
            $ssl = json_encode(empty($ssl) ? (object) [] : $ssl);

            /* Update the domain name */
            db()->where('domain_name_id', $row->domain_name_id)->update('domain_names', [
                'whois' => $whois,
                'ssl' => $ssl,
                'total_checks' => db()->inc(),
                'last_check_datetime' => \Altum\Date::$date,
                'next_check_datetime' => (new \DateTime())->modify('+1 day')->format('Y-m-d H:i:s'),
            ]);

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItemsByTag('domain_name_id=' . $row->domain_name_id);

        }

    }

    public function monitors_email_reports() {

        $this->initiate();

        $date = \Altum\Date::$date;

        /* Update cron job last run date */
        $this->update_cron_execution_datetimes('monitors_email_reports_datetime');

        /* Only run this part if the email reports are enabled */
        if(!settings()->monitors_heartbeats->email_reports_is_enabled) {
            return;
        }

        /* Determine the frequency of email reports */
        $days_interval = 7;

        switch(settings()->monitors_heartbeats->email_reports_is_enabled) {
            case 'weekly':
                $days_interval = 7;

                break;

            case 'monthly':
                $days_interval = 30;

                break;
        }

        /* Get potential monitors from users that have almost all the conditions to get an email report right now */
        $result = database()->query("
            SELECT
                `monitors`.`monitor_id`,
                `monitors`.`name`,
                `monitors`.`email_reports_last_datetime`,
                `users`.`user_id`,
                `users`.`email`,
                `users`.`plan_settings`,
                `users`.`language`
            FROM 
                `monitors`
            LEFT JOIN 
                `users` ON `monitors`.`user_id` = `users`.`user_id` 
            WHERE 
                `users`.`status` = 1
                AND `monitors`.`is_enabled` = 1 
                AND `monitors`.`email_reports_is_enabled` = 1
				AND DATE_ADD(`monitors`.`email_reports_last_datetime`, INTERVAL {$days_interval} DAY) <= '{$date}'
            LIMIT 25
        ");

        /* Go through each result */
        while($row = $result->fetch_object()) {
            $row->plan_settings = json_decode($row->plan_settings);

            /* Make sure the plan still lets the user get email reports */
            if(!$row->plan_settings->email_reports_is_enabled) {
                db()->where('monitor_id', $row->monitor_id)->update('monitors', ['email_reports_is_enabled' => 0]);

                continue;
            }

            /* Prepare */
            $start_date = (new \DateTime())->modify('-' . $days_interval . ' days')->format('Y-m-d H:i:s');

            /* Monitor logs */
            $monitor_logs = [];

            $monitor_logs_result = database()->query("
                SELECT 
                    `is_ok`,
                    `response_time`,
                    `datetime`
                FROM 
                    `monitors_logs`
                WHERE 
                    `monitor_id` = {$row->monitor_id} 
                    AND (`datetime` BETWEEN '{$start_date}' AND '{$date}')
            ");

            $total_ok_checks = 0;
            $total_not_ok_checks = 0;
            $total_response_time = 0;

            while($monitor_log = $monitor_logs_result->fetch_object()) {
                $monitor_logs[] = $monitor_log;

                $total_ok_checks = $monitor_log->is_ok ? $total_ok_checks + 1 : $total_ok_checks;
                $total_not_ok_checks = !$monitor_log->is_ok ? $total_not_ok_checks + 1 : $total_not_ok_checks;
                $total_response_time += $monitor_log->response_time;
            }

            /* Monitor incidents */
            $monitor_incidents = [];

            $monitor_incidents_result = database()->query("
                SELECT 
                    `start_datetime`,
                    `end_datetime`
                FROM 
                    `incidents`
                WHERE 
                    `monitor_id` = {$row->monitor_id} 
                    AND `start_datetime` >= '{$start_date}' 
                    AND `end_datetime` <= '{$date}'
            ");

            while($monitor_incident = $monitor_incidents_result->fetch_object()) {
                $monitor_incidents[] = $monitor_incident;
            }

            /* calculate some data */
            $total_monitor_logs = count($monitor_logs);
            $uptime = $total_ok_checks > 0 ? $total_ok_checks / ($total_ok_checks + $total_not_ok_checks) * 100 : 0;
            $downtime = 100 - $uptime;
            $average_response_time = $total_ok_checks > 0 ? $total_response_time / $total_ok_checks : 0;

            /* Prepare the email title */
            $email_title = sprintf(
                l('cron.monitor_email_report.title', $row->language),
                $row->name,
                \Altum\Date::get($start_date, 5),
                \Altum\Date::get('', 5)
            );

            /* Prepare the View for the email content */
            $data = [
                'row'                       => $row,
                'monitor_logs'              => $monitor_logs,
                'total_monitor_logs'        => $total_monitor_logs,
                'monitor_logs_data' => [
                    'uptime'                => $uptime,
                    'downtime'              => $downtime,
                    'average_response_time' => $average_response_time,
                    'total_ok_checks'       => $total_ok_checks,
                    'total_not_ok_checks'   => $total_not_ok_checks
                ],
                'monitor_incidents'         => $monitor_incidents,

                'start_date'                => $start_date,
                'end_date'                  => $date
            ];

            $email_content = (new \Altum\View('partials/cron/monitor_email_report', (array) $this))->run($data);

            /* Send the email */
            send_mail($row->email, $email_title, $email_content);

            /* Update the store */
            db()->where('monitor_id', $row->monitor_id)->update('monitors', ['email_reports_last_datetime' => $date]);

            /* Insert email log */
            db()->insert('email_reports', ['user_id' => $row->user_id, 'monitor_id' => $row->monitor_id, 'datetime' => $date]);

            if(DEBUG) {
                echo sprintf('Email sent for user_id %s and monitor_id %s', $row->user_id, $row->monitor_id);
            }
        }

    }

    public function heartbeats_email_reports() {

        $this->initiate();

        $date = \Altum\Date::$date;

        /* Update cron job last run date */
        $this->update_cron_execution_datetimes('heartbeats_email_reports_datetime');

        /* Only run this part if the email reports are enabled */
        if(!settings()->monitors_heartbeats->email_reports_is_enabled) {
            return;
        }

        /* Determine the frequency of email reports */
        $days_interval = 7;

        switch(settings()->monitors_heartbeats->email_reports_is_enabled) {
            case 'weekly':
                $days_interval = 7;

                break;

            case 'monthly':
                $days_interval = 30;

                break;
        }

        /* Get potential heartbeats from users that have almost all the conditions to get an email report right now */
        $result = database()->query("
            SELECT
                `heartbeats`.`heartbeat_id`,
                `heartbeats`.`name`,
                `heartbeats`.`email_reports_last_datetime`,
                `users`.`user_id`,
                `users`.`email`,
                `users`.`plan_settings`,
                `users`.`language`
            FROM 
                `heartbeats`
            LEFT JOIN 
                `users` ON `heartbeats`.`user_id` = `users`.`user_id` 
            WHERE 
                `users`.`status` = 1
                AND `heartbeats`.`is_enabled` = 1 
                AND `heartbeats`.`email_reports_is_enabled` = 1
				AND DATE_ADD(`heartbeats`.`email_reports_last_datetime`, INTERVAL {$days_interval} DAY) <= '{$date}'
            LIMIT 25
        ");

        /* Go through each result */
        while($row = $result->fetch_object()) {
            $row->plan_settings = json_decode($row->plan_settings);

            /* Make sure the plan still lets the user get email reports */
            if(!$row->plan_settings->email_reports_is_enabled) {
                db()->where('heartbeat_id', $row->heartbeat_id)->update('heartbeats', ['email_reports_is_enabled' => 0]);

                continue;
            }

            /* Prepare */
            $start_date = (new \DateTime())->modify('-' . $days_interval . ' days')->format('Y-m-d H:i:s');

            /* Monitor logs */
            $heartbeat_logs = [];

            $heartbeat_logs_result = database()->query("
                SELECT 
                    `is_ok`,
                    `datetime`
                FROM 
                    `heartbeats_logs`
                WHERE 
                    `heartbeat_id` = {$row->heartbeat_id} 
                    AND (`datetime` BETWEEN '{$start_date}' AND '{$date}')
            ");

            $total_runs = 0;
            $total_missed_runs = 0;

            while($heartbeat_log = $heartbeat_logs_result->fetch_object()) {
                $heartbeat_logs[] = $heartbeat_log;

                $total_runs = $heartbeat_log->is_ok ? $total_runs + 1 : $total_runs;
                $total_missed_runs = !$heartbeat_log->is_ok ? $total_missed_runs + 1 : $total_missed_runs;
            }

            /* Monitor incidents */
            $heartbeat_incidents = [];

            $heartbeat_incidents_result = database()->query("
                SELECT 
                    `start_datetime`,
                    `end_datetime`
                FROM 
                    `incidents`
                WHERE 
                    `heartbeat_id` = {$row->heartbeat_id} 
                    AND `start_datetime` >= '{$start_date}' 
                    AND `end_datetime` <= '{$date}'
            ");

            while($heartbeat_incident = $heartbeat_incidents_result->fetch_object()) {
                $heartbeat_incidents[] = $heartbeat_incident;
            }

            /* calculate some data */
            $total_heartbeat_logs = count($heartbeat_logs);
            $uptime = $total_runs > 0 ? $total_runs / ($total_runs + $total_missed_runs) * 100 : 0;
            $downtime = 100 - $uptime;

            /* Prepare the email title */
            $email_title = sprintf(
                l('cron.heartbeat_email_report.title', $row->language),
                $row->name,
                \Altum\Date::get($start_date, 5),
                \Altum\Date::get('', 5)
            );

            /* Prepare the View for the email content */
            $data = [
                'row'                       => $row,
                'heartbeat_logs'            => $heartbeat_logs,
                'total_heartbeat_logs'      => $total_heartbeat_logs,
                'heartbeat_logs_data' => [
                    'uptime'                => $uptime,
                    'downtime'              => $downtime,
                    'total_runs'            => $total_runs,
                    'total_missed_runs'     => $total_missed_runs
                ],
                'heartbeat_incidents'       => $heartbeat_incidents,

                'start_date'                => $start_date,
                'end_date'                  => $date
            ];

            $email_content = (new \Altum\View('partials/cron/heartbeat_email_report', (array) $this))->run($data);

            /* Send the email */
            send_mail($row->email, $email_title, $email_content);

            /* Update the store */
            db()->where('heartbeat_id', $row->heartbeat_id)->update('heartbeats', ['email_reports_last_datetime' => $date]);

            /* Insert email log */
            db()->insert('email_reports', ['user_id' => $row->user_id, 'heartbeat_id' => $row->heartbeat_id, 'datetime' => $date]);

            if(DEBUG) {
                echo sprintf('Email sent for user_id %s and heartbeat_id %s', $row->user_id, $row->heartbeat_id);
            }
        }

    }

}
