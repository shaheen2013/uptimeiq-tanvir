<?php defined('ALTUMCODE') || die() ?>

<nav aria-label="breadcrumb">
    <ol class="custom-breadcrumbs small">
        <li>
            <a href="<?= url('admin/ping-servers') ?>"><?= l('admin_ping_servers.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
        </li>
        <li class="active" aria-current="page"><?= l('admin_ping_server_update.breadcrumb') ?></li>
    </ol>
</nav>

<div class="mb-4">
    <div class="d-flex justify-content-between">
        <h1 class="h3 mb-0 text-truncate"><i class="fa fa-fw fa-xs fa-map-marked-alt text-primary-900 mr-2"></i> <?= l('admin_ping_server_update.header') ?></h1>

        <?= include_view(THEME_PATH . 'views/admin/ping-servers/admin_ping_server_dropdown_button.php', ['id' => $data->ping_server->ping_server_id, 'resource_name' => $data->ping_server->name]) ?>
    </div>
</div>

<div class="alert alert-info" role="alert">
    <?= l('admin_ping_server_create.subheader') ?>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="card <?= \Altum\Alerts::has_field_errors() ? 'border-danger' : null ?>">
    <div class="card-body">

        <form action="" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

            <div class="form-group">
                <label for="name"><?= l('admin_ping_servers.main.name') ?></label>
                <input id="name" type="text" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" name="name" value="<?= $data->ping_server->name ?>" required="required" />
                <?= \Altum\Alerts::output_field_error('name') ?>
            </div>

            <div class="form-group">
                <label for="url"><?= l('admin_ping_servers.main.url') ?></label>
                <input id="url" type="url" class="form-control <?= \Altum\Alerts::has_field_errors('url') ? 'is-invalid' : null ?>" name="url" value="<?= $data->ping_server->url ?>" placeholder="<?= l('admin_ping_servers.main.url_placeholder') ?>" <?= $data->ping_server->ping_server_id == 1 ? 'readonly="readonly"' : 'required="required"' ?> />
                <?= \Altum\Alerts::output_field_error('url') ?>
            </div>

            <div class="form-group">
                <label for="country_code"><?= l('admin_ping_servers.main.country_code') ?></label>
                <select id="country_code" name="country_code" class="form-control">
                    <?php foreach(get_countries_array() as $country_code => $country_name): ?>
                        <option value="<?= $country_code ?>" <?= $data->ping_server->country_code == $country_code ? 'selected="selected"' : null ?>><?= $country_name ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group">
                <label for="city_name"><?= l('admin_ping_servers.main.city_name') ?></label>
                <input id="city_name" type="text" class="form-control <?= \Altum\Alerts::has_field_errors('city_name') ? 'is-invalid' : null ?>" name="city_name" value="<?= $data->ping_server->city_name ?>" required="required" />
                <?= \Altum\Alerts::output_field_error('city_name') ?>
                <small class="form-text text-muted"><?= l('admin_ping_servers.main.city_name_help') ?></small>
            </div>

            <div class="form-group">
                <label for="is_enabled"><?= l('admin_ping_servers.main.is_enabled') ?></label>
                <select id="is_enabled" name="is_enabled" class="form-control" <?= $data->ping_server->ping_server_id == 1 ? 'disabled="disabled"' : null ?>>
                    <option value="1" <?= $data->ping_server->is_enabled ? 'selected="selected"' : null ?>><?= l('global.active') ?></option>
                    <option value="0" <?= !$data->ping_server->is_enabled ? 'selected="selected"' : null ?>><?= l('global.disabled') ?></option>
                </select>
            </div>

            <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
        </form>

    </div>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_url.php', [
    'name' => 'ping_server',
    'resource_id' => 'ping_server_id',
    'has_dynamic_resource_name' => true,
    'path' => 'admin/ping-servers/delete/'
]), 'modals'); ?>
