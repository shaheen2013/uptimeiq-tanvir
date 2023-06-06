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
use Altum\Date;

class TeamsMembers extends Controller
{

    public function delete()
    {

        \Altum\Authentication::guard();

        if (!\Altum\Plugin::is_active('teams')) {
            redirect('dashboard');
        }

        if (empty($_POST)) {
            redirect('teams');
        }
        $main_user_id = \Altum\Teams::get_main_user()->user_id;
        $main_user_email = \Altum\Teams::get_main_user()->email;

        $team_member_id = (int) $_POST['team_member_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if (!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('teams');
        }

        if (!$team_member = db()->where('team_member_id', $team_member_id)->getOne('teams_members')) {
            redirect('teams');
        }

        if (!$team = db()->where('team_id', $team_member->team_id)->getOne('teams')) {
            redirect('teams');
        }

        /* Detect if it's a team owner deletion or team member deletion */
        $team_member_deletion_as = null;

        if ($team->user_id == $main_user_id) {
            $team_member_deletion_as = 'owner';
        } elseif ($team_member->user_id == $main_user_id || $team_member->user_email == $main_user_email) {
            $team_member_deletion_as = 'member';
        } else {
            redirect('teams');
        }


        if (!Alerts::has_field_errors() && !Alerts::has_errors()) {

            $uri = explode("/", $_POST['uri']);

            /* Delete the team member */
            db()->where('team_member_id', $team_member->team_member_id)->delete('teams_members');

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItem('team_member?team_id=' . $team_member->team_id . '&user_id=' . $team_member->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $team_member->user_email . '</strong>'));

            if ($team_member_deletion_as == 'owner') {
                redirect('team/' . $team_member->team_id);
            } else {
                if ($uri[2] == "sub-admin") {
                    redirect('sub-admin/team-invitation');
                } elseif ($uri[2] == "admin") {
                    redirect('admin/team-invitation');
                }
                redirect('teams');
            }
        }

        redirect('teams');
    }

    public function join()
    {

        \Altum\Authentication::guard();

        if (!\Altum\Plugin::is_active('teams')) {
            redirect('dashboard');
        }

        if (empty($_POST)) {
            redirect('teams');
        }

        $main_user_id = \Altum\Teams::get_main_user()->user_id;
        $main_user_email = \Altum\Teams::get_main_user()->email;

        $team_member_id = (int) $_POST['team_member_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if (!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('teams');
        }

        if (!$team_member = db()->where('team_member_id', $team_member_id)->where('user_email', $main_user_email)->where('status', 0)->getOne('teams_members')) {
            redirect('teams');
        }

        $uri = explode("/", $_POST['uri']);

        if (!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Update the team member */
            db()->where('team_member_id', $team_member->team_member_id)->update('teams_members', [
                'user_id' => $main_user_id,
                'status' => 1,
                'last_datetime' => Date::$date,
            ]);

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItem('team_member?team_id=' . $team_member->team_id . '&user_id=' . $team_member->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.update2'));

            if ($uri[2] == "sub-admin") {
                redirect('sub-admin/team-invitation');
            } elseif ($uri[2] == "admin") {
                redirect('admin/team-invitation');
            }
            redirect('teams');
        }

        if ($uri[2] == "sub-admin") {
            redirect('sub-admin/team-invitation');
        } elseif ($uri[2] == "admin") {
            redirect('admin/team-invitation');
        }
        redirect('teams');
    }

    public function login()
    {

        \Altum\Authentication::guard();

        if (isset($_SESSION['team_id'])) {
            unset($_SESSION['team_id']);
        }

        if (!\Altum\Plugin::is_active('teams')) {
            redirect('dashboard');
        }

        if (empty($_POST) && empty($_GET)) {
            redirect('teams');
        }
        if (isset($_GET['team_member_id'])) {
            $team_member_id = (int) $_GET['team_member_id'];
        } else {
            $team_member_id = (int) $_POST['team_member_id'];
        }
        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if (!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('teams');
        }
        $main_user_id = \Altum\Teams::get_main_user()->user_id;
        if (!$team_member = db()->where('team_member_id', $team_member_id)->where('user_id', $main_user_id)->where('status', 1)->getOne('teams_members')) {
            redirect('dashboard');
        }


        if (!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Login the team member */
            $_SESSION['team_id'] = $team_member->team_id;

            /* Clear the cache */
            \Altum\Cache::$adapter->deleteItem('team_member?team_id=' . $team_member->team_id . '&user_id=' . $team_member->user_id);

            redirect('dashboard');
        }

        redirect('teams');
    }
}
