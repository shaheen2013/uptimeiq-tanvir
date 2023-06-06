<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('monitors') ?>"><?= l('monitors.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li>
                <a href="<?= url('monitor/' . $data->monitor->monitor_id) ?>"><?= l('monitor.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('monitor_logs.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="card bg-blue-900 border-0">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div>
                    <?php if($data->monitor->is_enabled): ?>
                        <?php if($data->monitor->is_ok): ?>
                            <div data-toggle="tooltip" title="<?= l('monitor.is_ok') ?>">
                                <i class="fa fa-fw fa-check-circle fa-3x text-primary-400"></i>
                            </div>
                        <?php else: ?>
                            <div data-toggle="tooltip" title="<?= l('monitor.is_not_ok') ?>">
                                <i class="fa fa-fw fa-sm fa-times-circle fa-3x text-danger"></i>
                            </div>
                        <?php endif ?>
                    <?php else: ?>
                        <div data-toggle="tooltip" title="<?= l('monitor.is_enabled_paused') ?>">
                            <i class="fa fa-fw fa-sm fa-pause-circle fa-3x text-warning"></i>
                        </div>
                    <?php endif ?>
                </div>

                <div class="ml-3">
                    <div class="d-flex align-items-center">
                        <h1 class="h3 text-truncate text-white mb-0 mr-2"><?= sprintf(l('monitor_logs.header'), $data->monitor->name) ?></h1>

                        <?= include_view(THEME_PATH . 'views/monitor/monitor_dropdown_button.php', ['id' => $data->monitor->monitor_id, 'resource_name' => $data->monitor->name]) ?>
                    </div>

                    <div class="text-gray-400">
                        <span><?= $data->monitor->target ?><?= $data->monitor->port ? ':' . $data->monitor->port : null ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$data->monitor->total_checks): ?>
        <div class="d-flex flex-column align-items-center justify-content-center mt-4">
            <img src="<?= ASSETS_FULL_URL . 'images/processing.svg' ?>" class="col-10 col-md-7 col-lg-5 mb-3" alt="<?= l('monitor.no_data') ?>" />
            <h2 class="h4 text-muted"><?= l('monitor.no_data') ?></h2>
            <p class="text-muted"><?= sprintf(l('monitor.no_data_help'), $data->monitor->name) ?></p>
        </div>
    <?php endif ?>

    <?php if($data->monitor->total_checks): ?>

        <div class="d-flex justify-content-end mt-4">
            <div class="d-flex">
                <button
                        id="daterangepicker"
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-min-date="<?= \Altum\Date::get($data->monitor->datetime, 4) ?>"
                        data-max-date="<?= \Altum\Date::get('', 4) ?>"
                >
                    <i class="fa fa-fw fa-calendar mr-lg-1"></i>
                    <span class="d-none d-lg-inline-block">
                        <?php if($data->date->start_date == $data->date->end_date): ?>
                            <?= \Altum\Date::get($data->date->start_date, 2, \Altum\Date::$default_timezone) ?>
                        <?php else: ?>
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
                            <a href="<?= url('monitor-logs/' . $data->monitor->monitor_id . '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                                <i class="fa fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                            </a>
                            <a href="<?= url('monitor-logs/' . $data->monitor->monitor_id . '?start_date=' . $data->date->start_date . '&end_date=' . $data->date->end_date . '&export=json') ?>" target="_blank" class="dropdown-item">
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

                <div class="ml-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm <?= count($data->filters->get) ? 'btn-outline-primary' : 'btn-outline-secondary' ?> filters-button dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport"><i class="fa fa-fw fa-sm fa-filter"></i></button>

                        <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                            <div class="dropdown-header d-flex justify-content-between">
                                <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                                <?php if(count($data->filters->get)): ?>
                                    <a href="<?= url('monitor-logs/' . $data->monitor->monitor_id) ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                                <?php endif ?>
                            </div>

                            <div class="dropdown-divider"></div>

                            <form action="" method="get" role="form">
                                <div class="form-group px-4">
                                    <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                                    <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                    <select name="search_by" id="filters_search_by" class="form-control form-control-sm">
                                        <option value="response_status_code" <?= $data->filters->search_by == 'response_status_code' ? 'selected="selected"' : null ?>><?= l('monitor_logs.filters.search_by_response_status_code') ?></option>
                                    </select>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_is_ok" class="small"><?= l('monitor_logs.filters.status') ?></label>
                                    <select name="is_ok" id="filters_is_ok" class="form-control form-control-sm">
                                        <option value=""><?= l('global.filters.all') ?></option>
                                        <option value="1" <?= isset($data->filters->filters['is_ok']) && $data->filters->filters['is_ok'] == '1' ? 'selected="selected"' : null ?>><?= l('monitor_logs.filters.is_ok') ?></option>
                                        <option value="0" <?= isset($data->filters->filters['is_ok']) && $data->filters->filters['is_ok'] == '0' ? 'selected="selected"' : null ?>><?= l('monitor_logs.filters.is_not_ok') ?></option>
                                    </select>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_ping_server_id" class="small"><?= l('monitor_logs.filters.ping_server_id') ?></label>
                                    <select name="ping_server_id" id="filters_ping_server_id" class="form-control form-control-sm">
                                        <option value=""><?= l('global.filters.all') ?></option>
                                        <?php foreach($data->ping_servers as $ping_server_id => $ping_server): ?>
                                            <option value="<?= $ping_server_id ?>" <?= isset($data->filters->filters['ping_server_id']) && $data->filters->filters['ping_server_id'] == $ping_server_id ? 'selected="selected"' : null ?>><?= $ping_server->city_name . ' (' . $ping_server->country_code . ')' ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                    <select name="order_by" id="filters_order_by" class="form-control form-control-sm">
                                        <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                        <option value="response_time" <?= $data->filters->order_by == 'response_time' ? 'selected="selected"' : null ?>><?= l('monitor_logs.filters.order_by_response_time') ?></option>
                                    </select>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                    <select name="order_type" id="filters_order_type" class="form-control form-control-sm">
                                        <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                        <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                    </select>
                                </div>

                                <div class="form-group px-4">
                                    <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                    <select name="results_per_page" id="filters_results_per_page" class="form-control form-control-sm">
                                        <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                            <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="form-group px-4 mt-4">
                                    <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th colspan="4"><?= l('monitor.checks.header') ?></th>
                        </tr>
                        <tr>
                            <th colspan="2"><?= l('monitor.checks.status') ?></th>
                            <th><?= l('monitor.checks.response_time') ?></th>
                            <?php if($data->monitor->type == 'website'): ?>
                            <th><?= l('monitor.checks.response_status_code') ?></th>
                            <?php endif ?>
                            <th><?= l('monitor.checks.datetime') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!count($data->monitor_logs)): ?>
                        <tr>
                            <td colspan="4" class="text-muted"><?= l('monitor.checks.no_data') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data->monitor_logs as $monitor_log): ?>

                            <tr>
                                <td class="text-nowrap">
                                    <?php if($monitor_log->is_ok): ?>
                                        <i class="fa fa-fw fa-sm fa-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="fa fa-fw fa-sm fa-times-circle text-danger"></i>
                                    <?php endif ?>

                                    <?php if($data->monitor->type == 'website' && !$monitor_log->is_ok): ?>
                                        <?php
                                        $monitor_log->error = json_decode($monitor_log->error ?? '');
                                        if(isset($monitor_log->error->type)) {
                                            if ($monitor_log->error->type == 'exception') {
                                                $error = $monitor_log->error->message;
                                            } elseif (in_array($monitor_log->error->type, ['response_status_code', 'response_body', 'response_header'])) {
                                                $error = l('monitor.checks.error.' . $monitor_log->error->type);
                                            }
                                        }
                                        ?>

                                        <span class="ml-3" data-toggle="tooltip" title="<?= $error ?>">
                                            <i class="fa fa-fw fa-sm fa-envelope-open-text text-muted"></i>
                                        </span>
                                    <?php endif ?>

                                </td>

                                <td class="text-nowrap">
                                    <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->ping_servers[$monitor_log->ping_server_id]->country_code) . '.svg' ?>" class="img-fluid icon-favicon" data-toggle="tooltip" title="<?= get_country_from_country_code($data->ping_servers[$monitor_log->ping_server_id]->country_code). ', ' . $data->ping_servers[$monitor_log->ping_server_id]->city_name ?>" />
                                </td>

                                <td class="text-nowrap">
                                    <?= display_response_time($monitor_log->response_time) ?>
                                </td>

                                <?php if($data->monitor->type == 'website'): ?>
                                    <td class="text-nowrap"><?= $monitor_log->response_status_code ?></td>
                                <?php endif ?>

                                <td class="text-nowrap">
                                    <span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($monitor_log->datetime, 1) ?>">
                                        <?= \Altum\Date::get_timeago($monitor_log->datetime) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach ?>
                    <?php endif ?>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>

    <?php endif ?>

</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css' ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js' ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js' ?>"></script>

<script>
    'use strict';

    moment.tz.setDefault(<?= json_encode($this->user->timezone) ?>);

    /* Daterangepicker */
    $('#daterangepicker').daterangepicker({
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
        redirect(`<?= url('monitor-logs/' . $data->monitor->monitor_id) ?>&start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

    });

</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
