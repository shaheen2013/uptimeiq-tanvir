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
use Altum\Models\Plan;
use Altum\Models\User;

class SubAdminUsers extends Controller {

    public function index() {

        if (\Altum\Teams::get_main_user()->type != 2) {
            redirect('dashboard');
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['status', 'source', 'plan_id', 'country', 'type', 'referred_by'], ['name', 'email'], ['email', 'datetime', 'last_activity', 'name', 'total_logins', 'plan_expiration_date']));
        $filters->set_default_order_by('user_id', settings()->main->default_order_type);
        $filters->set_default_results_per_page(settings()->main->default_results_per_page);

        /* Prepare the paginator for sub admin (team members only) */
        $team_id = db()->where('user_id', \Altum\Teams::get_main_user()->user_id)->get('teams_members', null, ['team_id']);
        $users_id = [];
        foreach ($team_id as $key => $value) {
            $team_users = db()->where('team_id', $value->team_id)->get('teams_members', null, ['user_id']);
            foreach ($team_users as $k => $val) {
                array_push($users_id, $val->user_id);
            }   
        }
        $users_id=implode(',', array_unique($users_id));
            $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `users` WHERE 1 = 1 AND `user_id` in ($users_id) {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('sub-admin/users?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $users = [];
        $users_result = database()->query("
            SELECT
                *
            FROM
                `users`
            WHERE
                1 = 1
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                
            {$paginator->get_sql_limit()}
        ");
        while($row = $users_result->fetch_object()) {
            $users[] = $row;
        }

        /* Export handler */
        process_export_json($users, 'include', ['user_id', 'email', 'name', 'billing', 'plan_id', 'plan_settings', 'plan_expiration_date', 'plan_trial_done', 'status', 'source', 'language', 'timezone', 'country', 'datetime', 'last_activity', 'total_logins']);
        process_export_csv($users, 'include', ['user_id', 'email', 'name', 'plan_id', 'plan_expiration_date', 'plan_trial_done', 'status', 'source', 'language', 'timezone', 'country', 'datetime', 'last_activity', 'total_logins']);

        /* Requested plan details */
        $plans = [];
        $plans['free'] = (new Plan())->get_plan_by_id('free');
        $plans['custom'] = (new Plan())->get_plan_by_id('custom');
        $plans_result = database()->query("SELECT `plan_id`, `name` FROM `plans`");
        while($row = $plans_result->fetch_object()) {
            $plans[$row->plan_id] = $row;
        }

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Main View */
        $data = [
            'users' => $users,
            'plans' => $plans,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'filters' => $filters
        ];

        $view = new \Altum\View('sub-admin/users/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function login() {

        $user_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('sub-admin/users');
        }

        if($user_id == \Altum\Teams::get_main_user()->user_id) {
            redirect('sub-admin/users');
        }

        /* Check if resource exists */
        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('sub-admin/users');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Logout of the admin */
            \Altum\Authentication::logout(false);

            /* Login as the new user */
            session_start();
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['user_password_hash'] = md5($user->password);

            /* Tell the script that we're actually logged in as an admin in the background */
            $_SESSION['sub_admin_user_id'] = \Altum\Teams::get_main_user()->user_id;

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('admin_user_login_modal.success_message'), $user->name));

            redirect('dashboard');

        }

        redirect('sub-admin/users');
    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('sub-admin/users');
        }

        if(empty($_POST['selected'])) {
            redirect('sub-admin/users');
        }

        if(!isset($_POST['type']) || (isset($_POST['type']) && !in_array($_POST['type'], ['delete']))) {
            redirect('sub-admin/users');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $user_id) {
                        /* Do not allow self-deletion */
                        if($user_id == \Altum\Teams::get_main_user()->user_id) {
                            continue;
                        }

                        (new User())->delete((int) $user_id);
                    }
                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('admin_bulk_delete_modal.success_message'));

        }

        redirect('sub-admin/users');
    }

    public function delete() {

        $user_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        /* Do not allow self-deletion */
        if($user_id == \Altum\Teams::get_main_user()->user_id) {
            Alerts::add_error(l('admin_users.error_message.self_delete'));
        }
        // can't delete site admin
        $user_data = db()->where('user_id', $user_id)->getOne('users');
        if($user_data->type == 1) {
            Alerts::add_error('You can not delete site-admin!');
        }

        if(!$user = db()->where('user_id', $user_id)->getOne('users')) {
            redirect('sub-admin/users');
        }
        
        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete the user */
            (new User())->delete($user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $user->name . '</strong>'));

        }

        redirect('sub-admin/users');
    }

    public function roleChange()
    {
        $user= db()->where('user_id', $_POST['user_id'])->getOne('users', 'type');
        if ($_POST['role_id']!=1 && $user->type !=1) {
            $response=db()->where('user_id', $_POST['user_id'])->update('users', [
                'type' => $_POST['role_id']
            ]);
        }else {
            $response=false;
        }
        
        echo json_encode($response);
        exit;
    }

}
