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

class AccountApi extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!empty($_POST)) {

            /* Clean some posted variables */
            $api_key = md5(\Altum\Teams::get_main_user()->email . microtime() . microtime());

            //ALTUMCODE:DEMO if(DEMO) if(\Altum\Teams::get_main_user()->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                db()->where('user_id', \Altum\Teams::get_main_user()->user_id)->update('users', ['api_key' => $api_key]);

                /* Set a nice success message */
                Alerts::add_success(l('account_api.success_message'));

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItemsByTag('user_id=' . \Altum\Teams::get_main_user()->user_id);

                redirect('account-api');
            }

        }

        /* Get the account header menu */
        $menu = new \Altum\View('partials/account_header_menu', (array) $this);
        $this->add_view_content('account_header_menu', $menu->run());

        /* Prepare the View */
        $view = new \Altum\View('account-api/index', (array) $this);

        $this->add_view_content('content', $view->run());

    }

}
