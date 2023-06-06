<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group">
        <label for="email_reports_is_enabled"><i class="fa fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= l('admin_settings.monitors_heartbeats.email_reports_is_enabled') ?></label>
        <select id="email_reports_is_enabled" name="email_reports_is_enabled" class="form-control">
            <option value="0" <?= !settings()->monitors_heartbeats->email_reports_is_enabled ? 'selected="selected"' : null ?>><?= l('global.disabled') ?></option>
            <option value="weekly" <?= settings()->monitors_heartbeats->email_reports_is_enabled == 'weekly' ? 'selected="selected"' : null ?>><?= l('admin_settings.monitors_heartbeats.email_reports_is_enabled_weekly') ?></option>
            <option value="monthly" <?= settings()->monitors_heartbeats->email_reports_is_enabled == 'monthly' ? 'selected="selected"' : null ?>><?= l('admin_settings.monitors_heartbeats.email_reports_is_enabled_monthly') ?></option>
        </select>
        <small class="form-text text-muted"><?= l('admin_settings.monitors_heartbeats.email_reports_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="monitors_ping_method"><?= l('admin_settings.monitors_heartbeats.monitors_ping_method') ?></label>
        <select id="monitors_ping_method" name="monitors_ping_method" class="form-control">
            <option value="exec" <?= settings()->monitors_heartbeats->monitors_ping_method == 'exec' ? 'selected="selected"' : null ?>>exec</option>
            <option value="fsockopen" <?= settings()->monitors_heartbeats->monitors_ping_method == 'fsockopen' ? 'selected="selected"' : null ?>>fsockopen</option>
        </select>
        <small class="form-text text-muted"><?= l('admin_settings.monitors_heartbeats.monitors_ping_method_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="twilio_notifications_is_enabled" name="twilio_notifications_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->monitors_heartbeats->twilio_notifications_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="twilio_notifications_is_enabled"><?= l('admin_settings.monitors_heartbeats.twilio_notifications_is_enabled') ?></label>
    </div>

    <div class="form-group">
        <label for="twilio_sid"><?= l('admin_settings.monitors_heartbeats.twilio_sid') ?></label>
        <input id="twilio_sid" type="text" name="twilio_sid" class="form-control" value="<?= settings()->monitors_heartbeats->twilio_sid ?>" />
    </div>

    <div class="form-group">
        <label for="twilio_token"><?= l('admin_settings.monitors_heartbeats.twilio_token') ?></label>
        <input id="twilio_token" type="text" name="twilio_token" class="form-control" value="<?= settings()->monitors_heartbeats->twilio_token ?>" />
    </div>

    <div class="form-group">
        <label for="twilio_number"><?= l('admin_settings.monitors_heartbeats.twilio_number') ?></label>
        <input id="twilio_number" type="text" name="twilio_number" class="form-control" value="<?= settings()->monitors_heartbeats->twilio_number ?>" />
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
