<?php
/*
 * @copyright Copyright (c) 2023 UptimeIQ (https://uptimeiq.com/)
 *
 * This software is exclusively sold through https://uptimeiq.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://uptimeiq.com/.
 */

namespace Altum\Controllers;

class Sitemap extends Controller {

    public function index() {

        /* Set the header as xml so the browser can read it properly */
        header('Content-Type: text/xml');

        /* How many external users per sitemap page */
        $pagination = 5000;

        $page = isset($this->params[0]) ? $this->params[0] : null;

        /* Different answers for different parts */
        switch($page) {

            /* Sitemap index */
            case null:

                /* Get the total amount of status_pages */
                $total_status_pages = database()->query("
                    SELECT 
                        COUNT(`status_pages`.`status_page_id`) AS `total` 
                    FROM 
                        `status_pages`
                    LEFT JOIN
                        `users` ON `status_pages`.`user_id` = `users`.`user_id`
                    WHERE
                        `users`.`status` = 1
                        AND `status_pages`.`is_enabled` = 1
                        AND `status_pages`.`is_se_visible` = 1
                  ")->fetch_object()->total ?? 0;

                /* Calculate the needed sitemaps */
                $total_sitemaps = 1 + ceil((int) $total_status_pages / $pagination);

                /* Main View */
                $data = [
                    'total_sitemaps' => $total_sitemaps
                ];

                $view = new \Altum\View('sitemap/sitemap_index', (array) $this);

                break;

            /* Output base pages like the homepage, register..etc*/
            case 1:

                /* Get all pages & categories */
                $pages = db()->where('type', 'internal')->where('is_published', 1)->get('pages', null, ['url', 'language']);
                $pages_categories = db()->get('pages_categories', null, ['url', 'language']);

                if(settings()->main->blog_is_enabled) {
                    $blog_posts = db()->where('is_published', 1)->get('blog_posts', null, ['url', 'language']);
                    $blog_posts_categories = db()->get('blog_posts_categories', null, ['url', 'language']);
                }

                /* Main View */
                $data = [
                    'pages' => $pages,
                    'pages_categories' => $pages_categories,
                    'blog_posts' => $blog_posts ?? null,
                    'blog_posts_categories' => $blog_posts_categories ?? null,
                ];

                $view = new \Altum\View('sitemap/sitemap_1', (array) $this);

                break;

            /* Output only indexed external users */
            default:

                $limit_start = ($page - 2) * $pagination;

                /* Get the external users list */
                $status_pages_result = database()->query("
                    SELECT
                        `status_pages`.`url`,
                        `status_pages`.`datetime`
                    FROM 
                        `status_pages`
                    LEFT JOIN
                        `users` ON `status_pages`.`user_id` = `users`.`user_id`
                    WHERE
                        `users`.`status` = 1
                        AND `status_pages`.`is_enabled` = 1
                        AND `status_pages`.`is_se_visible` = 1
                        AND `status_pages`.`domain_id` IS NULL
                    LIMIT 
                        {$limit_start}, {$pagination}
                ");

                /* Main View */
                $data = [
                    'status_pages_result' => $status_pages_result
                ];

                $view = new \Altum\View('sitemap/sitemap_x', (array) $this);

                break;

        }


        echo $view->run($data);

        die();
    }

}
