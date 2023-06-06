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

class DomainNameUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.domain_names')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('domain-names');
        }

        $domain_name_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->user->user_id)->getOne('domain_names')) {
            redirect('domain-names');
        }
        $domain_name->whois_notifications = json_decode($domain_name->whois_notifications);
        $domain_name->ssl_notifications = json_decode($domain_name->ssl_notifications);

        /* Get available projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        $domain_name_timings = require APP_PATH . 'includes/domain_name_timings.php';

        if(!empty($_POST)) {
            $_POST['name'] = query_clean($_POST['name']);
            $_POST['target'] = query_clean($_POST['target']);
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['whois_notifications'] = array_map(
                function($notification_handler_id) {
                    return (int) $notification_handler_id;
                },
                array_filter($_POST['whois_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['whois_notifications_timing'] = array_key_exists($_POST['whois_notifications_timing'], $domain_name_timings) ? $_POST['whois_notifications_timing'] : array_key_first($domain_name_timings);
            $whois_notifications = json_encode([
                'whois_notifications' => $_POST['whois_notifications'],
                'whois_notifications_timing' => $_POST['whois_notifications_timing'],
            ]);
            $_POST['ssl_notifications'] = array_map(
                function($notification_handler_id) {
                    return (int) $notification_handler_id;
                },
                array_filter($_POST['ssl_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            $_POST['ssl_notifications_timing'] = array_key_exists($_POST['ssl_notifications_timing'], $domain_name_timings) ? $_POST['ssl_notifications_timing'] : array_key_first($domain_name_timings);
            $ssl_notifications = json_encode([
                'ssl_notifications' => $_POST['ssl_notifications'],
                'ssl_notifications_timing' => $_POST['ssl_notifications_timing'],
            ]);
            $_POST['is_enabled'] = (int) (bool) $_POST['is_enabled'];

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'target'];
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
                db()->where('domain_name_id', $domain_name_id)->update('domain_names', [
                    'project_id' => $_POST['project_id'],
                    'name' => $_POST['name'],
                    'target' => $_POST['target'],
                    'whois_notifications' => $whois_notifications,
                    'ssl_notifications' => $ssl_notifications,
                    'is_enabled' => $_POST['is_enabled'],
                    'last_datetime' => \Altum\Date::$date,
                ]);

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItemsByTag('domain_name_id=' . $domain_name_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('domain-name-update/' . $domain_name_id);
            }

        }

        /* Set a custom title */
        Title::set(sprintf(l('domain_name_update.title'), $domain_name->name));

        /* Prepare the View */
        $data = [
            'projects' => $projects,
            'notification_handlers' => $notification_handlers,
            'domain_name' => $domain_name,
            'domain_name_timings' => $domain_name_timings,
        ];

        $view = new \Altum\View('domain-name-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
