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
use Altum\Meta;
use Altum\Title;
use MaxMind\Db\Reader;

class Tools extends Controller {

    public function index() {

        if(!settings()->tools->is_enabled) {
            redirect();
        }

        if(settings()->tools->access == 'users') {
            \Altum\Authentication::guard();
        }

        $tools = require APP_PATH . 'includes/tools.php';

        /* Prepare the View */
        $data = [
            'tools' => $tools,
        ];

        $view = new \Altum\View('tools/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    private function initiate() {
        if(!settings()->tools->is_enabled) {
            redirect();
        }

        if(settings()->tools->access == 'users') {
            \Altum\Authentication::guard();
        }

        if(!settings()->tools->available_tools->{\Altum\Router::$method}) {
            redirect('tools');
        }

        /* Meta & title */
        Title::set(sprintf(l('tools.tool_title'), l('tools.' . \Altum\Router::$method . '.name')));
        Meta::set_description(l('tools.' . \Altum\Router::$method . '.description'));
        Meta::set_keywords(l('tools.' . \Altum\Router::$method . '.meta_keywords'));
    }

    public function dns_lookup() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['host'] = trim(query_clean($_POST['host']));

            if(filter_var($_POST['host'], FILTER_VALIDATE_URL)) {
                $_POST['host'] = parse_url($_POST['host'], PHP_URL_HOST);
            }

            /* Check for any errors */
            $required_fields = ['host'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $data['result'] = [];

            foreach([DNS_A, DNS_AAAA, DNS_CNAME, DNS_MX, DNS_NS, DNS_TXT, DNS_SOA, DNS_CAA] as $dns_type) {
                $dns_records = @dns_get_record($_POST['host'], $dns_type);

                if($dns_records) {
                    foreach($dns_records as $dns_record) {
                        if(!isset($data['result'][$dns_record['type']])) {
                            $data['result'][$dns_record['type']] = [$dns_record];
                        } else {
                            $data['result'][$dns_record['type']][] = $dns_record;
                        }
                    }
                }
            }

            if(empty($data['result'])) {
                Alerts::add_field_error('host', l('tools.dns_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                // :)
            }
        }

        $values = [
            'host' => $_POST['host'] ?? '',
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/dns_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function ip_lookup() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['ip'] = trim(query_clean($_POST['ip']));

            /* Check for any errors */
            $required_fields = ['ip'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!filter_var($_POST['ip'], FILTER_VALIDATE_IP)) {
                Alerts::add_field_error('ip', l('tools.ip_lookup.error_message'));
            }

            try {
                $maxmind = (new Reader(APP_PATH . 'includes/GeoLite2-City.mmdb'))->get($_POST['ip']);
            } catch(\Exception $exception) {
                Alerts::add_field_error('ip', l('tools.ip_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $data['result'] = $maxmind;

            }
        }

        $values = [
            'ip' => $_POST['ip'] ?? get_ip(),
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/ip_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function ssl_lookup() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['host'] = trim(query_clean($_POST['host']));

            if(filter_var($_POST['host'], FILTER_VALIDATE_URL)) {
                $_POST['host'] = parse_url($_POST['host'], PHP_URL_HOST);
            }

            /* Check for any errors */
            $required_fields = ['host'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Check for an SSL certificate */
            $certificate = get_website_certificate('https://' . $_POST['host']);

            if(!$certificate) {
                Alerts::add_field_error('host', l('tools.ssl_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Create the new SSL object */
                $ssl = [
                    'organization' => $certificate['issuer']['O'],
                    'country' => $certificate['issuer']['C'],
                    'common_name' => $certificate['issuer']['CN'],
                    'start_datetime' => (new \DateTime())->setTimestamp($certificate['validFrom_time_t'])->format('Y-m-d H:i:s'),
                    'end_datetime' => (new \DateTime())->setTimestamp($certificate['validTo_time_t'])->format('Y-m-d H:i:s'),
                ];

                $data['result'] = $ssl;

            }
        }

        $values = [
            'host' => $_POST['host'] ?? '',
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/ssl_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function whois_lookup() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['domain_name'] = trim(query_clean($_POST['domain_name']));

            if(filter_var($_POST['domain_name'], FILTER_VALIDATE_URL)) {
                $_POST['domain_name'] = parse_url($_POST['domain_name'], PHP_URL_HOST);
            }

            /* Check for any errors */
            $required_fields = ['domain_name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            try {
                $get_whois = \Iodev\Whois\Factory::get()->createWhois();
                $whois_info = $get_whois->loadDomainInfo($_POST['domain_name']);
            } catch (\Exception $e) {
                Alerts::add_field_error('domain_name', l('tools.whois_lookup.error_message'));
            }

            $whois = isset($whois_info) && $whois_info ? [
                'start_datetime' => $whois_info->creationDate ? (new \DateTime())->setTimestamp($whois_info->creationDate)->format('Y-m-d H:i:s') : null,
                'updated_datetime' => $whois_info->updatedDate ? (new \DateTime())->setTimestamp($whois_info->updatedDate)->format('Y-m-d H:i:s') : null,
                'end_datetime' => $whois_info->expirationDate ? (new \DateTime())->setTimestamp($whois_info->expirationDate)->format('Y-m-d H:i:s') : null,
                'registrar' => $whois_info->registrar,
                'nameservers' => $whois_info->nameServers,
            ] : [];

            if(empty($whois)) {
                Alerts::add_field_error('domain_name', l('tools.whois_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $data['result'] = $whois;

            }
        }

        $values = [
            'domain_name' => $_POST['domain_name'] ?? '',
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/whois_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function ping() {
        $this->initiate();

        $data = [];

        /* Get available ping servers */
        $ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();
        $data['ping_servers'] = $ping_servers;

        if(!empty($_POST)) {
            $_POST['type'] = in_array($_POST['type'], ['website', 'ping', 'port']) ? query_clean($_POST['type']) : 'website';
            $_POST['target'] = query_clean($_POST['target']);
            $_POST['port'] = isset($_POST['port']) ? (int) $_POST['port'] : 0;
            $_POST['ping_server_id'] = array_key_exists($_POST['ping_server_id'], $ping_servers) ? (int) $_POST['ping_server_id'] : reset($ping_servers)->ping_server_id;

            /* Check for any errors */
            $required_fields = ['target'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $target = (new \StdClass());
                $target->type = $_POST['type'];
                $target->target = $_POST['target'];
                $target->port = $_POST['port'] ?? 0;
                $target->ping_servers_ids = [$_POST['ping_server_id']];
                $target->settings = (new \StdClass());
                $target->settings->timeout_seconds = 5;
                $target->settings->request_method = 'get';
                $target->settings->request_basic_auth_username = '';
                $target->settings->request_basic_auth_password = '';

                /* Do the check */
                $check = \Altum\Monitor::check($target, $ping_servers);

                $data['result'] = $check;
                $data['ping_servers'] = $ping_servers;
            }
        }

        $values = [
            'type' => $_POST['type'] ?? '',
            'target' => $_POST['target'] ?? '',
            'port' => $_POST['port'] ?? '',
            'ping_server_id' => $_POST['ping_server_id'] ?? reset($ping_servers)->ping_server_id,
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/ping', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function meta_tags_checker() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['url'] = trim(filter_var($_POST['url'], FILTER_SANITIZE_URL));

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }


            /* Get the URL source */
            try {
                $response = \Unirest\Request::get($_POST['url']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.meta_tags_checker.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $doc = new \DOMDocument();
                @$doc->loadHTML($response->raw_body);

                $meta_tags_array = $doc->getElementsByTagName('meta');
                $meta_tags = [];

                for($i = 0; $i < $meta_tags_array->length; $i++) {
                    $meta_tag = $meta_tags_array->item($i);

                    $meta_tag_key = !empty($meta_tag->getAttribute('name')) ? $meta_tag->getAttribute('name') : $meta_tag->getAttribute('property');

                    if($meta_tag_key) {
                        $meta_tags[$meta_tag_key] = $meta_tag->getAttribute('content');
                    }
                }

                $data['result'] = $meta_tags;
            }
        }

        $values = [
            'url' => $_POST['url'] ?? '',
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/meta_tags_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function website_hosting_checker() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['host'] = trim(query_clean($_POST['host']));

            if(filter_var($_POST['host'], FILTER_VALIDATE_URL)) {
                $_POST['host'] = parse_url($_POST['host'], PHP_URL_HOST);
            }

            /* Check for any errors */
            $required_fields = ['host'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Get ip of host */
            $host_ip = gethostbyname($_POST['host']);

            /* Check via ip-api */
            $response = \Unirest\Request::get('http://ip-api.com/json/' . $host_ip);

            if($response->body->status == 'fail') {
                Alerts::add_field_error('host', l('tools.website_hosting_checker.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $data['result'] = $response->body;

            }
        }

        $values = [
            'host' => $_POST['host'] ?? '',
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/website_hosting_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function http_headers_lookup() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['url'] = filter_var($_POST['url'], FILTER_SANITIZE_URL);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            try {
                $response = \Unirest\Request::get($_POST['url']);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.http_headers_lookup.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $data['result'] = $response->headers;

            }
        }

        $values = [
            'url' => $_POST['url'] ?? null,
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/http_headers_lookup', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function google_cache_checker() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['url'] = filter_var($_POST['url'], FILTER_SANITIZE_URL);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Get the URL source */
            $url = 'http://webcache.googleusercontent.com/search?hl=en&q=cache:' . urlencode($_POST['url']) . '&strip=0&vwsrc=1';
            try {
                $response = \Unirest\Request::get($url, [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0'
                ]);
            } catch (\Exception $exception) {
                Alerts::add_field_error('url', l('tools.google_cache_checker.error_message'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                /* Get details from the google query result */
                preg_match('/It is a snapshot of the page as it appeared on ([^\.]+)\./i', $response->raw_body, $matches);

                $data['result'] = empty($matches) ? false : $matches[1];
            }
        }

        $values = [
            'url' => $_POST['url'] ?? null,
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/google_cache_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function url_redirect_checker() {
        $this->initiate();

        $data = [];

        if(!empty($_POST)) {
            $_POST['url'] = filter_var($_POST['url'], FILTER_SANITIZE_URL);

            /* Check for any errors */
            $required_fields = ['url'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Save locations of each request */
            $locations = [];

            /* Get the URL source */
            $i = 1;
            $url = $_POST['url'];

            /* Start the requests process */
            do {
                try {
                    \Unirest\Request::curlOpt(CURLOPT_FOLLOWLOCATION, 0);
                    $response = \Unirest\Request::get($url, [
                        'User-Agent' => settings()->main->title . ' ' . url('tools/url_redirect_checker') . '/1.0'
                    ]);

                    $locations[] = [
                        'url' => $url,
                        'status_code' => $response->code,
                        'redirect_to' => $response->headers['Location'] ?? $response->headers['location'] ?? null,
                    ];

                    $i++;
                    $url = $response->headers['Location'] ?? $response->headers['location'] ?? null;
                } catch (\Exception $exception) {
                    Alerts::add_field_error('url', l('tools.url_redirect_checker.error_message'));
                    break;
                }
            } while($i <= 10 && ($response->code == 301 || $response->code == 302));

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $data['result'] = $locations;
            }
        }

        $values = [
            'url' => $_POST['url'] ?? null,
        ];

        /* Prepare the View */
        $data['values'] = $values;

        $view = new \Altum\View('tools/url_redirect_checker', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

//    public function safe_url_checker() {
//        $this->initiate();
//
//        $data = [];
//
//        if(!empty($_POST)) {
//            $_POST['url'] = filter_var($_POST['url'], FILTER_SANITIZE_URL);
//
//            /* Check for any errors */
//            $required_fields = ['url'];
//            foreach($required_fields as $field) {
//                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
//                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
//                }
//            }
//
//            if(!\Altum\Csrf::check()) {
//                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
//            }
//
//            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
//                $data['result'] = !google_safe_browsing_check($_POST['url'], settings()->links->google_safe_browsing_api_key);
//            }
//        }
//
//        $values = [
//            'url' => $_POST['url'] ?? null,
//        ];
//
//        /* Prepare the View */
//        $data['values'] = $values;
//
//        $view = new \Altum\View('tools/safe_url_checker', (array) $this);
//
//        $this->add_view_content('content', $view->run($data));
//
//    }

}
