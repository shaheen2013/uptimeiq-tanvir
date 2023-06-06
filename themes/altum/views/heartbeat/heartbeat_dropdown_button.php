<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" href="#" data-toggle="dropdown" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple">
        <i class="fa fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('heartbeat/' . $data->id) ?>"><i class="fa fa-fw fa-sm fa-server mr-2"></i> <?= l('global.view') ?></a>
        <a class="dropdown-item" href="<?= url('heartbeat-update/' . $data->id) ?>"><i class="fa fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#heartbeat_delete_modal" data-heartbeat-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fa fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'heartbeat',
    'resource_id' => 'heartbeat_id',
    'has_dynamic_resource_name' => true,
    'path' => 'heartbeat/delete'
]), 'modals'); ?>
