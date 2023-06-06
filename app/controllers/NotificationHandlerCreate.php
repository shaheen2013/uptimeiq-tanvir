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

class NotificationHandlerCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.notification_handlers')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('notification-handlers');
        }

        $main_user=\Altum\Teams::get_main_user();

        if(!empty($_POST)) {
            $_POST['type'] = in_array($_POST['type'], require APP_PATH . 'includes/notification_handlers.php') ? input_clean($_POST['type']) : null;
            $_POST['name'] = input_clean($_POST['name']);

            //ALTUMCODE:DEMO if(DEMO) if($main_user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['type', 'name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Check for the plan limit */
            $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `notification_handlers` WHERE `user_id` = {$main_user->user_id} AND `type` = '{$_POST['type']}'")->fetch_object()->total ?? 0;

            if($main_user->plan_settings->{'notification_handlers_' . $_POST['type'] . '_limit'} != -1 && $total_rows >= $main_user->plan_settings->{'notification_handlers_' . $_POST['type'] . '_limit'}) {
                Alerts::add_error(l('global.info_message.plan_feature_limit'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = [];
                switch($_POST['type']) {
                    case 'telegram':
                        $settings['telegram'] = mb_substr(input_clean($_POST['telegram']), 0, 512);
                        $settings['telegram_chat_id'] = mb_substr(input_clean($_POST['telegram_chat_id']), 0, 512);
                        break;

                    default:
                        $settings[$_POST['type']] = mb_substr(input_clean($_POST[$_POST['type']]), 0, 512);
                        break;
                }
                $settings = json_encode($settings);

                /* Prepare the statement and execute query */
                db()->insert('notification_handlers', [
                    'user_id' => $main_user->user_id,
                    'type' => $_POST['type'],
                    'name' => $_POST['name'],
                    'settings' => $settings,
                    'datetime' => \Altum\Date::$date,
                    'team_id' => $main_user->team_id,
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItem('notification_handlers?user_id=' . $main_user->user_id);

                redirect('notification-handlers');
            }
        }

        $values = [
            'name' => $_POST['name'] ?? '',
            'type' => $_POST['type'] ?? '',
            'email' => $_POST['email'] ?? '',
            'webhook' => $_POST['webhook'] ?? '',
            'slack' => $_POST['slack'] ?? '',
            'discord' => $_POST['discord'] ?? '',
            'twilio' => $_POST['twilio'] ?? '',
            'telegram' => $_POST['telegram'] ?? '',
            'telegram_chat_id' => $_POST['telegram_chat_id'] ?? '',
        ];

        /* Prepare the View */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('notification-handler-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
