<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('thresholds') ?>"><?= l('thresholds.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('threshold.breadcrumb') ?></li>
        </ol>
    </nav>
    <div class="card bg-blue-900 border-0">
        <div class="card-body">
            <div class="row">
                <div class="col-auto">
                    <?php if ($data->threshold->is_enabled) : ?>
                        <?php if ($data->threshold->is_ok) : ?>
                            <div data-toggle="tooltip" title="<?= l('threshold.is_ok') ?>">
                                <i class="fa fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php else : ?>
                            <div data-toggle="tooltip" title="<?= l('threshold.is_not_ok') ?>">
                                <i class="fa fa-fw fa-sm fa-times-circle fa-3x text-danger"></i>
                            </div>
                        <?php endif ?>
                    <?php else : ?>
                        <div data-toggle="tooltip" title="<?= l('threshold.is_enabled_paused') ?>">
                            <i class="fa fa-fw fa-sm fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('threshold.header'), $data->threshold->name) ?></h1>

                    <div class="text-truncate text-gray-400">
                        <span><?= l('threshold.code') ?></span>
                    </div>

                    <div>
                        <code><?= url('webhook-threshold/' . $data->threshold->code . '?value=' . $data->threshold->settings->threshold_breach_value) ?></code>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/threshold/threshold_dropdown_button.php', ['id' => $data->threshold->threshold_id, 'resource_name' => $data->threshold->name]) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$data->threshold->total_runs) : ?>
        <div class="d-flex flex-column align-items-center justify-content-center mt-4">
            <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('threshold.no_data') ?>" />
            <h2 class="h4 text-muted"><?= l('threshold.no_data') ?></h2>
            <p class="text-muted"><?= sprintf(l('threshold.no_data_help'), $data->threshold->name) ?></p>
        </div>
    <?php endif ?>

    <?php if ($data->threshold->total_runs) : ?>


        <div class="row justify-content-between mt-4">
            <div class="col-12 col-xl mb-3 mb-xl-0">
                <div class="card h-100 tags-card">
                    <div class="card-body">

                        <!-- tags -->
                        <div>
                            <div class="clearfix ">
                                <h5 class="float-left" style="margin-right: 10%;">Tags</h5>
                                <div class="dropdown">
                                    <button class="float-right px-3 py-1 btn" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 15px;">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                    <div class="dropdown-menu p-3" style="width: 350px;" aria-labelledby="dropdownMenuButton">
                                        <div>
                                            <input class="form-control mr-sm-2" type="search" placeholder="Create or find a tag" aria-label="Search" id="tag-search">
                                        </div>
                                        <div>
                                            <p class="pt-3 tag-caption"></p>
                                            <div class="available-tags-list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="tag-list">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-between mt-4">
            <div class="col-12 col-xl mb-3 mb-xl-0">
                <div class="card h-100">
                    <div class="card-body d-flex">

                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fa fa-fw fa-globe fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted"><?= l('threshold.uptime') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h4 m-0"><?= $data->total_threshold_logs ? nr($data->threshold_logs_data['uptime'], 3) . '%' : '?' ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('threshold.total_runs_tooltip'), nr($data->total_threshold_logs)) ?>">
                                        <i class="fa fa-fw fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl mb-3 mb-xl-0">
                <div class="card h-100">
                    <div class="card-body d-flex">

                        <div>
                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3">
                                <div class="p-3 d-flex align-items-center justify-content-between">
                                    <i class="fa fa-fw fa-times-circle fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted"><?= l('threshold.total_incidents') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h4 m-0"><?= $data->total_threshold_logs ? nr(count($data->threshold_incidents)) : '?' ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('threshold.downtime_tooltip'), nr($data->threshold_logs_data['downtime'], 3) . '%') ?>">
                                        <i class="fa fa-fw fa-info-circle text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (($data->date->start_date != $data->date->end_date && $data->date->end_date == \Altum\Date::get('', 4)) || ($data->date->start_date == $data->date->end_date && $data->date->start_date == \Altum\Date::get('', 4))) : ?>
            <div class="mt-4">
                <div class="row justify-content-between">
                    <?php if ($data->threshold->is_enabled) : ?>
                        <div class="col-12 col-xl mb-3 mb-xl-0">
                            <?php if ($data->threshold->is_ok) : ?>
                                <div class="card h-100">
                                    <div class="card-body d-flex">
                                        <div>
                                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3">
                                                <div class="p-3 d-flex align-items-center justify-content-between">
                                                    <i class="fa fa-fw fa-check fa-lg"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <span class="text-muted"><?= l('threshold.currently_up_for') ?></span>
                                            <div class="d-flex align-items-center">
                                                <div class="card-title h4 m-0"><?= \Altum\Date::get_elapsed_time($data->threshold->main_run_datetime) ?></div>
                                                <div class="ml-2">
                                                    <span data-toggle="tooltip" title="<?= sprintf(l('threshold.last_missed_datetime_tooltip'), \Altum\Date::get($data->threshold->last_missed_datetime, 1)) ?>">
                                                        <i class="fa fa-fw fa-info-circle text-muted"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="card h-100">
                                    <div class="card-body d-flex">
                                        <div>
                                            <div class="card border-0 bg-blue-50 text-blue-800 mr-3">
                                                <div class="p-3 d-flex align-items-center justify-content-between">
                                                    <i class="fa fa-fw fa-times fa-lg"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <span class="text-muted"><?= l('threshold.currently_down_for') ?></span>
                                            <div class="d-flex align-items-center">
                                                <div class="card-title h4 m-0"><?= \Altum\Date::get_elapsed_time($data->threshold->main_missed_datetime) ?></div>
                                                <div class="ml-2">
                                                    <span data-toggle="tooltip" title="<?= sprintf(l('threshold.last_run_datetime_tooltip'), \Altum\Date::get($data->threshold->last_run_datetime, 1)) ?>">
                                                        <i class="fa fa-fw fa-info-circle text-muted"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                    <?php endif ?>

                    <div class="col-12 col-xl mb-3 mb-xl-0">
                        <div class="card h-100">
                            <div class="card-body d-flex">
                                <div>
                                    <div class="card border-0 bg-blue-50 text-blue-800 mr-3">
                                        <div class="p-3 d-flex align-items-center justify-content-between">
                                            <i class="fa fa-fw fa-calendar-check fa-lg"></i>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-muted"><?= l('threshold.last_run_datetime') ?></span>
                                    <div class="d-flex align-items-center">
                                        <div class="card-title h4 m-0"><?= \Altum\Date::get_timeago($data->threshold->last_run_datetime) ?></div>
                                        <div class="ml-2">
                                            <span data-toggle="tooltip" title="<?= sprintf(l('threshold.run_interval_seconds_tooltip'), $data->threshold->settings->run_interval, l('global.date.' . $data->threshold->settings->run_interval_type)) ?>">
                                                <i class="fa fa-fw fa-info-circle text-muted"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="d-flex justify-content-end mt-4">
            <div class="d-flex">
                <button id="daterangepicker" type="button" class="btn btn-sm btn-outline-secondary" data-min-date="<?= \Altum\Date::get($data->threshold->datetime, 4) ?>" data-max-date="<?= \Altum\Date::get('', 4) ?>">
                    <i class="fa fa-fw fa-calendar mr-lg-1"></i>
                    <span class="d-none d-lg-inline-block">
                        <?php if ($data->date->start_date == $data->date->end_date) : ?>
                            <?= \Altum\Date::get($data->date->start_date, 2, \Altum\Date::$default_timezone) ?>
                        <?php else : ?>
                            <?= \Altum\Date::get($data->date->start_date, 2, \Altum\Date::$default_timezone) . ' - ' . \Altum\Date::get($data->date->end_date, 2, \Altum\Date::$default_timezone) ?>
                        <?php endif ?>
                    </span>
                    <i class="fa fa-fw fa-caret-down d-none d-lg-inline-block ml-lg-1"></i>
                </button>

                <div class="ml-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>">
                            <i class="fa fa-fw fa-sm fa-download"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right d-print-none">
                            <a href="<?= url('threshold/' . $data->threshold->threshold_id . '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                                <i class="fa fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('threshold/' . $data->threshold->threshold_id . '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date . '&export=json') ?>" target="_blank" class="dropdown-item">
                                <i class="fa fa-fw fa-sm fa-file-code mr-1"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="ml-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary d-print-none" onclick="window.print()" title="<?= sprintf(l('global.export_to'), 'PDF') ?>">
                        <i class="fa fa-fw fa-file-pdf"></i>
                    </button>
                </div>
            </div>
        </div>

        <?php if ($data->total_threshold_logs) : ?>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="threshold_logs_chart"></canvas>
                    </div>
                </div>
            </div>

        <?php endif ?>

        <?php if ($data->total_threshold_logs) : ?>
            <div class="mt-4">
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th colspan="3"><?= l('threshold.incidents.header') ?></th>
                            </tr>
                            <tr>
                                <th><?= l('threshold.incidents.start_datetime') ?></th>
                                <th><?= l('threshold.incidents.end_datetime') ?></th>
                                <th><?= l('threshold.incidents.length') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!count($data->threshold_incidents)) : ?>
                                <tr>
                                    <td colspan="3" class="text-muted"><?= l('threshold.incidents.no_data') ?></td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($data->threshold_incidents as $threshold_incident) : ?>
                                    <tr>
                                        <td class="text-truncate text-muted">
                                            <?= \Altum\Date::get($threshold_incident->start_datetime, 1) ?>
                                        </td>
                                        <td class="text-truncate text-muted">
                                            <?= $threshold_incident->end_datetime ? \Altum\Date::get($threshold_incident->end_datetime, 1) : l('threshold.incidents.end_datetime_null') ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <?= \Altum\Date::get_elapsed_time($threshold_incident->start_datetime, $threshold_incident->end_datetime) ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            <?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif ?>

    <?php endif ?>

</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css' ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/Chart.bundle.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/chartjs_defaults.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/chartjs-plugin-annotation.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/tags.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/views.js' ?>"></script>

<script>
    'use strict';

    moment.tz.setDefault(<?= json_encode($this->user->timezone) ?>);

    /* Daterangepicker */
    $('#daterangepicker').daterangepicker({
        maxSpan: {
            days: 30
        },
        startDate: <?= json_encode($data->date->start_date) ?>,
        endDate: <?= json_encode($data->date->end_date) ?>,
        minDate: $('#daterangepicker').data('min-date'),
        maxDate: $('#daterangepicker').data('max-date'),
        ranges: {
            <?= json_encode(l('global.date.today')) ?>: [moment(), moment()],
            <?= json_encode(l('global.date.yesterday')) ?>: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            <?= json_encode(l('global.date.last_7_days')) ?>: [moment().subtract(6, 'days'), moment()],
            <?= json_encode(l('global.date.last_30_days')) ?>: [moment().subtract(29, 'days'), moment()],
            <?= json_encode(l('global.date.this_month')) ?>: [moment().startOf('month'), moment().endOf('month')],
            <?= json_encode(l('global.date.last_month')) ?>: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            <?= json_encode(l('global.date.all_time')) ?>: [moment($('#daterangepicker').data('min-date')), moment()]
        },
        alwaysShowCalendars: true,
        linkedCalendars: false,
        singleCalendar: true,
        locale: <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>,
    }, (start, end, label) => {

        /* Redirect */
        redirect(`<?= url('threshold/' . $data->threshold->threshold_id) ?>&start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });

    <?php if ($data->total_threshold_logs) : ?>
        let css = window.getComputedStyle(document.body)

        /* Response Time chart */
        let threshold_logs_chart = document.getElementById('threshold_logs_chart').getContext('2d');

        let is_ok_color = css.getPropertyValue('--gray-300');
        let is_ok_gradient = threshold_logs_chart.createLinearGradient(0, 0, 0, 250);
        is_ok_gradient.addColorStop(0, 'rgba(37, 45, 60, .1)');
        is_ok_gradient.addColorStop(1, 'rgba(37, 45, 60, 0.025)');

        /* Display chart */
        new Chart(threshold_logs_chart, {
            type: 'line',
            data: {
                labels: <?= $data->threshold_logs_chart['labels'] ?>,
                datasets: [{
                    label: <?= json_encode(l('threshold.is_ok_label')) ?>,
                    data: <?= $data->threshold_logs_chart['threshold_value'] ?? '[]' ?>,
                    backgroundColor: is_ok_gradient,
                    borderColor: is_ok_color,
                    fill: true
                }]
            },
            options: {
                ...chart_options,
                annotation: {
                    annotations: [{
                        type: 'line',
                        mode: 'horizontal',
                        scaleID: 'y-axis-0',
                        <?php
                        if ($data->threshold->settings->threshold_breach_value_type != 'none') { ?>
                            value: <?= $data->threshold->settings->threshold_breach_value ?? '' ?>,
                            borderColor: 'green',
                            borderWidth: 2,
                        <?php
                        }
                        ?>

                        label: {
                            enabled: true,
                            content: "Threshold: <?= $data->threshold->settings->threshold_breach_value ?? '' ?>"
                        }
                    }]
                }
            }
        });
    <?php endif ?>
</script>
<script>
    $(document).ready(function() {

        var object_type = 'threshold';
        var object_id = <?php echo $data->threshold->threshold_id ?>;
        var get_url = '<?php echo url('tags/getTags') ?>';
        var delete_url = '<?php echo url('tags/deleteTags') ?>';
        var search_url = '<?php echo url('tags/searchTags') ?>';
        var save_url = '<?php echo url('tags/saveTags') ?>';
        
        getTags(get_url, object_type, object_id);

        $(document).on('click', '.tag-remove', function() {
            var tag_name = $(this).parent('span').attr('tag_name');
            deleteTag(delete_url, object_type, object_id, tag_name);
            getTags(get_url, object_type, object_id);
        });

        $(document).on('input', '#tag-search', function() {
            var search_string = $(this).val();
            if (search_string == '') {
                getTags(get_url, object_type, object_id);
            } else {
                searchTag(search_url, object_type, object_id, search_string);
            }
        });

        $(document).on('click', '.available-tags', function() {
            var tag_name = $(this).text();
            saveTag(save_url, object_type, object_id, tag_name);
            getTags(get_url, object_type, object_id);
        });
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
