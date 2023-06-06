<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

class WebhookThreshold extends Controller {

    public function index() {
        /* Clean the threshold code */
        $code = isset($this->params[0]) ? query_clean($this->params[0]) : false;
        
        /* Clean the threshold value */
        $threshold_value = isset($_GET['value'])?query_clean($_GET['value']): false;
        
        /* Get the details of the campaign from the database */
        $threshold = (new \Altum\Models\Thresholds())->get_threshold_by_code($code);
        $threshold->notifications = json_decode($threshold->notifications);
        $threshold->settings = json_decode($threshold->settings);

        /* Make sure the campaign has access */
        if(!$threshold) {
            http_response_code(401);
            die();
        }

        if(!$threshold->is_enabled) {
            http_response_code(403);
            die();
        }

        /* Make sure we don't get spammed */
        if($threshold->last_run_datetime && (new \DateTime($threshold->last_run_datetime))->modify('+60 seconds') > (new \DateTime())) {
            http_response_code(403);
            die();
        }

        /* Make sure to get the user data and confirm the user is ok */
        $user = (new \Altum\Models\User())->get_user_by_user_id($threshold->user_id);

        if(!$user) {
            http_response_code(403);
            die();
        }

        if(!$user->status) {
            http_response_code(403);
            die();
        }

        /* Make sure the user's plan is not already expired */
        if((new \DateTime()) > (new \DateTime($user->plan_expiration_date)) && $user->plan_id != 'free') {
            http_response_code(403);
            die();
        }
        
        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($user->user_id);
        
        
        $is_ok = 1;
        
        /* Threshold breach check */
        if($threshold->settings->threshold_breach_value_type == 'below' && $threshold->settings->threshold_breach_value > $threshold_value){
            $is_ok = 0;
        }
        else if($threshold->settings->threshold_breach_value_type == 'above' && $threshold->settings->threshold_breach_value < $threshold_value){
            $is_ok = 0; 
        }
        /* Insert the history log */
        $threshold_log_id = db()->insert('thresholds_logs', [
            'threshold_id' => $threshold->threshold_id,
            'user_id' => $user->user_id,
            'is_ok' => $is_ok,
            'threshold_value'=>$threshold_value,
            'datetime' => \Altum\Date::$date,
        ]);

        /* Assuming, based on the run interval */
        $uptime_seconds_to_add = 0;
        switch($threshold->settings->run_interval_type) {
            case 'minutes':
                $uptime_seconds_to_add = $threshold->settings->run_interval * 60;
                break;

            case 'hours':
                $uptime_seconds_to_add = $threshold->settings->run_interval * 60 * 60;
                break;

            case 'days':
                $uptime_seconds_to_add = $threshold->settings->run_interval * 60 * 60 * 24;
                break;
        }
        $uptime_seconds = $threshold->uptime_seconds + $uptime_seconds_to_add;
        $downtime_seconds = $threshold->downtime_seconds;

        /* ^_^ */
        $uptime = $uptime_seconds > 0 ? $uptime_seconds / ($uptime_seconds + $downtime_seconds) * 100 : 0;
        $downtime = 100 - $uptime;
        $main_run_datetime = !$threshold->main_run_datetime || (!$threshold->is_ok && $is_ok) ? \Altum\Date::$date : $threshold->main_run_datetime;
        $last_run_datetime = \Altum\Date::$date;

        /* Calculate expected next run */
        $next_run_datetime = (new \DateTime())
            ->modify('+' . $threshold->settings->run_interval . ' ' . $threshold->settings->run_interval_type)
            ->modify('+' . $threshold->settings->run_interval_grace . ' ' . $threshold->settings->run_interval_grace_type)
            ->format('Y-m-d H:i:s');

        /* Create / update an incident if needed */
        $incident_id = $threshold->incident_id;

        /* Close incident */
        if(!$is_ok && $threshold->incident_id) {
            /* Database query */
            db()->where('incident_id', $threshold->incident_id)->update('incidents', [
                'end_threshold_log_id' => $threshold_log_id,
                'end_datetime' => \Altum\Date::$date,
            ]);
            
            // $incident_id = null;
            
            /* Get details about the incident */
            $threshold_incident = db()->where('incident_id', $threshold->incident_id)->getOne('incidents', ['start_datetime', 'end_datetime']);
            
            /* Get the language for the user */
            \Altum\Date::$timezone = $user->timezone;
            
            /* Processing the notification handlers */
            
            foreach($notification_handlers as $notification_handler) {
                
                if(!$notification_handler->is_enabled) continue;
                if(!in_array($notification_handler->notification_handler_id, $threshold->notifications->is_ok)) continue;
                
                switch($notification_handler->type) {
                    case 'email':
                        
                        /* Prepare the email title */
                        $email_title = sprintf(l('cron.is_not_ok.title', $user->language), $threshold->name);

                        /* Prepare the View for the email content */
                        $data = [
                            'threshold_incident' => $threshold_incident,
                            'row' => $threshold
                        ];

                        $email_content = (new \Altum\View('partials/cron/threshold_is_not_ok', (array)$this))->run($data);

                        /* Send the email */
                        
                        send_mail($user->email, $email_title, $email_content);

                        break;

                    case 'webhook';

                        try {
                            \Unirest\Request::post($notification_handler->settings->webhook, [], [
                                'threshold_id' => $threshold->threshold_id,
                                'name' => $threshold->name,
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
                                    'text' => sprintf(l('threshold.slack_notifications.is_ok', $user->language), $threshold->name),
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
                                            'title' => sprintf(l('threshold.discord_notifications.is_ok', $user->language), $threshold->name),
                                            'color' => '2664261',
                                            'url' => url('threshold/' . $threshold->threshold_id)
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
                                        l('threshold.telegram_notifications.is_ok', $user->language),
                                        $threshold->name
                                    )
                                )
                            );
                        } catch (\Exception $exception) {
                            // :)
                        }

                        break;

                    case 'twilio';

                        try {
                            \Unirest\Request::auth(settings()->monitors_thresholds->twilio_sid, settings()->monitors_thresholds->twilio_token);

                            \Unirest\Request::post(
                                sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', settings()->monitors_thresholds->twilio_sid),
                                [],
                                [
                                    'From' => settings()->monitors_thresholds->twilio_number,
                                    'To' => $notification_handler->settings->twilio,
                                    'Body' => sprintf(l('threshold.twilio_notifications.is_ok', $user->language), $threshold->name),
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

        /* Update the threshold */
        db()->where('threshold_id', $threshold->threshold_id)->update('thresholds', [
            'incident_id' => $incident_id,
            'is_ok' => $is_ok,
            'uptime' => $uptime,
            'uptime_seconds' => $uptime_seconds,
            'downtime' => $downtime,
            'downtime_seconds' => $downtime_seconds,
            'total_runs' => db()->inc(),
            'main_run_datetime' => $main_run_datetime,
            'last_run_datetime' => $last_run_datetime,
            'next_run_datetime' => $next_run_datetime,
        ]);

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('threshold_id=' . $threshold->threshold_id);

    }
}
