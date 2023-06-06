<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

use Altum\Title;

class Project extends Controller {

    public function index() {

        \Altum\Authentication::guard();

	$project_id = isset($this->params[0]) ? (int) $this->params[0] : null;

	if(!$project = db()->where('project_id', $project_id)->where('user_id', $this->user->user_id)->getOne('projects')) {
            redirect('projects');
        }

	$project->settings = json_decode($project->settings);
	$projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        /* Get the monitors */
        $monitors = [];
        $monitors_result = database()->query("
            SELECT
                *
            FROM
                `monitors`
            WHERE
                `user_id` = {$this->user->user_id}
	    AND
	        `project_id` = {$project->project_id}
            LIMIT
                5
        ");
        while($row = $monitors_result->fetch_object()) {
            $monitors[] = $row;
        }
	$monitors_total = database()->query("SELECT COUNT(*) AS `total` FROM `monitors` WHERE `project_id` = {$project->project_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        /* Get the heartbeats */
        $heartbeats = [];
        $heartbeats_result = database()->query("
            SELECT
                *
            FROM
                `heartbeats`
            WHERE
                `user_id` = {$this->user->user_id}
	    AND
	        `project_id` = {$project->project_id}
            LIMIT
                5
        ");
        while($row = $heartbeats_result->fetch_object()) {
            $heartbeats[] = $row;
        }
	$heartbeats_total = database()->query("SELECT COUNT(*) AS `total` FROM `heartbeats` WHERE `project_id` = {$project->project_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        /* Get the thresholds */
        $thresholds = [];
        $thresholds_result = database()->query("
            SELECT
                *
            FROM
                `thresholds`
            WHERE
                `user_id` = {$this->user->user_id}
	    AND
	        `project_id` = {$project->project_id}
            LIMIT
                5
        ");
        while($row = $thresholds_result->fetch_object()) {
            $thresholds[] = $row;
        }
	$thresholds_total = database()->query("SELECT COUNT(*) AS `total` FROM `thresholds` WHERE `project_id` = {$project->project_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        /* Get the status_pages */
        $status_pages = [];
        $status_pages_result = database()->query("
            SELECT
                *
            FROM
                `status_pages`
            WHERE
                `user_id` = {$this->user->user_id}
	    AND
	        `project_id` = {$project->project_id}
            LIMIT
                5
        ");
        while($row = $status_pages_result->fetch_object()) {

            /* Genereate the status page full URL base */
            $row->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($row, $this->user, $domains);

            $status_pages[] = $row;
        }
	$status_pages_total = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE `project_id` = {$project->project_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        /* Set a custom title */
	Title::set(sprintf(l('project.title'), $project->name));

        /* Prepare the View */
        $data = [
	    'project' => $project,
	    'projects' => $projects,
            'monitors' => $monitors,
            'monitors_total' => $monitors_total,
            'heartbeats' => $heartbeats,
            'heartbeats_total' => $heartbeats_total,
            'thresholds' => $thresholds,
            'thresholds_total' => $thresholds_total,
            'status_pages' => $status_pages,
            'status_pages_total' => $status_pages_total
        ];

        $view = new \Altum\View('project/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
