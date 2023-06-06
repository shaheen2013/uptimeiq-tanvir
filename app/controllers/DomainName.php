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

class DomainName extends Controller {

    public function index() {

        redirect('domain-names');

    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.domain_names')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('domain-names');
        }

        if(empty($_POST)) {
            redirect('domain-names');
        }

        $domain_name_id = (int) query_clean($_POST['domain_name_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        /* Make sure the domain name id is created by the logged in user */
        if(!$domain_name = db()->where('domain_name_id', $domain_name_id)->where('user_id', $this->user->user_id)->getOne('domain_names')) {
            redirect('domain-names');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the domain_name */
            db()->where('domain_name_id', $domain_name->domain_name_id)->delete('domain_names');

            /* Clear cache */
            \Altum\Cache::$adapter->deleteItemsByTag('domain_name_id=' . $domain_name->domain_name_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $domain_name->name . '</strong>'));

            redirect('domain-names');

        }

        redirect('domain-names');
    }
}
