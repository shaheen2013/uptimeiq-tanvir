<?php defined('ALTUMCODE') || die() ?>

<div>
    <?php foreach(['reset', 'monitors', 'heartbeats', 'domain_names', 'monitors_email_reports', 'heartbeats_email_reports'] as $cron): ?>
        <div class="form-group">
            <label for="cron_<?= $cron ?>"><?= l('admin_settings.cron.' . $cron) ?></label>
            <div class="input-group">
                <input id="cron_<?= $cron ?>" name="cron_<?= $cron ?>" type="text" class="form-control" value="<?= '* * * * * wget --quiet -O /dev/null ' . SITE_URL . 'cron/' . $cron . '?key=' . settings()->cron->key ?>" readonly="readonly" />
                <div class="input-group-append">
                    <span class="input-group-text" data-toggle="tooltip" title="<?= sprintf(l('admin_settings.cron.last_execution'), isset(settings()->cron->{$cron . '_datetime'}) ? \Altum\Date::get_timeago(settings()->cron->{$cron . '_datetime'}) : '-') ?>">
                        <i class="fa fa-fw fa-calendar text-muted"></i>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach ?>

    <hr class="border-gray-200 my-5" />

    <h5>BETA</h5>
    <p class="text-muted">
        This is a beta functionality that you can run only via <code>CLI mode</code> & if your host supports <code>pcntl</code> extension.
        <br /> This is intended to run checks in parallel so that a Cron job run is faster and check more monitors in less time.
        <br /> Reach out to me ( support@uptimeiq.com ) if you want to give feedback, would be highly appreciated.
    </p>

    <div class="form-group">
        <label for="cron_monitors"><?= l('admin_settings.cron.monitors') ?></label>
        <div class="input-group">
            <input id="cron_monitors" name="cron_monitors" type="text" class="form-control" value="<?= '* * * * * php -q ' . ROOT_PATH . 'index.php ' . 'cron/monitors ' . 'key=' . settings()->cron->key . ' >/dev/null 2>&1' ?>" readonly="readonly" />
            <div class="input-group-append">
            <span class="input-group-text" data-toggle="tooltip" title="<?= sprintf(l('admin_settings.cron.last_execution'), isset(settings()->cron->{$cron . '_datetime'}) ? \Altum\Date::get_timeago(settings()->cron->{$cron . '_datetime'}) : '-') ?>">
                <i class="fa fa-fw fa-calendar text-muted"></i>
            </span>
            </div>
        </div>
    </div>
</div>
