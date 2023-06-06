<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Title;
use Altum\Helpers\Tags;

class MonitorTags extends Controller
{

    public function getTags()
    {
        echo json_encode(Tags::get_tags($this->user->user_id, 'monitor_id', $_POST['id']));
    }

    public function deleteTags()
    {
        echo Tags::delete_tag($this->user->user_id, 'monitor_id', $_POST['id'], $_POST['tag_name']);
    }

    public function saveTags()
    {
        echo Tags::save_tag($this->user->user_id, 'monitor_id', $_POST['id'], $_POST['tag_name']);
    }

    public function searchTags()
    {
        echo json_encode(Tags::search_tags($this->user->user_id, 'monitor_id', $_POST['id'], $_POST['input_val']));
    }

}
