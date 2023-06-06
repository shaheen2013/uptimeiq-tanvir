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

class AccountDelete extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!empty($_POST)) {

            //ALTUMCODE:DEMO if(DEMO) if(\Altum\Teams::get_main_user()->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!password_verify($_POST['current_password'], \Altum\Teams::get_main_user()->password)) {
                Alerts::add_field_error('current_password', l('account.error_message.invalid_current_password'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Delete the user */
                (new User())->delete(\Altum\Teams::get_main_user()->user_id);

                /* Send notification to admin if needed */
                if(settings()->email_notifications->delete_user && !empty(settings()->email_notifications->emails)) {
                    $email_template = get_email_template(
                        [],
                        l('global.emails.admin_delete_user_notification.subject'),
                        [
                            '{{NAME}}' => \Altum\Teams::get_main_user()->name,
                            '{{EMAIL}}' => \Altum\Teams::get_main_user()->email,
                        ],
                        l('global.emails.admin_delete_user_notification.body')
                    );

                    send_mail(explode(',', settings()->email_notifications->emails), $email_template->subject, $email_template->body);
                }

                /* Logout of the user */
                \Altum\Authentication::logout(false);

                /* Start a new session to set a deletion message */
                session_start();

                /* Set a nice success message */
                Alerts::add_success(l('account_delete.success_message'));

                redirect();

            }

        }

        /* Get the account header menu */
        $menu = new \Altum\View('partials/account_header_menu', (array) $this);
        $this->add_view_content('account_header_menu', $menu->run());

        /* Prepare the View */
        $view = new \Altum\View('account-delete/index', (array) $this);

        $this->add_view_content('content', $view->run([]));

    }

}
