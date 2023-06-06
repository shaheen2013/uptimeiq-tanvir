<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" href="#" data-toggle="dropdown" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple">
        <i class="fa fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('domain-name-update/' . $data->id) ?>"><i class="fa fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#domain_name_delete_modal" data-domain-name-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fa fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'domain_name',
    'resource_id' => 'domain_name_id',
    'has_dynamic_resource_name' => true,
    'path' => 'domain-name/delete'
]), 'modals'); ?>
