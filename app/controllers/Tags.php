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
use Altum\Helpers\TagFunctions;

class Tags extends Controller
{

    public function getTags()
    {
        echo json_encode(TagFunctions::get_tags($this->user->user_id, $_POST['object_type'], $_POST['object_id']));
    }

    public function deleteTags()
    {
        echo TagFunctions::delete_tag($this->user->user_id, $_POST['object_type'], $_POST['object_id'], $_POST['tag_name']);
    }

    public function saveTags()
    {
        echo TagFunctions::save_tag($this->user->user_id, $_POST['object_type'], $_POST['object_id'], $_POST['tag_name']);
    }

    public function searchTags()
    {
        echo json_encode(TagFunctions::search_tags($this->user->user_id, $_POST['object_type'], $_POST['object_id'], $_POST['search_string']));
    }

}
