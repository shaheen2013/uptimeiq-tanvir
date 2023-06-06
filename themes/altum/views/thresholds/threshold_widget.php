<?php defined('ALTUMCODE') || die() ?>

<?php
/* Determine the border color based on the status */
$status_class_name = 'warning';

if($data->threshold->is_enabled) {
    $status_class_name = $data->threshold->is_ok ? 'primary' : 'danger';
}
?>
<div class="col-12 col-md-6 col-xl-4 mb-4">
    <div class="card h-100" <?= $data->threshold->project_id ? 'style="border-color: ' . $data->projects[$data->threshold->project_id]->color . ';"' : null ?>>
        <div class="card-body d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between">
                <h2 class="h5 m-0 card-title text-truncate">
                    <a href="<?= url('threshold/' . $data->threshold->threshold_id) ?>"><?= $data->threshold->name ?></a>
                </h2>

                <?= include_view(THEME_PATH . 'views/threshold/threshold_dropdown_button.php', ['id' => $data->threshold->threshold_id, 'resource_name' => $data->threshold->name]) ?>
            </div>

            <div class="mb-3 text-truncate">
                <small class="text-muted">
                    <?php if($data->threshold->is_enabled): ?>
                        <?php if($data->threshold->is_ok): ?>
                            <span class="mr-1" data-toggle="tooltip" title="<?= l('threshold.is_ok') ?>">
                                <i class="fa fa-fw fa-sm fa-check-circle text-success"></i>
                            </span>
                        <?php else: ?>
                            <span class="mr-1" data-toggle="tooltip" title="<?= l('threshold.is_not_ok') ?>">
                                <i class="fa fa-fw fa-sm fa-times-circle text-danger"></i>
                            </span>
                        <?php endif ?>
                    <?php else: ?>
                        <span class="mr-1" data-toggle="tooltip" title="<?= l('threshold.is_enabled_paused') ?>">
                            <i class="fa fa-fw fa-sm fa-pause-circle text-warning"></i>
                        </span>
                    <?php endif ?>

                    <span data-toggle="tooltip" title="<?= $data->threshold->last_run_datetime ? \Altum\Date::get($data->threshold->last_run_datetime, 1) : '' ?>"><?= sprintf(l('thresholds.last_run_datetime'), $data->threshold->last_run_datetime ? \Altum\Date::get_timeago($data->threshold->last_run_datetime) : '-') ?></span>
                </small>
            </div>

            <div class="d-flex flex-column flex-xl-row justify-content-xl-between">
                <div class="d-flex flex-column mb-2 mb-xl-0">
                    <small class="text-muted"><?= l('threshold.uptime') ?></small>
                    <span class="font-weight-bold" data-toggle="tooltip" title="<?= sprintf(l('threshold.total_runs_tooltip'), nr($data->threshold->total_runs)) ?>">
                        <?= nr($data->threshold->uptime, 3) . '%' ?>
                    </span>
                </div>

                <div class="d-flex flex-column mb-2 mb-xl-0">
                    <small class="text-muted"><?= l('threshold.downtime') ?></small>
                    <span class="font-weight-bold" data-toggle="tooltip" title="<?= sprintf(l('threshold.total_missed_runs_tooltip'), nr($data->threshold->total_missed_runs)) ?>">
                        <p class="text-right"><?= nr($data->threshold->downtime, 3) . '%' ?></p>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
