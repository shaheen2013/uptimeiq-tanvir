<?php defined('ALTUMCODE') || die() ?>

<?php
$ping_servers = (new \Altum\Models\PingServers())->get_ping_servers();
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fa fa-fw fa-angle-right"></i></li>
            <li><a href="<?= url('api-documentation') ?>"><?= l('api_documentation.breadcrumb') ?></a> <i class="fa fa-fw fa-angle-right"></i></li>
            <li class="active" aria-current="page"><?= l('api_documentation.monitors.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 mb-4"><?= l('api_documentation.monitors.header') ?></h1>

    <div class="accordion">
        <div class="card">
            <div class="card-header bg-gray-50 p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link" data-toggle="collapse" data-target="#monitors_read_all" aria-expanded="true" aria-controls="monitors_read_all">
                        <?= l('api_documentation.read_all') ?>
                    </a>
                </h3>
            </div>

            <div id="monitors_read_all" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/monitors/</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/monitors/' \<br />
                                --header 'Authorization: Bearer <span class="text-primary">{api_key}</span>' \
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container mb-4">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('api_documentation.parameters') ?></th>
                                <th><?= l('api_documentation.details') ?></th>
                                <th><?= l('api_documentation.description') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>page</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= l('api_documentation.filters.page') ?></td>
                            </tr>
                            <tr>
                                <td>results_per_page</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.filters.results_per_page'), '<code>' . implode('</code> , <code>', [10, 25, 50, 100, 250, 500]) . '</code>', 25) ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <div class="card bg-gray-100 border-0">
                                        <pre class="card-body">
{
    "data": [
        {
            "id": 1,
            "project_id": 0,
            "name": "Example",
            "type": "website",
            "target": "https://example.com/",
            "port": 0,
            "settings": {
                "check_interval_seconds": 3600,
                "timeout_seconds": 1,
                "request_method": "GET",
                "request_body": "",
                "request_basic_auth_username": "",
                "request_basic_auth_password": "",
                "request_headers": [],
                "response_status_code": 200,
                "response_body": "",
                "response_headers": []
            },
            "ping_servers_ids": [1],
            "is_ok": 1,
            "uptime": 95.5,
            "downtime": 4.5,
            "average_response_time": 500,
            "total_checks": 500,
            "total_ok_checks": 450,
            "total_not_ok_checks": 50,
            "last_check_datetime": "2021-03-25 08:27:07",
            "notifications": {
                "email_is_enabled": 0,
                "webhook":"",
                "slack":"",
                "twilio":""
            },
            "is_enabled": false,
            "datetime": "2021-02-12 21:54:29"
        }
    ],
    "meta": {
        "page": 1,
        "results_per_page": 25,
        "total": 1,
        "total_pages": 1
    },
    "links": {
        "first": "<?= SITE_URL ?>api/monitors?&page=1",
        "last": "<?= SITE_URL ?>api/monitors?&page=1",
        "next": null,
        "prev": null,
        "self": "<?= SITE_URL ?>api/monitors?&page=1"
    }
}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-gray-50 p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link" data-toggle="collapse" data-target="#monitors_read" aria-expanded="true" aria-controls="monitors_read">
                        <?= l('api_documentation.read') ?>
                    </a>
                </h3>
            </div>

            <div id="monitors_read" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-success mr-3">GET</span> <span class="text-muted"><?= SITE_URL ?>api/monitors/</span><span class="text-primary">{monitor_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request GET \<br />
                                --url '<?= SITE_URL ?>api/monitors/<span class="text-primary">{monitor_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary">{api_key}</span>' \
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <div class="card bg-gray-100 border-0">
                                        <pre class="card-body">
{
    "data": {
        "id": 1,
        "project_id": 0,
        "name": "Example",
        "type": "website",
        "target": "https://example.com/",
        "port": 0,
        "settings": {
            "check_interval_seconds": 3600,
            "timeout_seconds": 1,
            "request_method": "GET",
            "request_body": "",
            "request_basic_auth_username": "",
            "request_basic_auth_password": "",
            "request_headers": [],
            "response_status_code": 200,
            "response_body": "",
            "response_headers": []
        },
        "ping_servers_ids": [1],
        "is_ok": 1,
        "uptime": 95.5,
        "downtime": 4.5,
        "average_response_time": 500,
        "total_checks": 500,
        "total_ok_checks": 450,
        "total_not_ok_checks": 50,
        "last_check_datetime": "2021-03-25 08:27:07",
        "notifications": {
            "email_is_enabled": 0,
            "webhook":"",
            "slack":"",
            "twilio":""
        },
        "is_enabled": false,
        "datetime": "2021-02-12 21:54:29"
    }
}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-gray-50 p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link" data-toggle="collapse" data-target="#projects_create" aria-expanded="true" aria-controls="projects_create">
                        <?= l('api_documentation.create') ?>
                    </a>
                </h3>
            </div>

            <div id="projects_create" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/monitors</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container mb-4">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('api_documentation.parameters') ?></th>
                                <th><?= l('api_documentation.details') ?></th>
                                <th><?= l('api_documentation.description') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>name</td>
                                <td>
                                    <span class="badge badge-danger"><?= l('api_documentation.required') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>target</td>
                                <td>
                                    <span class="badge badge-danger"><?= l('api_documentation.required') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>port</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>type</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['website', 'ping', 'port'])) ?></td>
                            </tr>
                            <tr>
                                <td>project_id</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>ping_servers_ids</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.array') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', array_keys($ping_servers)) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>check_interval_seconds</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', array_keys(require APP_PATH . 'includes/monitor_check_intervals.php')) . '</code>') ?> <?= '(' . l('global.date.seconds') . ')' ?></td>
                            </tr>
                            <tr>
                                <td>timeout_seconds</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', array_keys(require APP_PATH . 'includes/monitor_timeouts.php')) . '</code>') ?> <?= '(' . l('global.date.seconds') . ')' ?></td>
                            </tr>
                            <tr>
                                <td>request_method</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', ['HEAD', 'GET', 'POST', 'PUT', 'PATCH']) . '</code>') ?></td>
                            </tr>
                            <tr>
                                <td>request_body</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>request_basic_auth_username</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>request_basic_auth_password</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>request_header_name</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.array') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>request_header_value</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.array') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>response_status_code</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>response_body</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>response_header_name</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>response_header_value</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>is_ok_notifications</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.array') ?></span>
                                </td>
                                <td><?= l('api_documentation.monitors.is_ok_notifications') ?></td>
                            </tr>
                            <tr>
                                <td>email_reports_is_enabled</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>is_enabled</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request POST \<br />
                                --url '<?= SITE_URL ?>api/monitors' \<br />
                                --header 'Authorization: Bearer <span class="text-primary">{api_key}</span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">Example</span>' \<br />
                                --form 'target=<span class="text-primary">https://example.com/</span>' \<br />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <div class="card bg-gray-100 border-0">
                                <pre class="card-body">
{
    "data": {
        "id": 1
    }
}</pre>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-gray-50 p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link" data-toggle="collapse" data-target="#projects_update" aria-expanded="true" aria-controls="projects_update">
                        <?= l('api_documentation.update') ?>
                    </a>
                </h3>
            </div>

            <div id="projects_update" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-info mr-3">POST</span> <span class="text-muted"><?= SITE_URL ?>api/monitors/</span><span class="text-primary">{monitor_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-custom-container mb-4">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('api_documentation.parameters') ?></th>
                                <th><?= l('api_documentation.details') ?></th>
                                <th><?= l('api_documentation.description') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>name</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>target</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.string') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>project_id</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>whois_notifications</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.array') ?></span>
                                </td>
                                <td><?= l('api_documentation.domain_names.whois_notifications') ?></td>
                            </tr>
                            <tr>
                                <td>whois_notifications_timing</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', array_keys(require APP_PATH . 'includes/domain_name_timings.php')) . '</code>') ?> <?= '(' . l('global.date.days') . ')' ?></td>
                            </tr>
                            <tr>
                                <td>ssl_notifications</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.array') ?></span>
                                </td>
                                <td><?= l('api_documentation.domain_names.ssl_notifications') ?></td>
                            </tr>
                            <tr>
                                <td>ssl_notifications_timing</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.int') ?></span>
                                </td>
                                <td><?= sprintf(l('api_documentation.allowed_values'), '<code>' . implode('</code>, <code>', array_keys(require APP_PATH . 'includes/domain_name_timings.php')) . '</code>') ?> <?= '(' . l('global.date.days') . ')' ?></td>
                            </tr>
                            <tr>
                                <td>is_enabled</td>
                                <td>
                                    <span class="badge badge-info"><?= l('api_documentation.optional') ?></span>
                                    <span class="badge badge-secondary"><?= l('api_documentation.boolean') ?></span>
                                </td>
                                <td>-</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request POST \<br />
                                --url '<?= SITE_URL ?>api/monitors/<span class="text-primary">{monitor_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary">{api_key}</span>' \<br />
                                --header 'Content-Type: multipart/form-data' \<br />
                                --form 'name=<span class="text-primary">Example</span>' \<br />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.response') ?></label>
                        <div class="card bg-gray-100 border-0">
                                <pre class="card-body">
{
  "data": {
    "id": 1
  }
}</pre>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-gray-50 p-3 position-relative">
                <h3 class="h6 m-0">
                    <a href="#" class="stretched-link" data-toggle="collapse" data-target="#projects_delete" aria-expanded="true" aria-controls="projects_delete">
                        <?= l('api_documentation.delete') ?>
                    </a>
                </h3>
            </div>

            <div id="projects_delete" class="collapse">
                <div class="card-body">

                    <div class="form-group mb-4">
                        <label><?= l('api_documentation.endpoint') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                <span class="badge badge-danger mr-3">DELETE</span> <span class="text-muted"><?= SITE_URL ?>api/monitors/</span><span class="text-primary">{monitor_id}</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('api_documentation.example') ?></label>
                        <div class="card bg-gray-100 border-0">
                            <div class="card-body">
                                curl --request DELETE \<br />
                                --url '<?= SITE_URL ?>api/monitors/<span class="text-primary">{monitor_id}</span>' \<br />
                                --header 'Authorization: Bearer <span class="text-primary">{api_key}</span>' \<br />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
