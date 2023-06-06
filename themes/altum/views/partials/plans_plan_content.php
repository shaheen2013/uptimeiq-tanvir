<?php defined('ALTUMCODE') || die() ?>

<?php $monitor_check_intervals = require APP_PATH . 'includes/monitor_check_intervals.php'; ?>

<?php if(settings()->status_pages->additional_domains_is_enabled): ?>
    <?php $additional_domains = (new \Altum\Models\Domain())->get_available_additional_domains(); ?>
<?php endif ?>

<div class="">
    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <?= sprintf(l('global.plan_settings.monitors_limit'), '<strong>' . ($data->plan_settings->monitors_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->monitors_limit)) . '</strong>') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.monitors_check_intervals'), implode(', ', array_values(array_intersect_key($monitor_check_intervals, array_flip($data->plan_settings->monitors_check_intervals ?? []))))) ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->monitors_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <?= sprintf(l('global.plan_settings.heartbeats_limit'), '<strong>' . ($data->plan_settings->heartbeats_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->heartbeats_limit)) . '</strong>') ?>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->heartbeats_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <?= sprintf(l('global.plan_settings.domain_names_limit'), '<strong>' . ($data->plan_settings->domain_names_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->domain_names_limit)) . '</strong>') ?>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->domain_names_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <?= sprintf(l('global.plan_settings.status_pages_limit'), '<strong>' . ($data->plan_settings->status_pages_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->status_pages_limit)) . '</strong>') ?>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->status_pages_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <?= sprintf(l('global.plan_settings.projects_limit'), '<strong>' . ($data->plan_settings->projects_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->projects_limit)) . '</strong>') ?>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->projects_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
    </div>

    <?php if(settings()->status_pages->domains_is_enabled): ?>
        <div class="d-flex justify-content-between align-items-center my-3">
            <div>
                <?= sprintf(l('global.plan_settings.domains_limit'), '<strong>' . ($data->plan_settings->domains_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->domains_limit)) . '</strong>') ?>
            </div>

            <i class="fa fa-fw fa-sm <?= $data->plan_settings->domains_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        </div>
    <?php endif ?>

    <?php if(settings()->status_pages->additional_domains_is_enabled): ?>
        <div class="d-flex justify-content-between align-items-center my-3 <?= count($data->plan_settings->additional_domains ?? []) ? null : 'text-muted' ?>">
            <div>
                <?= sprintf(l('global.plan_settings.additional_domains'), '<strong>' . nr(count($data->plan_settings->additional_domains ?? [])) . '</strong>') ?>
                <span class="mr-1" data-toggle="tooltip" title="<?= sprintf(l('global.plan_settings.additional_domains_help'), implode(', ', array_map(function($domain_id) use($additional_domains) { return $additional_domains[$domain_id]->host ?? null; }, $data->plan_settings->additional_domains ?? []))) ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
            </div>

            <i class="fa fa-fw fa-sm <?= count($data->plan_settings->additional_domains ?? []) ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
        </div>
    <?php endif ?>

    <?php if(\Altum\Plugin::is_active('teams')): ?>
        <div class="d-flex justify-content-between align-items-center my-3">
            <div>
                <?= sprintf(l('global.plan_settings.teams_limit'), '<strong>' . ($data->plan_settings->teams_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->teams_limit)) . '</strong>') ?>
            </div>

            <i class="fa fa-fw fa-sm <?= $data->plan_settings->teams_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        </div>

        <div class="d-flex justify-content-between align-items-center my-3">
            <div>
                <?= sprintf(l('global.plan_settings.team_members_limit'), '<strong>' . ($data->plan_settings->team_members_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->team_members_limit)) . '</strong>') ?>
            </div>

            <i class="fa fa-fw fa-sm <?= $data->plan_settings->team_members_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        </div>
    <?php endif ?>

    <?php if(settings()->affiliate->is_enabled): ?>
        <div class="d-flex justify-content-between align-items-center my-3">
            <div>
                <?= sprintf(l('global.plan_settings.affiliate_commission_percentage'), '<strong>' . nr($data->plan_settings->affiliate_commission_percentage) . '%</strong>') ?>
            </div>

            <i class="fa fa-fw fa-sm <?= $data->plan_settings->affiliate_commission_percentage ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        </div>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <?= sprintf(l('global.plan_settings.logs_retention'), '<strong>' . ($data->plan_settings->logs_retention == -1 ? l('global.unlimited') : nr($data->plan_settings->logs_retention)) . '</strong>') ?>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->logs_retention ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <?= sprintf(l('global.plan_settings.statistics_retention'), '<strong>' . ($data->plan_settings->statistics_retention == -1 ? l('global.unlimited') : nr($data->plan_settings->statistics_retention)) . '</strong>') ?>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->statistics_retention ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
    </div>

    <?php ob_start() ?>
    <div class='d-flex flex-column'>
        <?php foreach(require APP_PATH . 'includes/notification_handlers.php' as $notification_handler): ?>
            <span class='my-1'><?= sprintf(l('global.plan_settings.notification_handlers_' . $notification_handler . '_limit'), '<strong>' . ($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'} == -1 ? l('global.unlimited') : nr($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'})) . '</strong>') ?></span>
        <?php endforeach ?>
    </div>
    <?php $html = ob_get_clean() ?>

    <div class="d-flex justify-content-between align-items-center my-3">
        <div>
            <?= l('global.plan_settings.notification_handlers_limit') ?>
            <span class="mr-1" data-toggle="tooltip" data-html="true" title="<?= $html ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm fa-check-circle text-success"></i>
    </div>


    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->analytics_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.analytics_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.analytics_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->analytics_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->qr_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.qr_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.qr_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->qr_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->password_protection_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.password_protection_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.password_protection_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->password_protection_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->removable_branding_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.removable_branding_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.removable_branding_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->removable_branding_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->custom_url_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.custom_url_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.custom_url_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->custom_url_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->search_engine_block_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.search_engine_block_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.search_engine_block_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->search_engine_block_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->custom_css_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.custom_css_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.custom_css_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->custom_css_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->custom_js_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.custom_js_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.custom_js_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->custom_js_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <?php if(settings()->monitors_heartbeats->email_reports_is_enabled): ?>
        <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->email_reports_is_enabled ? null : 'text-muted' ?>">
            <div>
                <?= l('global.plan_settings.email_reports_is_enabled') ?>
                <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.email_reports_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
            </div>

            <i class="fa fa-fw fa-sm <?= $data->plan_settings->email_reports_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
        </div>
    <?php endif ?>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->api_is_enabled ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.api_is_enabled') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.api_is_enabled_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->api_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center my-3 <?= $data->plan_settings->no_ads ? null : 'text-muted' ?>">
        <div>
            <?= l('global.plan_settings.no_ads') ?>
            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.plan_settings.no_ads_help') ?>"><i class="fa fa-fw fa-xs fa-question-circle text-gray-500"></i></span>
        </div>

        <i class="fa fa-fw fa-sm <?= $data->plan_settings->no_ads ? 'fa-check-circle text-success' : 'fa-times-circle' ?>"></i>
    </div>
</div>
