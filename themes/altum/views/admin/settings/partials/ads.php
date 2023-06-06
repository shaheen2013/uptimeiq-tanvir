<?php defined('ALTUMCODE') || die() ?>

<div>
    <p class="text-muted"><?= l('admin_settings.ads.ads_help') ?></p>

    <div class="form-group">
        <label for="header"><?= l('admin_settings.ads.header') ?></label>
        <textarea id="header" name="header" class="form-control"><?= settings()->ads->header ?></textarea>
    </div>

    <div class="form-group">
        <label for="footer"><?= l('admin_settings.ads.footer') ?></label>
        <textarea id="footer" name="footer" class="form-control"><?= settings()->ads->footer ?></textarea>
    </div>

    <div class="form-group">
        <label for="header_status_pages"><?= l('admin_settings.ads.header_status_pages') ?></label>
        <textarea id="header_status_pages" name="header_status_pages" class="form-control"><?= settings()->ads->header_status_pages ?></textarea>
    </div>

    <div class="form-group">
        <label for="footer_status_pages"><?= l('admin_settings.ads.footer_status_pages') ?></label>
        <textarea id="footer_status_pages" name="footer_status_pages" class="form-control"><?= settings()->ads->footer_status_pages ?></textarea>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
