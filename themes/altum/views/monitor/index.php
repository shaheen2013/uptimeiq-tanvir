<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('monitors') ?>"><?= l('monitors.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('monitor.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="card bg-blue-900 border-0">
        <div class="card-body">
            <div class="row">
                <div class="col-auto">
                    <?php if ($data->monitor->is_enabled) : ?>
                        <?php if ($data->monitor->is_ok) : ?>
                            <div data-toggle="tooltip" title="<?= l('monitor.is_ok') ?>">
                                <i class="fa fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php else : ?>
                            <div data-toggle="tooltip" title="<?= l('monitor.is_not_ok') ?>">
                                <i class="fa fa-fw fa-sm fa-times-circle fa-3x text-danger"></i>
                            </div>
                        <?php endif ?>
                    <?php else : ?>
                        <div data-toggle="tooltip" title="<?= l('monitor.is_enabled_paused') ?>">
                            <i class="fa fa-fw fa-sm fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="col text-truncate">
                    <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('monitor.header'), $data->monitor->name) ?></h1>

                    <div class="text-gray-400">
                        <span><?= $data->monitor->target ?><?= $data->monitor->port ? ':' . $data->monitor->port : null ?></span>
                    </div>
                </div>

                <div class="col-auto">
                    <?= include_view(THEME_PATH . 'views/monitor/monitor_dropdown_button.php', ['id' => $data->monitor->monitor_id, 'resource_name' => $data->monitor->name]) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$data->monitor->total_checks) : ?>
        <div class="d-flex flex-column align-items-center justify-content-center mt-4">
            <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('monitor.no_data') ?>" />
            <h2 class="h4 text-muted"><?= l('monitor.no_data') ?></h2>
            <p class="text-muted"><?= sprintf(l('monitor.no_data_help'), $data->monitor->name) ?></p>
        </div>
    <?php endif ?>

    <?php if ($data->monitor->total_checks) : ?>

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
                            <span class="text-muted"><?= l('monitor.uptime') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h4 m-0"><?= $data->total_monitor_logs ? nr($data->monitor_logs_data['uptime'], 3) . '%' : '?' ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('monitor.total_checks_tooltip'), nr($data->total_monitor_logs)) ?>">
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
                                    <i class="fa fa-fw fa-bolt fa-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted"><?= l('monitor.average_response_time') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h4 m-0"><?= $data->total_monitor_logs ? display_response_time($data->monitor_logs_data['average_response_time']) : '?' ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('monitor.total_ok_checks_tooltip'), nr($data->monitor_logs_data['total_ok_checks'])) ?>">
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
                            <span class="text-muted"><?= l('monitor.total_incidents') ?></span>
                            <div class="d-flex align-items-center">
                                <div class="card-title h4 m-0"><?= $data->total_monitor_logs ? nr(count($data->monitor_incidents)) : '?' ?></div>
                                <div class="ml-2">
                                    <span data-toggle="tooltip" title="<?= sprintf(l('monitor.downtime_tooltip'), nr($data->monitor_logs_data['downtime'], 3) . '%') ?>">
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
                    <?php if ($data->monitor->is_enabled) : ?>
                        <div class="col-12 col-xl mb-3 mb-xl-0">
                            <?php if ($data->monitor->is_ok) : ?>
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
                                            <span class="text-muted"><?= l('monitor.currently_up_for') ?></span>
                                            <div class="d-flex align-items-center">
                                                <div class="card-title h4 m-0"><?= \Altum\Date::get_elapsed_time($data->monitor->main_ok_datetime) ?></div>
                                                <div class="ml-2">
                                                    <span data-toggle="tooltip" title="<?= sprintf(l('monitor.last_not_ok_datetime_tooltip'), \Altum\Date::get($data->monitor->last_not_ok_datetime, 1)) ?>">
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
                                            <span class="text-muted"><?= l('monitor.currently_down_for') ?></span>
                                            <div class="d-flex align-items-center">
                                                <div class="card-title h4 m-0"><?= \Altum\Date::get_elapsed_time($data->monitor->main_not_ok_datetime) ?></div>
                                                <div class="ml-2">
                                                    <span data-toggle="tooltip" title="<?= sprintf(l('monitor.last_ok_datetime_tooltip'), \Altum\Date::get($data->monitor->last_ok_datetime, 1)) ?>">
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
                                    <span class="text-muted"><?= l('monitor.last_check_datetime') ?></span>
                                    <div class="d-flex align-items-center">
                                        <div class="card-title h4 m-0"><?= \Altum\Date::get_timeago($data->monitor->last_check_datetime) ?></div>
                                        <div class="ml-2">
                                            <span data-toggle="tooltip" title="<?= sprintf(l('monitor.check_interval_seconds_tooltip'), $data->monitor->settings->check_interval_seconds) ?>">
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
                <button id="daterangepicker" type="button" class="btn btn-sm btn-outline-secondary" data-min-date="<?= \Altum\Date::get($data->monitor->datetime, 4) ?>" data-max-date="<?= \Altum\Date::get('', 4) ?>">
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
                            <a href="<?= url('monitor/' . $data->monitor->monitor_id . '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                                <i class="fa fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('monitor/' . $data->monitor->monitor_id . '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date . '&export=json') ?>" target="_blank" class="dropdown-item">
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

        <?php if ($data->total_monitor_logs) : ?>

            <div class="card mt-4">
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monitor_logs_chart"></canvas>
                    </div>
                </div>
            </div>

        <?php endif ?>

        <div class="mt-4">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th colspan="5"><?= l('monitor.ping_servers_checks.header') ?></th>
                        </tr>
                        <tr>
                            <th><?= l('monitor.ping_servers_checks.ping_server') ?></th>
                            <th><?= l('monitor.ping_servers_checks.lowest_response_time') ?></th>
                            <th><?= l('monitor.ping_servers_checks.highest_response_time') ?></th>
                            <th><?= l('monitor.ping_servers_checks.average_response_time') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data->total_monitor_logs) : ?>
                            <tr>
                                <td colspan="4" class="text-muted"><?= l('monitor.ping_servers_checks.no_data') ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($data->ping_servers_checks as $ping_server_id => $ping_server_data) : ?>
                                <?php
                                /* Calculate */
                                $ping_server_data['average_response_time'] = $ping_server_data['total_ok_checks'] > 0 ? $ping_server_data['total_response_time'] / $ping_server_data['total_ok_checks'] : 0;
                                ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->ping_servers[$ping_server_id]->country_code) . '.svg' ?>" class="img-fluid icon-favicon mr-1" data-toggle="tooltip" title="<?= get_country_from_country_code($data->ping_servers[$ping_server_id]->country_code) . ', ' . $data->ping_servers[$ping_server_id]->city_name ?>" />
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= display_response_time($ping_server_data['lowest_response_time']) ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= display_response_time($ping_server_data['highest_response_time']) ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= display_response_time($ping_server_data['average_response_time']) ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="text-muted">
                                            <?= sprintf(l('monitor.ping_servers_checks.total_ok_checks'), nr($ping_server_data['total_ok_checks'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach ?>

                            <tr>
                                <td colspan="5" class="text-muted">
                                    <div class="d-flex">
                                        <span class="font-weight-bold"><?= l('monitor.ping_servers_checks.self_location') ?></span>

                                        <span><i class="fa fa-fw fa-sm fa-arrow-right mx-1"></i></span>

                                        <div class="d-flex align-items-center">
                                            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->monitor->details->country_code) . '.svg' ?>" class="img-fluid icon-favicon mr-1" data-toggle="tooltip" title="<?= $data->monitor->details->continent_name . ', ' . get_country_from_country_code($data->monitor->details->country_code) ?>" />
                                            <span><?= $data->monitor->details->city_name ?></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif ?>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th colspan="4">
                                <?= l('monitor.checks.last_checks') ?>
                                <span class="ml-3 small">
                                    <a href="<?= url('monitor-logs/' . $data->monitor->monitor_id) ?>"><?= l('monitor.checks.view_all') ?></a>
                                </span>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="2"><?= l('monitor.checks.status') ?></th>
                            <th><?= l('monitor.checks.response_time') ?></th>
                            <?php if ($data->monitor->type == 'website') : ?>
                                <th><?= l('monitor.checks.response_status_code') ?></th>
                            <?php endif ?>
                            <th><?= l('monitor.checks.datetime') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data->total_monitor_logs) : ?>
                            <tr>
                                <td colspan="4" class="text-muted"><?= l('monitor.checks.no_data') ?></td>
                            </tr>
                        <?php else : ?>
                            <?php for ($i = count($data->monitor_logs) - 1; $i >= count($data->monitor_logs) - 5; $i--) : ?>

                                <?php
                                if (!isset($data->monitor_logs[$i])) {
                                    continue;
                                }
                                ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <?php if ($data->monitor_logs[$i]->is_ok) : ?>
                                            <i class="fa fa-fw fa-sm fa-check-circle text-success"></i>
                                        <?php else : ?>
                                            <i class="fa fa-fw fa-sm fa-times-circle text-danger"></i>
                                        <?php endif ?>

                                        <?php if ($data->monitor->type == 'website' && !$data->monitor_logs[$i]->is_ok) : ?>
                                            <?php
                                            $data->monitor_logs[$i]->error = json_decode($data->monitor_logs[$i]->error ?? '');
                                            if (isset($data->monitor_logs[$i]->error->type)) {
                                                if ($data->monitor_logs[$i]->error->type == 'exception') {
                                                    $error = $data->monitor_logs[$i]->error->message;
                                                } elseif (in_array($data->monitor_logs[$i]->error->type, ['response_status_code', 'response_body', 'response_header'])) {
                                                    $error = l('monitor.checks.error.' . $data->monitor_logs[$i]->error->type);
                                                }
                                            }
                                            ?>

                                            <span class="ml-3" data-toggle="tooltip" title="<?= $error ?>">
                                                <i class="fa fa-fw fa-sm fa-envelope-open-text text-muted"></i>
                                            </span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->ping_servers[$data->monitor_logs[$i]->ping_server_id]->country_code) . '.svg' ?>" class="img-fluid icon-favicon" data-toggle="tooltip" title="<?= get_country_from_country_code($data->ping_servers[$data->monitor_logs[$i]->ping_server_id]->country_code) . ', ' . $data->ping_servers[$data->monitor_logs[$i]->ping_server_id]->city_name ?>" />
                                    </td>

                                    <td class="text-nowrap">
                                        <?= display_response_time($data->monitor_logs[$i]->response_time) ?>
                                    </td>

                                    <?php if ($data->monitor->type == 'website') : ?>
                                        <td class="text-nowrap"><?= $data->monitor_logs[$i]->response_status_code ?></td>
                                    <?php endif ?>

                                    <td class="text-nowrap">
                                        <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($data->monitor_logs[$i]->datetime, 1) ?>">
                                            <?= \Altum\Date::get_timeago($data->monitor_logs[$i]->datetime) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endfor ?>
                        <?php endif ?>

                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($data->total_monitor_logs) : ?>
            <div class="mt-4">
                <div class="table-responsive table-custom-container">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th colspan="4"><?= l('monitor.incidents.header') ?></th>
                            </tr>
                            <tr>
                                <th><?= l('monitor.incidents.start_datetime') ?></th>
                                <th><?= l('monitor.incidents.end_datetime') ?></th>
                                <th><?= l('monitor.incidents.length') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!count($data->monitor_incidents)) : ?>
                                <tr>
                                    <td colspan="4" class="text-muted"><?= l('monitor.incidents.no_data') ?></td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($data->monitor_incidents as $monitor_incident) : ?>
                                    <tr>
                                        <td class="text-truncate text-muted">
                                            <?= \Altum\Date::get($monitor_incident->start_datetime, 1) ?>
                                        </td>
                                        <td class="text-truncate text-muted">
                                            <?= $monitor_incident->end_datetime ? \Altum\Date::get($monitor_incident->end_datetime, 1) : l('monitor.incidents.end_datetime_null') ?>
                                        </td>
                                        <td class="text-truncate">
                                            <?= \Altum\Date::get_elapsed_time($monitor_incident->start_datetime, $monitor_incident->end_datetime) ?>
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
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/tags.js' ?>"></script>

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
        redirect(`<?= url('monitor/' . $data->monitor->monitor_id) ?>&start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });

    <?php if ($data->total_monitor_logs) : ?>
        let css = window.getComputedStyle(document.body)

        /* Response Time chart */
        let monitor_logs_chart = document.getElementById('monitor_logs_chart').getContext('2d');

        let response_time_color = css.getPropertyValue('--primary');
        let response_time_gradient = monitor_logs_chart.createLinearGradient(0, 0, 0, 250);
        response_time_gradient.addColorStop(0, 'rgba(16, 183, 127, .1)');
        response_time_gradient.addColorStop(1, 'rgba(16, 183, 127, 0.025)');

        let is_ok_color = css.getPropertyValue('--gray-300');
        let is_ok_gradient = monitor_logs_chart.createLinearGradient(0, 0, 0, 250);
        is_ok_gradient.addColorStop(0, 'rgba(37, 45, 60, .1)');
        is_ok_gradient.addColorStop(1, 'rgba(37, 45, 60, 0.025)');

        /* Display chart */
        new Chart(monitor_logs_chart, {
            type: 'line',
            data: {
                labels: <?= $data->monitor_logs_chart['labels'] ?>,
                datasets: [{
                        label: <?= json_encode(l('monitor.response_time_label')) ?>,
                        data: <?= $data->monitor_logs_chart['response_time'] ?? '[]' ?>,
                        backgroundColor: response_time_gradient,
                        borderColor: response_time_color,
                        fill: true
                    },
                    {
                        label: <?= json_encode(l('monitor.is_ok_label')) ?>,
                        data: <?= $data->monitor_logs_chart['is_ok'] ?? '[]' ?>,
                        backgroundColor: is_ok_gradient,
                        borderColor: is_ok_color,
                        fill: true
                    }
                ]
            },
            options: chart_options
        });
    <?php endif ?>
</script>
<script>
    $(document).ready(function() {

        var object_type = 'monitor';
        var object_id = <?php echo $data->monitor->monitor_id ?>;
        var user_id = <?php echo $data->monitor->user_id ?>;
        var get_url = '<?php echo url('tags/getTags') ?>';
        var delete_url = '<?php echo url('tags/deleteTags') ?>';
        var search_url = '<?php echo url('tags/searchTags') ?>';
        var save_url = '<?php echo url('tags/saveTags') ?>';
        
        getTags(get_url, object_type, object_id, user_id);

        $(document).on('click', '.tag-remove', function() {
            var tag_name = $(this).parent('span').attr('tag_name');
            deleteTag(delete_url, object_type, object_id, tag_name);
            getTags(get_url, object_type, object_id, user_id);
        });

        $(document).on('input', '#tag-search', function() {
            var search_string = $(this).val();
            if (search_string == '') {
                getTags(get_url, object_type, object_id, user_id);
            } else {
                searchTag(search_url, object_type, object_id, search_string);
            }
        });

        $(document).on('click', '.available-tags', function() {
            var tag_name = $(this).text();
            saveTag(save_url, object_type, object_id, tag_name);
            getTags(get_url, object_type, object_id, user_id);
        });
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
