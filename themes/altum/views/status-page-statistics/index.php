<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('status-pages') ?>"><?= l('status_pages.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li>
                <?= l('status_page.breadcrumb') ?><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('status_page_statistics.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><?= sprintf(l('status_page_statistics.header'), $data->status_page->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
            <div data-toggle="tooltip" title="<?= l('global.reset') ?>">
                <button
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="modal"
                        data-target="#status_page_statistics_reset_modal"
                        aria-label="<?= l('global.reset') ?>"
                        data-status-page-id="<?= $data->status_page->status_page_id ?>"
                        data-start-date="<?= $data->datetime['start_date'] ?>"
                        data-end-date="<?= $data->datetime['end_date'] ?>"
                >
                    <i class="fa fa-fw fa-sm fa-redo"></i>
                </button>
            </div>

            <div>
                <button
                        id="url_copy"
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="tooltip"
                        title="<?= l('global.clipboard_copy') ?>"
                        aria-label="<?= l('global.clipboard_copy') ?>"
                        data-copy="<?= l('global.clipboard_copy') ?>"
                        data-copied="<?= l('global.clipboard_copied') ?>"
                        data-clipboard-text="<?= $data->status_page->full_url ?>"
                >
                    <i class="fa fa-fw fa-sm fa-copy"></i>
                </button>
            </div>

            <div>
                <button
                        id="daterangepicker"
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-min-date="<?= \Altum\Date::get($data->status_page->datetime, 4) ?>"
                        data-max-date="<?= \Altum\Date::get('', 4) ?>"
                >
                    <i class="fa fa-fw fa-calendar mr-lg-1"></i>
                    <span class="d-none d-lg-inline-block">
                        <?php if($data->datetime['start_date'] == $data->datetime['end_date']): ?>
                            <?= \Altum\Date::get($data->datetime['start_date'], 2, \Altum\Date::$default_timezone) ?>
                        <?php else: ?>
                            <?= \Altum\Date::get($data->datetime['start_date'], 2, \Altum\Date::$default_timezone) . ' - ' . \Altum\Date::get($data->datetime['end_date'], 2, \Altum\Date::$default_timezone) ?>
                        <?php endif ?>
                    </span>
                    <i class="fa fa-fw fa-caret-down d-none d-lg-inline-block ml-lg-1"></i>
                </button>
            </div>

            <?= include_view(THEME_PATH . 'views/status-page/status_page_dropdown_button.php', ['id' => $data->status_page->status_page_id, 'resource_name' => $data->status_page->name]) ?>
        </div>
    </div>

    <p class="text-truncate">
        <a href="<?= $data->status_page->full_url ?>" target="_blank">
            <i class="fa fa-fw fa-sm fa-external-link-alt text-muted mr-1"></i> <?= remove_url_protocol_from_url($data->status_page->full_url) ?>
        </a>
    </p>

    <?php if(!$data->has_data): ?>
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-4 mb-3" alt="<?= l('status_page_statistics.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('status_page_statistics.no_data') ?></h2>
                    <p class="text-muted"><?= l('status_page_statistics.no_data_help') ?></p>
                </div>
            </div>
        </div>
    <?php else: ?>

    <div class="row mb-lg-3">
        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'overview' ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=overview&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-list mr-1"></i>
                <?= l('status_page_statistics.statistics.overview') ?>
            </a>
        </div>

        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'entries' ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=entries&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-chart-bar mr-1"></i>
                <?= l('status_page_statistics.statistics.entries') ?>
            </a>
        </div>

        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'country' ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=country&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-globe mr-1"></i>
                <?= l('status_page_statistics.statistics.country') ?>
            </a>
        </div>

        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'city_name' ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=city_name&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-city mr-1"></i>
                <?= l('status_page_statistics.statistics.city_name') ?>
            </a>
        </div>

        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= in_array($data->type, ['referrer_host', 'referrer_path']) ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=referrer_host&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-random mr-1"></i>
                <?= l('status_page_statistics.statistics.referrer_host') ?>
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'device' ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=device&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-laptop mr-1"></i>
                <?= l('status_page_statistics.statistics.device') ?>
            </a>
        </div>

        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'os' ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=os&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-server mr-1"></i>
                <?= l('status_page_statistics.statistics.os') ?>
            </a>
        </div>

        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'browser' ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=browser&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-window-restore mr-1"></i>
                <?= l('status_page_statistics.statistics.browser') ?>
            </a>
        </div>

        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= $data->type == 'language' ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=language&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-language mr-1"></i>
                <?= l('status_page_statistics.statistics.language') ?>
            </a>
        </div>

        <div class="col-lg mb-2 mb-lg-0 text-truncate">
            <a class="btn btn-block btn-custom text-truncate <?= in_array($data->type, ['utm_source', 'utm_medium', 'utm_campaign']) ? 'active' : null ?>" href="<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=utm_source&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>">
                <i class="fa fa-fw fa-sm fa-link mr-1"></i>
                <?= l('status_page_statistics.statistics.utms') ?>
            </a>
        </div>
    </div>

        <?= $this->views['statistics'] ?>

    <?php endif ?>

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
            startDate: <?= json_encode($data->datetime['start_date']) ?>,
            endDate: <?= json_encode($data->datetime['end_date']) ?>,
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
            redirect(`<?= url('status-page-statistics/' . $data->status_page->status_page_id . '?type=' . $data->type) ?>&start_date=${start.format('YYYY-MM-DD')}&end_date=${end.format('YYYY-MM-DD')}`, true);

        });
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/statistics_reset_modal.php', ['modal_id' => 'status_page_statistics_reset_modal', 'resource_id' => 'status_page_id', 'path' => 'status-page-statistics/reset']), 'modals'); ?>

