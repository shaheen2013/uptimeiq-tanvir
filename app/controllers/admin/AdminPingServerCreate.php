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

class AdminPingServerCreate extends Controller {

    public function index() {

        if(!empty($_POST)) {
            /* Clean some posted variables */
            $_POST['name'] = input_clean($_POST['name']);
            $_POST['url'] = input_clean($_POST['url']);
            $_POST['country_code'] = array_key_exists($_POST['country_code'], get_countries_array()) ? input_clean($_POST['country_code']) : 'US';
            $_POST['city_name'] = input_clean($_POST['city_name']);
            $_POST['is_enabled'] = (int) (bool) $_POST['is_enabled'];

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            /* Check for any errors */
            $required_fields = ['name', 'url', 'city_name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Update the row of the database */
                db()->insert('ping_servers', [
                    'name' => $_POST['name'],
                    'url' => $_POST['url'],
                    'country_code' => $_POST['country_code'],
                    'city_name' => $_POST['city_name'],
                    'is_enabled' => $_POST['is_enabled'],
                    'datetime' => \Altum\Date::$date,
                ]);

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItem('ping_servers');

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('admin/ping-servers');
            }

        }

        /* Main View */
        $data = [];

        $view = new \Altum\View('admin/ping-server-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
