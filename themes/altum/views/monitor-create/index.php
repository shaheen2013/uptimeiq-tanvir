<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('monitors') ?>"><?= l('monitors.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('monitor_create.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 text-truncate"><?= l('monitor_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fa fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('monitor.input.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                    <small class="form-text text-muted"><?= l('monitor.input.name_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="type"><i class="fa fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('monitor.input.type') ?></label>
                    <select id="type" name="type" class="form-control" required="required">
                        <option value="website" <?= $data->values['type'] == 'website' ? 'selected="selected"' : null ?>><?= l('monitor.input.type_website') ?></option>
                        <option value="ping" <?= $data->values['type'] == 'ping' ? 'selected="selected"' : null ?>><?= l('monitor.input.type_ping') ?></option>
                        <option value="port" <?= $data->values['type'] == 'port' ? 'selected="selected"' : null ?>><?= l('monitor.input.type_port') ?></option>
                    </select>
                    <small id="type_website_help" data-type="website" class="form-text text-muted"><?= l('monitor.input.type_website_help') ?></small id=type_help>
                    <small id="type_ping_help" data-type="ping" class="form-text text-muted"><?= l('monitor.input.type_ping_help') ?></small id=type_help>
                    <small id="type_port_help" data-type="port" class="form-text text-muted"><?= l('monitor.input.type_port_help') ?></small>
                </div>

                <div class="form-group" data-type="website">
                    <label for="target_website_url"><i class="fa fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('monitor.input.target_url') ?></label>
                    <input type="text" id="target_website_url" name="target" class="form-control <?= \Altum\Alerts::has_field_errors('target') ? 'is-invalid' : null ?>" value="<?= $data->values['target'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('target') ?>
                </div>

                <div class="form-group" data-type="ping">
                    <label for="target_ping_host"><i class="fa fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('monitor.input.target_host') ?></label>
                    <input type="text" id="target_ping_host" name="target" class="form-control" value="<?= $data->values['target'] ?>" required="required" />
                </div>

                <div class="row" data-type="port">
                    <div class="col-lg-3">
                        <div class="form-group" data-type="port">
                            <label for="target_port_host"><i class="fa fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('monitor.input.target_host') ?></label>
                            <input type="text" id="target_port_host" name="target" class="form-control" value="<?= $data->values['target'] ?>" required="required" />
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="form-group" data-type="port">
                            <label for="target_port_port"><i class="fa fa-fw fa-sm fa-dna text-muted mr-1"></i> <?= l('monitor.input.target_port') ?></label>
                            <input type="text" id="target_port_port" name="port" class="form-control" value="<?= $data->values['port'] ?>" required="required" />
                        </div>
                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fa fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('monitor.input.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div>

                        <div class="mb-3">
                            <div><i class="fa fa-fw fa-sm fa-map-marked-alt text-muted mr-1"></i><?= l('monitor.input.ping_servers_ids') ?></div>
                            <div><small class="form-text text-muted"><?= l('monitor.input.ping_servers_ids_help') ?></small></div>

                            <div class="row">
                                <?php foreach($data->ping_servers as $ping_server): ?>
                                    <div class="col-12 col-lg-6">
                                        <div class="custom-control custom-checkbox my-2">
                                            <input id="ping_server_id_<?= $ping_server->ping_server_id ?>" name="ping_servers_ids[]" value="<?= $ping_server->ping_server_id ?>" type="checkbox" class="custom-control-input" <?= in_array($ping_server->ping_server_id, $data->values['ping_servers_ids']) ? 'checked="checked"' : null ?>>
                                            <label class="custom-control-label d-flex align-items-center" for="ping_server_id_<?= $ping_server->ping_server_id ?>">
                                                <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($ping_server->country_code) . '.svg' ?>" class="img-fluid icon-favicon mr-1" />
                                                <span class="mr-1"><?= $ping_server->city_name ?></span>
                                                <small class="badge badge-light badge-pill"><?= $ping_server->name ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex flex-column flex-xl-row justify-content-between">
                                <span class="mb-2 mb-xl-0"><i class="fa fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('monitor.input.is_ok_notifications') ?></span>
                                <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                            </div>
                            <div><small class="form-text text-muted"><?= l('monitor.input.is_ok_notifications_help') ?></small></div>

                            <div class="row">
                                <?php foreach($data->notification_handlers as $notification_handler): ?>
                                    <div class="col-12 col-lg-6">
                                        <div class="custom-control custom-checkbox my-2">
                                            <input id="is_ok_notifications_<?= $notification_handler->notification_handler_id ?>" name="is_ok_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->values['is_ok_notifications'] ?? []) ? 'checked="checked"' : null ?>>
                                            <label class="custom-control-label" for="is_ok_notifications_<?= $notification_handler->notification_handler_id ?>">
                                                <span class="mr-1"><?= $notification_handler->name ?></span>
                                                <small class="badge badge-light badge-pill"><?= l('notification_handlers.input.' . $notification_handler->type) ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>

                        <?php if(settings()->monitors_heartbeats->email_reports_is_enabled): ?>
                            <div <?= $this->user->plan_settings->email_reports_is_enabled ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                                <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->email_reports_is_enabled ? null : 'container-disabled' ?>">
                                    <input id="email_reports_is_enabled" name="email_reports_is_enabled" type="checkbox" class="custom-control-input" <?= $data->values['email_reports_is_enabled'] ? 'checked="checked"' : null?>>
                                    <label class="custom-control-label" for="email_reports_is_enabled"><?= l('heartbeat.input.email_reports_is_enabled') ?></label>
                                    <small class="form-text text-muted"><?= l('heartbeat.input.email_reports_is_enabled_help') ?></small>
                                </div>
                            </div>
                        <?php endif ?>

                        <div class="form-group">
                            <div class="d-flex flex-column flex-xl-row justify-content-between">
                                <label for="project_id"><i class="fa fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                                <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                            </div>
                            <select id="project_id" name="project_id" class="form-control">
                                <option value=""><?= l('global.none') ?></option>
                                <?php foreach($data->projects as $project_id => $project): ?>
                                    <option value="<?= $project_id ?>" <?= $data->values['project_id'] == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                        </div>

                        <div class="form-group">
                            <label for="check_interval_seconds"><i class="fa fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('monitor.input.check_interval_seconds') ?></label>
                            <select id="check_interval_seconds" name="check_interval_seconds" class="form-control" required="required">
                                <?php foreach($data->monitor_check_intervals as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $data->values['check_interval_seconds'] == $key ? 'selected="selected"' : null ?> <?= !in_array($key, $this->user->plan_settings->monitors_check_intervals ?? []) ? 'disabled="disabled"' : null ?>><?= $value ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('monitor.input.check_interval_seconds_help') ?></small>
                        </div>

                        <div class="form-group">
                            <label for="timeout_seconds"><i class="fa fa-fw fa-sm fa-exclamation-triangle text-muted mr-1"></i> <?= l('monitor.input.timeout_seconds') ?></label>
                            <select id="timeout_seconds" name="timeout_seconds" class="form-control" required="required">
                                <?php foreach($data->monitor_timeouts as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $data->values['timeout_seconds'] == $key ? 'selected="selected"' : null ?>><?= $value ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 my-3" type="button" data-toggle="collapse" data-target="#custom_request_container" aria-expanded="false" aria-controls="custom_request_container" data-type="website">
                    <i class="fa fa-fw fa-share fa-sm mr-1"></i> <?= l('monitor.input.custom_request') ?>
                </button>

                <div class="collapse" id="custom_request_container">
                    <div data-type="website">

                        <div class="form-group">
                            <label for="request_method"><?= l('monitor.input.request_method') ?></label>
                            <select id="request_method" name="request_method" class="form-control" required="required">
                                <option value="GET" <?= $data->values['request_method'] == 'GET' ? 'selected="selected"' : null ?>>GET</option>
                                <option value="POST" <?= $data->values['request_method'] == 'POST' ? 'selected="selected"' : null ?>>POST</option>
                                <option value="HEAD" <?= $data->values['request_method'] == 'HEAD' ? 'selected="selected"' : null ?>>HEAD</option>
                                <option value="OPTIONS" <?= $data->values['request_method'] == 'OPTIONS' ? 'selected="selected"' : null ?>>OPTIONS</option>
                                <option value="PUT" <?= $data->values['request_method'] == 'PUT' ? 'selected="selected"' : null ?>>PUT</option>
                                <option value="PATCH" <?= $data->values['request_method'] == 'PATCH' ? 'selected="selected"' : null ?>>PATCH</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="request_body"><?= l('monitor.input.request_body') ?></label>
                            <textarea id="request_body" name="request_body" class="form-control" maxlength="8192"><?= $data->values['request_body'] ?></textarea>
                            <small class="form-text text-muted"><?= l('monitor.input.request_body_help') ?></small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-lg-6">
                                <label for="request_basic_auth_username"><?= l('monitor.input.request_basic_auth_username') ?></label>
                                <input type="text" id="request_basic_auth_username" name="request_basic_auth_username" class="form-control" value="<?= $data->values['request_basic_auth_username'] ?>" maxlength="256" autocomplete="off" />
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="request_basic_auth_password"><?= l('monitor.input.request_basic_auth_password') ?></label>
                                <input type="text" id="request_basic_auth_password" name="request_basic_auth_password" class="form-control" value="<?= $data->values['request_basic_auth_password'] ?>" maxlength="256" autocomplete="off" />
                            </div>
                        </div>

                        <label><?= l('monitor.input.request_headers') ?></label>
                        <div id="request_headers">
                            <?php foreach($data->values['request_headers'] as $key => $request_header): ?>
                                <div class="form-row">
                                    <div class="form-group col-lg-6">
                                        <input type="text" name="request_header_name[<?= $key ?>]" class="form-control" value="<?= $request_header->name ?>" placeholder="<?= l('monitor.input.request_header_name') ?>" />
                                    </div>

                                    <div class="form-group col-lg-5">
                                        <input type="text" name="request_header_value[<?= $key ?>]" class="form-control" value="<?= $request_header->value ?>" placeholder="<?= l('monitor.input.request_header_value') ?>" />
                                    </div>

                                    <div class="form-group col-lg-1 text-center">
                                        <button type="button" data-remove="request" class="btn btn-outline-danger" title="<?= l('monitor.input.request_header_remove') ?>"><i class="fa fa-fw fa-times"></i></button>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <div class="mb-3">
                            <button data-add="request" type="button" class="btn btn-sm btn-outline-success"><i class="fa fa-fw fa-plus-circle"></i> <?= l('monitor.input.request_header_add') ?></button>
                        </div>

                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 my-3" type="button" data-toggle="collapse" data-target="#custom_response_container" aria-expanded="false" aria-controls="custom_response_container" data-type="website">
                    <i class="fa fa-fw fa-reply fa-sm mr-1"></i> <?= l('monitor.input.custom_response') ?>
                </button>

                <div class="collapse" id="custom_response_container">
                    <div data-type="website">

                        <div class="form-group">
                            <label for="response_status_code"><?= l('monitor.input.response_status_code') ?></label>
                            <input type="number" id="response_status_code" name="response_status_code" min="0" max="1000" class="form-control" value="<?= $data->values['response_status_code'] ?>" required="required" />
                        </div>

                        <div class="form-group">
                            <label for="response_body"><?= l('monitor.input.response_body') ?></label>
                            <textarea id="response_body" name="response_body" maxlength="8192" class="form-control"><?= $data->values['response_body'] ?></textarea>
                            <small class="form-text text-muted"><?= l('monitor.input.response_body_help') ?></small>
                        </div>

                        <label><?= l('monitor.input.response_headers') ?></label>
                        <div id="response_headers">
                            <?php foreach($data->values['response_headers'] as $key => $response_header): ?>
                                <div class="form-row">
                                    <div class="form-group col-lg-6">
                                        <input type="text" name="response_header_name[<?= $key ?>]" class="form-control" value="<?= $response_header->name ?>" placeholder="<?= l('monitor.input.response_header_name') ?>" />
                                    </div>

                                    <div class="form-group col-lg-5">
                                        <input type="text" name="response_header_value[<?= $key ?>]" class="form-control" value="<?= $response_header->value ?>" placeholder="<?= l('monitor.input.response_header_value') ?>" />
                                    </div>

                                    <div class="form-group col-lg-1 text-center">
                                        <button type="button" data-remove="response" class="btn btn-outline-danger" title="<?= l('monitor.input.response_header_remove') ?>"><i class="fa fa-fw fa-times"></i></button>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <div class="mb-3">
                            <button data-add="response" type="button" class="btn btn-sm btn-outline-success"><i class="fa fa-fw fa-plus-circle"></i> <?= l('monitor.input.request_header_add') ?></button>
                        </div>

                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<template id="template_request_header">
    <div class="form-row">
        <div class="form-group col-lg-6">
            <input type="text" name="request_header_name[]" class="form-control" value="" maxlength="128" placeholder="<?= l('monitor.input.request_header_name') ?>" />
        </div>

        <div class="form-group col-lg-5">
            <input type="text" name="request_header_value[]" class="form-control" value="" maxlength="256" placeholder="<?= l('monitor.input.request_header_value') ?>" />
        </div>

        <div class="form-group col-lg-1 text-center">
            <button type="button" data-remove="request" class="btn btn-outline-danger" title="<?= l('monitor.input.request_header_remove') ?>"><i class="fa fa-fw fa-times"></i></button>
        </div>
    </div>
</template>

<template id="template_response_header">
    <div class="form-row">
        <div class="form-group col-lg-6">
            <input type="text" name="response_header_name[]" class="form-control" value="" maxlength="128" placeholder="<?= l('monitor.input.response_header_name') ?>" />
        </div>

        <div class="form-group col-lg-5">
            <input type="text" name="response_header_value[]" class="form-control" value="" maxlength="256" placeholder="<?= l('monitor.input.response_header_value') ?>" />
        </div>

        <div class="form-group col-lg-1 text-center">
            <button type="button" data-remove="response" class="btn btn-outline-danger" title="<?= l('monitor.input.response_header_remove') ?>"><i class="fa fa-fw fa-times"></i></button>
        </div>
    </div>
</template>

<?php ob_start() ?>
<script>
'use strict';

/* Type handler */
let type_handler = () => {
    let type = document.querySelector('select[name="type"]').value;

    document.querySelectorAll(`[data-type]:not([data-type="${type}"])`).forEach(element => {
        element.classList.add('d-none');

        element.querySelector('input[name="target"],input[name="port"]') && element.querySelector('input[name="target"],input[name="port"]').setAttribute('disabled', 'disabled');
        element.querySelector('input[name="target"],input[name="port"]') && element.querySelector('input[name="target"],input[name="port"]').removeAttribute('required');
    });

    document.querySelectorAll(`[data-type="${type}"]`).forEach(element => {
        element.classList.remove('d-none');

        element.querySelector('input[name="target"],input[name="port"]') && element.querySelector('input[name="target"],input[name="port"]').removeAttribute('disabled');
        element.querySelector('input[name="target"],input[name="port"]') && element.querySelector('input[name="target"],input[name="port"]').setAttribute('required', 'required');
    });
}

type_handler();

document.querySelector('select[name="type"]') && document.querySelector('select[name="type"]').addEventListener('change', type_handler);

/* add new request header */
let header_add = event => {
    let type = event.currentTarget.getAttribute('data-add');

    let clone = document.querySelector(`#template_${type}_header`).content.cloneNode(true);

    let request_headers_count = document.querySelectorAll(`#${type}_headers .form-row`).length;

    if(request_headers_count > 20) {
        return;
    }

    clone.querySelector(`input[name="${type}_header_name[]"`).setAttribute('name', `${type}_header_name[${request_headers_count}]`);
    clone.querySelector(`input[name="${type}_header_value[]"`).setAttribute('name', `${type}_header_value[${request_headers_count}]`);

    document.querySelector(`#${type}_headers`).appendChild(clone);

    header_remove_initiator();
};

document.querySelectorAll('[data-add]').forEach(element => {
    element.addEventListener('click', header_add);
})


/* remove request header */
let header_remove = event => {
    event.currentTarget.closest('.form-row').remove();
};

let header_remove_initiator = () => {
    document.querySelectorAll('#request_headers [data-remove], #response_headers [data-remove]').forEach(element => {
        element.removeEventListener('click', header_remove);
        element.addEventListener('click', header_remove)
    })
};

header_remove_initiator();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
