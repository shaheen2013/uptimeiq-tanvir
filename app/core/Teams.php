<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum;

use Altum\Models\User;

class Teams
{
    public static $team = null;
    public static $team_member = null;
    public static $team_user = null;

    public static function initialize()
    {
        if (isset($_SESSION['team_id']) && \Altum\Plugin::is_active('teams')) {
            /* Get requested team */
            self::$team = (new \Altum\Models\Teams())->get_team_by_team_id($_SESSION['team_id']);

            if (self::$team) {
                /* Get team member */
                self::$team_member = (new \Altum\Models\TeamsMembers())->get_team_member_by_team_id_and_user_id(self::$team->team_id, \Altum\Authentication::$user_id);

                if (self::$team_member) {
                    self::$team_member->access = json_decode(self::$team_member->access);
                }
            }
        }
    }

    public static function delegate_access()
    {
        if (!self::$team || !self::$team_member) {
            return false;
        }

        /* Get team owner user */
        self::$team_user = (new User())->get_user_by_user_id(self::$team->user_id);

        return self::$team_user;
    }

    public static function is_delegated()
    {
        return self::$team && self::$team_member;
    }

    public static function get_main_user($user_id = null)
    {
        $user_id = $user_id ?? isset($_SESSION["user_id"]) ?  $_SESSION["user_id"] : null;
        /* Get data from the database */
        $data = db()->where('user_id', $user_id)->getOne('users');

        if ($data) {

            /* Parse the users plan settings */
            $data->plan_settings = json_decode($data->plan_settings);

            /* Parse billing details if existing */
            $data->billing = json_decode($data->billing ?? '');

            // set logged in team
            $data->team_id = $_SESSION["team_id"] ?? null;
        }
        return $data;
    }

    public static function has_access($access_level = null)
    {
        if (!self::$team || !self::$team_member) {
            /* Return true as there is no team or team member set */
            return true;
        }

        return self::$team_member->access->{$access_level};
    }

    public static function invitation($user_id)
    {
        $invitation = db()->where('user_id', $user_id)->where('status', 0)->get('teams_members', null, 'team_member_id');

        return count($invitation);
    }

    public static function team_joined_count($user_id)
    {
        $team_joined = db()->where('user_id', $user_id)->where('status', 1)->get('teams_members', null, 'team_member_id');

        return count($team_joined);
    }
    public static function team_joined($user_id)
    {
        $team_joined = db()->where('user_id', $user_id)->where('status', 1)->get('teams_members', null, ['team_member_id', 'team_id']);

        foreach ($team_joined as $key => $value) {
            $team = db()->where('team_id', $value->team_id)->get('teams', null, 'name');
            $team_joined[$key]->name = $team[0]->name;
        }
        return $team_joined;
    }

    public static function is_same_team_member($current_user_id, $user_id)
    {
        $team_id = db()->where('user_id', $current_user_id)->get('teams_members', null, ['team_id']);

        $team_mate = false;
        foreach ($team_id as $key => $val) {
            $team = db()->where('user_id', $user_id)->where('team_id', $val->team_id)->get('teams_members', null, ['team_id']);
            if ($team) {
                $team_mate = true;
            }
        }

        return $team_mate;
    }

    public static function team_creator($user_id, $team_id)
    {
        $team_creator = db()->where('user_id', $user_id)->where('team_id', $team_id)->get('teams', null, ['team_id']);
        if ($team_creator) {
            return true;
        } else {
            return false;
        }
    }

    public static function is_admin_check($user_id)
    {
        $user = db()->where('user_id', $user_id)->getOne('users', ['type']);
        if ($user->type==1) {
            return true;
        } else {
            return false;
        }
    }
}
