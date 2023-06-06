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

class ProjectCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.projects')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('projects');
        }

        $main_user=\Altum\Teams::get_main_user();

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `projects` WHERE `user_id` = {$main_user->user_id}")->fetch_object()->total ?? 0;

        if($main_user->plan_settings->projects_limit != -1 && $total_rows >= $main_user->plan_settings->projects_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('projects');
        }

        if(!empty($_POST)) {
            $_POST['name'] = query_clean($_POST['name']);
            $_POST['color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['color']) ? '#000' : $_POST['color'];

            //ALTUMCODE:DEMO if(DEMO) if($main_user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'color'];
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
                db()->insert('projects', [
                    'user_id' => $main_user->user_id,
                    'name' => $_POST['name'],
                    'color' => $_POST['color'],
                    'datetime' => \Altum\Date::$date,
                    'team_id' => $main_user->team_id,
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItem('projects?user_id=' . $main_user->user_id);

                redirect('projects');
            }
        }

        $values = [
            'name' => $_POST['name'] ?? '',
            'color' => $_POST['color'] ?? '#000000',
        ];

        /* Prepare the View */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('project-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
