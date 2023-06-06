<?php defined('ALTUMCODE') || die() ?>

<?php
/* Determine the border color based on the status */
$status_class_name = 'warning';

if($data->monitor->is_enabled) {
    $status_class_name = $data->monitor->is_ok ? 'primary' : 'danger';
}
?>
<div class="col-12 col-md-6 col-xl-4 mb-4">
    <div class="card h-100" <?= $data->monitor->project_id ? 'style="border-color: ' . $data->projects[$data->monitor->project_id]->color . ';"' : null ?>>
        <div class="card-body d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between">
                <h2 class="h5 m-0 card-title text-truncate">
                    <a href="<?= url('monitor/' . $data->monitor->monitor_id) ?>"><?= $data->monitor->name ?></a>
                </h2>

                <?= include_view(THEME_PATH . 'views/monitor/monitor_dropdown_button.php', ['id' => $data->monitor->monitor_id, 'resource_name' => $data->monitor->name]) ?>
            </div>

            <div class="d-flex flex-column flex-xl-row justify-content-xl-between mb-3">
                <div class="d-flex flex-column mb-2 mb-xl-0 text-truncate">
                    <small class="text-muted">
                        <?php if($data->monitor->is_enabled): ?>
                            <?php if($data->monitor->is_ok): ?>
                                <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.is_ok') ?>">
                                    <i class="fa fa-fw fa-sm fa-check-circle text-success"></i>
                            <?php else: ?>
                                <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.is_not_ok') ?>">
                                    <i class="fa fa-fw fa-sm fa-times-circle text-danger"></i>
                            <?php endif ?>
                        <?php else: ?>
                            <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.is_enabled_paused') ?>">
                                <i class="fa fa-fw fa-sm fa-pause-circle text-warning"></i>
                        <?php endif ?>
       
			    <?= $data->monitor->target ?><?= $data->monitor->port ? ':' . $data->monitor->port : null ?>
                        </span>
                    </small>
                    <small class="text-muted">
                        <span data-toggle="tooltip" title="Project">Project: <a href="<?= url('project/' . $data->monitor->project_id) ?>"><?= $data->monitor->project_id ? $data->projects[$data->monitor->project_id]->name : "none" ?></a></span>
                    </small>
                </div>
            </div>

            <div class="d-flex flex-column flex-xl-row justify-content-xl-between">
                <div class="d-flex flex-column mb-2 mb-xl-0">
                    <small class="text-muted"><?= l('monitor.uptime') ?></small>
                    <span class="font-weight-bold" data-toggle="tooltip" title="<?= sprintf(l('monitor.total_checks_tooltip'), nr($data->monitor->total_checks)) ?>">
                        <?= nr($data->monitor->uptime, 3) . '%' ?>
                    </span>
                </div>

                <div class="d-flex flex-column">
                    <small class="text-muted"><?= l('monitor.average_response_time') ?></small>
                    <span class="font-weight-bold" data-toggle="tooltip" title="<?= sprintf(l('monitor.total_ok_checks_tooltip'), nr($data->monitor->total_ok_checks)) ?>">
                        <p class="text-right"><?= display_response_time($data->monitor->average_response_time) ?></p>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
