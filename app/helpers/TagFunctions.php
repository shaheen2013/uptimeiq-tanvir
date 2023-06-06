<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Helpers;

use Altum\Response;

class TagFunctions {
    private static function parse_tag_type($tag_type) {
        if ($tag_type === 'heartbeat' || $tag_type === 'threshold' || $tag_type === 'monitor') {
            return $tag_type . "_id";
        } else {
            return false;
        }
    }

    public static function get_tags($user_id, $tag_type, $object_id) {
        $tag_type_clean = self::parse_tag_type($tag_type);
        if ($tag_type_clean == false) {
            return;
        }

        $current_tags = db()->where($tag_type_clean, $object_id)->where('user_id', $user_id)->get('tags', null, ['name']);
        $current_tags_arr = [];
        foreach ($current_tags as $key => $value) {
            $current_tags_arr[] = $value->name;
        }

        $all_tags = db()->where('user_id', $user_id)->get('tags', null, ['name']);
        $all_tag_arr = [];
        foreach ($all_tags as $key => $value) {
            $all_tag_arr[] = $value->name;
        }
 
        $all_tag_unique = array_unique($all_tag_arr);

        $unused_tags = array_diff($all_tag_unique, $current_tags_arr);
        $all_tag_count = array_count_values($unused_tags);
        arsort($all_tag_count);
        $top_tag = array_slice(array_keys($all_tag_count), 0, 3, true);
 
        return [$top_tag, $current_tags_arr];
    }

    // WHERE nesting only goes two deep and I don't want to rewrite it now, so breaking it into two steps.
    // This has the added advantage of not sending the string to the DB.
    public static function delete_tag($user_id, $tag_type, $object_id, $tag_name) {
        $tag_type_clean = self::parse_tag_type($tag_type);
        if ($tag_type_clean == false) {
            return;
        }

        $tag_list = db()->where($tag_type_clean, $object_id)->where('user_id', $user_id)->get('tags', null, ['tag_id', 'name']);
	foreach ($tag_list as $key => $value) {
            if ($value->name === $tag_name) {
                return db()->where('tag_id', $value->tag_id)->delete('tags');
            }
        }
	return "";
    }

    public static function save_tag($user_id, $tag_type, $object_id, $tag_name) {
        $tag_type_clean = self::parse_tag_type($tag_type);
        if ($tag_type_clean == false) {
            return;
        }

        $tag_list = db()->where($tag_type_clean, $object_id)->where('user_id', $user_id)->get('tags', null, ['tag_id', 'name']);
	foreach ($tag_list as $key => $value) {
            if ($value->name === $tag_name) {
		// Already exists
                return "";
            }
        }
	$response = db()->insert('tags', ['user_id' => $user_id, 'name' => $tag_name, $tag_type_clean => $object_id]);
        if ($response > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function search_tags($user_id, $tag_type, $object_id, $search_string) {
        $tag_type_clean = self::parse_tag_type($tag_type);
        if ($tag_type_clean == false) {
            return;
        }
        $current_tags = self::get_tags($user_id, $tag_type, $object_id)[1];

        $all_tags = db()->where('user_id', $user_id)->get('tags', null, ['name']);
        
        $all_tag_arr = [];
        foreach ($all_tags as $key => $value) {
            array_push($all_tag_arr,$value->name);
        }
        $unused_tags = array_slice(array_values(array_unique(preg_grep("/^$search_string/i", $all_tag_arr))), 0, 5, true);
    
        $response=[$current_tags,$unused_tags]; 
        return $response;
    }
}

