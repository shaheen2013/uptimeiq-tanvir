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
            <li class="active" aria-current="page"><?= l('status_page_update.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h4 text-truncate mb-0"><?= sprintf(l('status_page_update.header'), $data->status_page->name) ?></h1>

        <div class="d-flex align-items-center col-auto p-0">
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

            <?= include_view(THEME_PATH . 'views/status-page/status_page_dropdown_button.php', ['id' => $data->status_page->status_page_id, 'resource_name' => $data->status_page->name]) ?>
        </div>
    </div>

    <p class="text-truncate">
        <a href="<?= $data->status_page->full_url ?>" target="_blank">
            <i class="fa fa-fw fa-sm fa-external-link-alt text-muted mr-1"></i> <?= remove_url_protocol_from_url($data->status_page->full_url) ?>
        </a>
    </p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <?php if(count($data->domains) && (settings()->status_pages->domains_is_enabled || settings()->status_pages->additional_domains_is_enabled)): ?>
                    <div class="form-group">
                        <label for="domain_id"><i class="fa fa-fw fa-sm fa-globe text-muted mr-1"></i> <?= l('status_page.input.domain_id') ?></label>
                        <select id="domain_id" name="domain_id" class="form-control">
                            <?php if(settings()->status_pages->main_domain_is_enabled || \Altum\Authentication::is_admin()): ?>
                                <option value="" <?= $data->status_page->domain_id ? null : 'selected="selected"' ?>><?= remove_url_protocol_from_url(SITE_URL) . 's/' ?></option>
                            <?php endif ?>

                            <?php foreach($data->domains as $row): ?>
                                <option value="<?= $row->domain_id ?>" data-type="<?= $row->type ?>" <?= $data->status_page->domain_id && $data->status_page->domain_id == $row->domain_id ? 'selected="selected"' : null ?>><?= remove_url_protocol_from_url($row->url) ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('status_page.input.domain_id_help') ?></small>
                    </div>

                    <div id="is_main_status_page_wrapper" class="form-group custom-control custom-switch">
                        <input id="is_main_status_page" name="is_main_status_page" type="checkbox" class="custom-control-input" <?= $data->status_page->domain_id && $data->domains[$data->status_page->domain_id]->status_page_id == $data->status_page->status_page_id ? 'checked="checked"' : null ?>>
                        <label class="custom-control-label" for="is_main_status_page"><?= l('status_page.input.is_main_status_page') ?></label>
                        <small class="form-text text-muted"><?= l('status_page.input.is_main_status_page_help') ?></small>
                    </div>

                    <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                        <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                            <div class="form-group">
                                <label for="url"><i class="fa fa-fw fa-sm fa-bolt text-muted mr-1"></i> <?= l('status_page.input.url') ?></label>
                                <input type="text" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->status_page->url ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" placeholder="<?= l('status_page.input.url_placeholder') ?>" />
                                <?= \Altum\Alerts::output_field_error('url') ?>
                                <small class="form-text text-muted"><?= l('status_page.input.url_help') ?></small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div <?= $this->user->plan_settings->custom_url_is_enabled ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                        <div class="<?= $this->user->plan_settings->custom_url_is_enabled ? null : 'container-disabled' ?>">
                            <label for="url"><i class="fa fa-fw fa-sm fa-bolt text-muted mr-1"></i> <?= l('status_page.input.url') ?></label>
                            <div class="mb-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><?= remove_url_protocol_from_url(SITE_URL) . 's/' ?></span>
                                    </div>
                                    <input type="text" id="url" name="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" value="<?= $data->status_page->url ?>" onchange="update_this_value(this, get_slug)" onkeyup="update_this_value(this, get_slug)" placeholder="<?= l('status_page.input.url_placeholder') ?>" />
                                    <?= \Altum\Alerts::output_field_error('url') ?>
                                </div>
                                <small class="form-text text-muted"><?= l('status_page.input.url_help') ?></small>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <label for="name"><i class="fa fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('status_page.input.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->status_page->name ?>" maxlength="256" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fa fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('status_page.input.description') ?></label>
                    <input type="text" id="description" name="description" class="form-control" value="<?= $data->status_page->description ?>" maxlength="256" />
                    <small class="form-text text-muted"><?= l('status_page.input.description_help') ?></small>
                </div>


                <div class="form-group">
                    <div class="d-flex flex-column flex-xl-row justify-content-between">
                        <label for="monitors_ids"><i class="fa fa-fw fa-sm fa-server text-muted mr-1"></i> <?= l('status_page.input.monitors_ids') ?></label>
                        <a href="<?= url('monitor-create') ?>" target="_blank" class="small mb-2"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                    </div>
                    <select id="monitors_ids" name="monitors_ids[]" class="form-control" multiple="multiple">
                        <?php foreach($data->monitors as $monitor): ?>
                            <option value="<?= $monitor->monitor_id ?>" <?= in_array($monitor->monitor_id, $data->status_page->monitors_ids)  ? 'selected="selected"' : null ?>>
                                <?= $monitor->name . ' - ' . $monitor->target ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <small class="form-text text-muted"><?= l('status_page.input.monitors_ids_help') ?></small>
                </div>

                <button class="btn btn-sm btn-block <?= \Altum\Alerts::has_field_errors(['logo', 'favicon']) ? 'btn-outline-danger' : 'btn-outline-blue-500' ?> my-3" type="button" data-toggle="collapse" data-target="#customizations_container" aria-expanded="false" aria-controls="customizations_container">
                    <i class="fa fa-fw fa-paint-brush fa-sm mr-1"></i> <?= l('status_page.input.customizations') ?>
                </button>

                <div class="collapse" id="customizations_container">
                    <div class="form-group">
                        <label for="logo"><i class="fa fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('status_page.input.logo') ?></label>
                        <?php if(!empty($data->status_page->logo)): ?>
                            <div class="row">
                                <div class="m-1 col-6 col-xl-3">
                                    <img src="<?= UPLOADS_FULL_URL . 'status_pages_logos/' . $data->status_page->logo ?>" class="img-fluid" loading="lazy" />
                                </div>
                            </div>
                            <div class="custom-control custom-checkbox my-2">
                                <input id="logo_remove" name="logo_remove" type="checkbox" class="custom-control-input" onchange="this.checked ? document.querySelector('#logo').classList.add('d-none') : document.querySelector('#logo').classList.remove('d-none')">
                                <label class="custom-control-label" for="logo_remove">
                                    <span class="text-muted"><?= l('global.delete_file') ?></span>
                                </label>
                            </div>
                        <?php endif ?>
                        <input id="logo" type="file" name="logo" accept="<?= \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_logos') ?>" class="form-control-file altum-file-input <?= \Altum\Alerts::has_field_errors('logo') ? 'is-invalid' : null ?>" />
                        <?= \Altum\Alerts::output_field_error('logo') ?>
                        <small class="form-text text-muted"><?= l('status_page.input.logo_help') ?> <?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_logos')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->status_pages->logo_size_limit) ?></small>
                    </div>

                    <div class="form-group">
                        <label for="favicon"><i class="fa fa-fw fa-sm fa-clone text-muted mr-1"></i> <?= l('status_page.input.favicon') ?></label>
                        <?php if(!empty($data->status_page->favicon)): ?>
                            <div class="row">
                                <div class="m-1 col-6 col-xl-3">
                                    <img src="<?= UPLOADS_FULL_URL . 'status_pages_favicons/' . $data->status_page->favicon ?>" class="img-fluid" loading="lazy" />
                                </div>
                            </div>
                            <div class="custom-control custom-checkbox my-2">
                                <input id="favicon_remove" name="favicon_remove" type="checkbox" class="custom-control-input" onchange="this.checked ? document.querySelector('#favicon').classList.add('d-none') : document.querySelector('#favicon').classList.remove('d-none')">
                                <label class="custom-control-label" for="favicon_remove">
                                    <span class="text-muted"><?= l('global.delete_file') ?></span>
                                </label>
                            </div>
                        <?php endif ?>
                        <input id="favicon" type="file" name="favicon" accept="<?= \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_favicons') ?>" class="form-control-file altum-file-input <?= \Altum\Alerts::has_field_errors('favicon') ? 'is-invalid' : null ?>" />
                        <?= \Altum\Alerts::output_field_error('favicon') ?>
                        <small class="form-text text-muted"><?= l('status_page.input.favicon_help') ?> <?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_favicons')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), settings()->status_pages->favicon_size_limit) ?></small>
                    </div>

                    <?php $fonts = require APP_PATH . 'includes/s/fonts.php'; ?>

                    <div class="form-group">
                        <label for="font_family"><i class="fa fa-fw fa-pen-nib fa-sm mr-1"></i> <?= l('status_page.input.font_family') ?></label>
                        <select id="font_family" name="font_family" class="form-control">
                            <?php foreach($fonts as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $data->status_page->settings->font_family == $key ? 'selected="selected"' : null?>><?= $value['name'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="font_size"><i class="fa fa-fw fa-font fa-sm mr-1"></i> <?= l('status_page.input.font_size') ?></label>
                        <div class="input-group">
                            <input id="font_size" type="number" min="14" max="22" name="font_size" class="form-control" value="<?= $data->status_page->settings->font_size ?>" />
                            <div class="input-group-append">
                                <span class="input-group-text">px</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 my-3" type="button" data-toggle="collapse" data-target="#socials_container" aria-expanded="false" aria-controls="socials_container">
                    <i class="fa fa-fw fa-share-alt fa-sm mr-1"></i> <?= l('status_page.input.socials') ?>
                </button>

                <div class="collapse" id="socials_container">
                    <div>
                        <?php foreach(require APP_PATH . 'includes/s/socials.php' as $key => $value): ?>
                            <div class="form-group">
                                <label for="socials_<?= $key ?>"><i class="<?= $value['icon'] ?> fa-fw fa-sm text-muted mr-1"></i> <?= l('status_page.input.' . $key) ?></label>
                                <div class="input-group">
                                    <?php if($value['input_display_format']): ?>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><?= str_replace('%s', '', $value['format']) ?></span>
                                        </div>
                                    <?php endif ?>
                                    <input id="socials_<?= $key ?>" type="text" class="form-control" name="socials[<?= $key ?>]" placeholder="<?= l('status_page.input.' . $key . '_placeholder') ?>" value="<?= $data->status_page->socials->{$key} ?? '' ?>" maxlength="<?= $value['max_length'] ?>" />
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <button class="btn btn-sm btn-block <?= \Altum\Alerts::has_field_errors(['opengraph']) ? 'btn-outline-danger' : 'btn-outline-blue-500' ?> my-3" type="button" data-toggle="collapse" data-target="#seo_container" aria-expanded="false" aria-controls="seo_container">
                    <i class="fa fa-fw fa-search-plus fa-sm mr-1"></i> <?= l('status_page.input.seo') ?>
                </button>

                <div class="collapse" id="seo_container">
                    <div <?= $this->user->plan_settings->search_engine_block_is_enabled ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                        <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->search_engine_block_is_enabled ? null : 'container-disabled' ?>">
                            <input id="is_se_visible" name="is_se_visible" type="checkbox" class="custom-control-input" <?= $data->status_page->is_se_visible ? 'checked="checked"' : null?> <?= $this->user->plan_settings->search_engine_block_is_enabled ? null : 'disabled="disabled"' ?>>
                            <label class="custom-control-label" for="is_se_visible"><?= l('status_page.input.is_se_visible') ?></label>
                            <small class="form-text text-muted"><?= l('status_page.input.is_se_visible_help') ?></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title"><i class="fa fa-fw fa-heading fa-sm text-muted mr-1"></i> <?= l('status_page.input.title') ?></label>
                        <input id="title" type="text" class="form-control" name="title" value="<?= $data->status_page->settings->title ?? '' ?>" maxlength="70" />
                        <small class="form-text text-muted"><?= l('status_page.input.title_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="meta_description"><i class="fa fa-fw fa-paragraph fa-sm text-muted mr-1"></i> <?= l('status_page.input.meta_description') ?></label>
                        <input id="meta_description" type="text" class="form-control" name="meta_description" value="<?= $data->status_page->settings->meta_description ?? '' ?>" maxlength="160" />
                        <small class="form-text text-muted"><?= l('status_page.input.meta_description_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="meta_keywords"><i class="fa fa-fw fa-file-word fa-sm text-muted mr-1"></i> <?= l('status_page.input.meta_keywords') ?></label>
                        <input id="meta_keywords" type="text" class="form-control" name="meta_keywords" value="<?= $data->status_page->settings->meta_keywords ?? '' ?>" maxlength="160" />
                    </div>

                    <div class="form-group">
                        <label for="opengraph"><i class="fa fa-fw fa-sm fa-image text-muted mr-1"></i> <?= l('status_page.input.opengraph') ?></label>
                        <?php if(!empty($data->status_page->opengraph)): ?>
                            <div class="row">
                                <div class="m-1 col-6 col-xl-3">
                                    <img src="<?= UPLOADS_FULL_URL . 'status_pages_opengraph/' . $data->status_page->opengraph ?>" class="img-fluid" loading="lazy" />
                                </div>
                            </div>
                            <div class="custom-control custom-checkbox my-2">
                                <input id="opengraph_remove" name="opengraph_remove" type="checkbox" class="custom-control-input" onchange="this.checked ? document.querySelector('#opengraph').classList.add('d-none') : document.querySelector('#opengraph').classList.remove('d-none')">
                                <label class="custom-control-label" for="opengraph_remove">
                                    <span class="text-muted"><?= l('global.delete_file') ?></span>
                                </label>
                            </div>
                        <?php endif ?>
                        <input id="opengraph" type="file" name="opengraph" accept="<?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('status_pages_opengraph')) ?>" class="form-control-file altum-file-input <?= \Altum\Alerts::has_field_errors('opengraph') ? 'is-invalid' : null ?>" />
                        <?= \Altum\Alerts::output_field_error('opengraph') ?>
                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-outline-blue-500 my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fa fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('status_page.input.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group">
                        <div class="d-flex flex-column flex-xl-row justify-content-between">
                            <label for="project_id"><i class="fa fa-fw fa-sm fa-project-diagram text-muted mr-1"></i> <?= l('projects.project_id') ?></label>
                            <a href="<?= url('project-create') ?>" target="_blank" class="small mb-2"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('projects.create') ?></a>
                        </div>
                        <select id="project_id" name="project_id" class="form-control">
                            <option value=""><?= l('global.none') ?></option>
                            <?php foreach($data->projects as $project_id => $project): ?>
                                <option value="<?= $project_id ?>" <?= $data->status_page->project_id == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('projects.project_id_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="timezone"><i class="fa fa-fw fa-sm fa-clock text-muted mr-1"></i> <?= l('status_page.input.timezone') ?></label>
                        <select id="timezone" name="timezone" class="form-control">
                            <?php foreach(DateTimeZone::listIdentifiers() as $timezone) echo '<option value="' . $timezone . '" ' . ($data->status_page->timezone == $timezone ? 'selected="selected"' : null) . '>' . $timezone . '</option>' ?>
                        </select>
                        <small class="form-text text-muted"><?= l('status_page.input.timezone_help') ?></small>
                    </div>

                    <div <?= $this->user->plan_settings->password_protection_is_enabled ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                        <div class="form-group <?= $this->user->plan_settings->password_protection_is_enabled ? null : 'container-disabled' ?>">
                            <label for="password"><i class="fa fa-fw fa-sm fa-lock text-muted mr-1"></i> <?= l('status_page.input.password') ?></label>
                            <input type="password" id="password" name="password" class="form-control" value="<?= $data->status_page->password ?>" autocomplete="new-password" />
                        </div>
                    </div>

                    <div <?= $this->user->plan_settings->removable_branding_is_enabled ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                        <div class="form-group custom-control custom-switch <?= $this->user->plan_settings->removable_branding_is_enabled ? null : 'container-disabled' ?>">
                            <input id="is_removed_branding" name="is_removed_branding" type="checkbox" class="custom-control-input" <?= $data->status_page->is_removed_branding ? 'checked="checked"' : null?> <?= $this->user->plan_settings->removable_branding_is_enabled ? null : 'disabled="disabled"' ?>>
                            <label class="custom-control-label" for="is_removed_branding"><?= l('status_page.input.is_removed_branding') ?></label>
                            <small class="form-text text-muted"><?= l('status_page.input.is_removed_branding_help') ?></small>
                        </div>
                    </div>

                    <div <?= $this->user->plan_settings->custom_css_is_enabled ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                        <div class="form-group <?= $this->user->plan_settings->custom_css_is_enabled ? null : 'container-disabled' ?>">
                            <label for="custom_css"><i class="fa fa-fw fa-sm fa-code text-muted mr-1"></i> <?= l('status_page.input.custom_css') ?></label>
                            <textarea id="custom_css" class="form-control" name="custom_css" maxlength="8192"><?= $data->status_page->custom_css ?></textarea>
                        </div>
                    </div>

                    <div <?= $this->user->plan_settings->custom_js_is_enabled ? null : 'data-toggle="tooltip" title="' . l('global.info_message.plan_feature_no_access') . '"' ?>>
                        <div class="form-group <?= $this->user->plan_settings->custom_js_is_enabled ? null : 'container-disabled' ?>">
                            <label for="custom_js"><i class="fa fa-fw fa-sm fa-code text-muted mr-1"></i> <?= l('status_page.input.custom_js') ?></label>
                            <textarea id="custom_js" class="form-control" name="custom_js" maxlength="8192"><?= $data->status_page->custom_js ?></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php ob_start() ?>
<script>
    'use strict';

    /* Is main status_page handler */
    let is_main_status_page_handler = () => {
        if(document.querySelector('#is_main_status_page').checked) {
            document.querySelector('#url').setAttribute('disabled', 'disabled');
        } else {
            document.querySelector('#url').removeAttribute('disabled');
        }
    }

    document.querySelector('#is_main_status_page') && document.querySelector('#is_main_status_page').addEventListener('change', is_main_status_page_handler);

    /* Domain Id Handler */
    let domain_id_handler = () => {
        let domain_id = document.querySelector('select[name="domain_id"]').value;

        if(document.querySelector(`select[name="domain_id"] option[value="${domain_id}"]`).getAttribute('data-type') == '0') {
            document.querySelector('#is_main_status_page_wrapper').classList.remove('d-none');
        } else {
            document.querySelector('#is_main_status_page_wrapper').classList.add('d-none');
            document.querySelector('#is_main_status_page').checked = false;
        }

        is_main_status_page_handler();
    }

    domain_id_handler();

    document.querySelector('select[name="domain_id"]') && document.querySelector('select[name="domain_id"]').addEventListener('change', domain_id_handler);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
