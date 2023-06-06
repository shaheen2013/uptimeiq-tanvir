<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group">
        <label for="branding"><?= l('admin_settings.status_pages.branding') ?></label>
        <textarea id="branding" name="branding" class="form-control"><?= settings()->status_pages->branding ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.branding_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="domains_is_enabled" name="domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->status_pages->domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="domains_is_enabled"><?= l('admin_settings.status_pages.domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="additional_domains_is_enabled" name="additional_domains_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->status_pages->additional_domains_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="additional_domains_is_enabled"><?= l('admin_settings.status_pages.additional_domains_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.additional_domains_is_enabled_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="main_domain_is_enabled" name="main_domain_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->status_pages->main_domain_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="main_domain_is_enabled"><?= l('admin_settings.status_pages.main_domain_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.main_domain_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_domains"><?= l('admin_settings.status_pages.blacklisted_domains') ?></label>
        <textarea id="blacklisted_domains" class="form-control" name="blacklisted_domains"><?= settings()->status_pages->blacklisted_domains ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.blacklisted_domains_help') ?></small>
    </div>

    <div class="form-group">
        <label for="blacklisted_keywords"><?= l('admin_settings.status_pages.blacklisted_keywords') ?></label>
        <textarea id="blacklisted_keywords" class="form-control" name="blacklisted_keywords"><?= settings()->status_pages->blacklisted_keywords ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.blacklisted_keywords_help') ?></small>
    </div>

    <div class="form-group">
        <label for="logo_size_limit"><?= l('admin_settings.status_pages.logo_size_limit') ?></label>
        <input id="logo_size_limit" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="logo_size_limit" class="form-control" value="<?= settings()->status_pages->logo_size_limit ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.size_limit_help') ?></small>
    </div>

    <div class="form-group">
        <label for="favicon_size_limit"><?= l('admin_settings.status_pages.favicon_size_limit') ?></label>
        <input id="favicon_size_limit" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="favicon_size_limit" class="form-control" value="<?= settings()->status_pages->favicon_size_limit ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.size_limit_help') ?></small>
    </div>

    <div class="form-group">
        <label for="opengraph_size_limit"><?= l('admin_settings.status_pages.opengraph_size_limit') ?></label>
        <input id="opengraph_size_limit" type="number" min="0" max="<?= get_max_upload() ?>" step="any" name="opengraph_size_limit" class="form-control" value="<?= settings()->status_pages->opengraph_size_limit ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.status_pages.size_limit_help') ?></small>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
