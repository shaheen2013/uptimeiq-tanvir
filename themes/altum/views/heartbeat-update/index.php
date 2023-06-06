<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('heartbeats') ?>"><?= l('heartbeats.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li>
                <a href="<?= url('heartbeat/' . $data->heartbeat->heartbeat_id) ?>"><?= l('heartbeat.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('heartbeat_update.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><?= sprintf(l('heartbeat_update.header'), $data->heartbeat->name) ?></h1>

        <?= include_view(THEME_PATH . 'views/heartbeat/heartbeat_dropdown_button.php', ['id' => $data->heartbeat->heartbeat_id, 'resource_name' => $data->heartbeat->name]) ?>
    </div>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fa fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('heartbeat.input.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->heartbeat->name ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                    <small class="form-text text-muted"><?= l('heartbeat.input.name_help') ?></small>
                </div>

                <div class="form-row">
                    <div class="form-group col">
                        <label for="run_interval"><i class="fa fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('heartbeat.input.run_interval') ?></label>
                        <input type="number" min="1" step="1" id="run_interval" name="run_interval" class="form-control" value="<?= $data->heartbeat->settings->run_interval ?>" />
                    </div>

                    <div class="form-group col">
                        <label>&nbsp;</label>
                        <select id="run_interval_type" name="run_interval_type" class="form-control">
                            <option value="minutes" <?= $data->heartbeat->settings->run_interval_type == 'minutes' ? 'selected="selected"' : null ?>><?= l('global.date.minutes') ?></option>
                            <option value="hours" <?= $data->heartbeat->settings->run_interval_type == 'hours' ? 'selected="selected"' : null ?>><?= l('global.date.hours') ?></option>
                            <option value="days" <?= $data->heartbeat->settings->run_interval_type == 'days' ? 'selected="selected"' : null ?>><?= l('global.date.days') ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col">
                        <label for="run_interval_grace"><i class="fa fa-fw fa-sm fa-hourglass-half text-muted mr-1"></i> <?= l('heartbeat.input.run_interval_grace') ?></label>
                        <input type="number" min="1" step="1" id="run_interval_grace" name="run_interval_grace" class="form-control" value="<?= $data->heartbeat->settings->run_interval_grace ?>" />
                        <small class="form-text text-muted"><?= l('heartbeat.input.run_interval_grace_help') ?></small>
                    </div>

                    <div class="form-group col">
                        <label>&nbsp;</label>
                        <select id="run_interval_grace_type" name="run_interval_grace_type" class="form-control">
                            <option value="seconds" <?= $data->heartbeat->settings->run_interval_grace_type == 'seconds' ? 'selected="selected"' : null ?>><?= l('global.date.seconds') ?></option>
                            <option value="minutes" <?= $data->heartbeat->settings->run_interval_grace_type == 'minutes' ? 'selected="selected"' : null ?>><?= l('global.date.minutes') ?></option>
                            <option value="hours" <?= $data->heartbeat->settings->run_interval_grace_type == 'hours' ? 'selected="selected"' : null ?>><?= l('global.date.hours') ?></option>
                            <option value="days" <?= $data->heartbeat->settings->run_interval_grace_type == 'days' ? 'selected="selected"' : null ?>><?= l('global.date.days') ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->heartbeat->is_enabled ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="is_enabled"><?= l('heartbeat.input.is_enabled') ?></label>
                    <small class="form-text text-muted"><?= l('heartbeat.input.is_enabled_help') ?></small>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fa fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('heartbeat.input.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div>

                        <div class="mb-3">
                            <div class="d-flex flex-column flex-xl-row justify-content-between">
                                <span class="mb-2 mb-xl-0"><i class="fa fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('heartbeat.input.is_ok_notifications') ?></span>
                                <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                            </div>
                            <div><small class="form-text text-muted"><?= l('heartbeat.input.is_ok_notifications_help') ?></small></div>

                            <div class="row">
                                <?php foreach($data->notification_handlers as $notification_handler): ?>
                                    <div class="col-12 col-lg-6">
                                        <div class="custom-control custom-checkbox my-2">
                                            <input id="is_ok_notifications_<?= $notification_handler->notification_handler_id ?>" name="is_ok_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->heartbeat->notifications->is_ok ?? []) ? 'checked="checked"' : null ?>>
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
                                    <input id="email_reports_is_enabled" name="email_reports_is_enabled" type="checkbox" class="custom-control-input" <?= $data->heartbeat->email_reports_is_enabled ? 'checked="checked"' : null?>>
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
                                    <option value="<?= $project_id ?>" <?= $data->heartbeat->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                        </div>

                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
'use strict';

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
