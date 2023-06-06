<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('domain-names') ?>"><?= l('domain_names.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('domain_name_create.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 text-truncate"><?= l('domain_name_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fa fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('domain_name.input.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                    <small class="form-text text-muted"><?= l('domain_name.input.name_help') ?></small>
                </div>

                <div class="form-group">
                    <label for="target"><i class="fa fa-fw fa-sm fa-network-wired text-muted mr-1"></i> <?= l('domain_name.input.target') ?></label>
                    <input type="text" id="target" name="target" class="form-control <?= \Altum\Alerts::has_field_errors('target') ? 'is-invalid' : null ?>" value="<?= $data->values['target'] ?>" placeholder="<?= l('domain_name.input.target_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('target') ?>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fa fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('domain_name.input.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div>

                        <div class="mb-3">
                            <div class="d-flex flex-column flex-xl-row justify-content-between">
                                <span class="mb-2 mb-xl-0"><i class="fa fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('domain_name.input.whois_notifications') ?></span>
                                <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                            </div>

                            <div class="row">
                                <?php foreach($data->notification_handlers as $notification_handler): ?>
                                    <div class="col-12 col-lg-6">
                                        <div class="custom-control custom-checkbox my-2">
                                            <input id="whois_notifications_<?= $notification_handler->notification_handler_id ?>" name="whois_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->values['whois_notifications'] ?? []) ? 'checked="checked"' : null ?>>
                                            <label class="custom-control-label" for="whois_notifications_<?= $notification_handler->notification_handler_id ?>">
                                                <span class="mr-1"><?= $notification_handler->name ?></span>
                                                <small class="badge badge-light badge-pill"><?= l('notification_handlers.input.' . $notification_handler->type) ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="whois_notifications_timing"><?= l('domain_name.input.whois_notifications_timing') ?></label>
                            <select id="whois_notifications_timing" name="whois_notifications_timing" class="form-control" required="required">
                                <?php foreach($data->domain_name_timings as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $data->values['whois_notifications_timing'] == $key ? 'selected="selected"' : null ?>><?= sprintf(l('domain_name.input.whois_notifications_timing_input'), $value) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex flex-column flex-xl-row justify-content-between">
                                <span class="mb-2 mb-xl-0"><i class="fa fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('domain_name.input.ssl_notifications') ?></span>
                                <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                            </div>

                            <div class="row">
                                <?php foreach($data->notification_handlers as $notification_handler): ?>
                                    <div class="col-12 col-lg-6">
                                        <div class="custom-control custom-checkbox my-2">
                                            <input id="ssl_notifications_<?= $notification_handler->notification_handler_id ?>" name="ssl_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->values['ssl_notifications'] ?? []) ? 'checked="checked"' : null ?>>
                                            <label class="custom-control-label" for="ssl_notifications_<?= $notification_handler->notification_handler_id ?>">
                                                <span class="mr-1"><?= $notification_handler->name ?></span>
                                                <small class="badge badge-light badge-pill"><?= l('notification_handlers.input.' . $notification_handler->type) ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ssl_notifications_timing"><i class="fa fa-fw fa-sm fa-calendar text-muted mr-1"></i> <?= l('domain_name.input.ssl_notifications_timing') ?></label>
                            <select id="ssl_notifications_timing" name="ssl_notifications_timing" class="form-control" required="required">
                                <?php foreach($data->domain_name_timings as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $data->values['ssl_notifications_timing'] == $key ? 'selected="selected"' : null ?>><?= sprintf(l('domain_name.input.ssl_notifications_timing_input'), $value) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

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

                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

