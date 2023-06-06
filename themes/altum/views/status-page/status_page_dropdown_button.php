<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" href="#" data-toggle="dropdown" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple">
        <i class="fa fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('status-page-redirect/' . $data->id) ?>" target="_blank" rel="noreferrer"><i class="fa fa-fw fa-sm fa-external-link-alt mr-2"></i> <?= l('status_pages.external_url') ?></a>
        <a class="dropdown-item" href="<?= url('status-page-qr/' . $data->id) ?>"><i class="fa fa-fw fa-sm fa-qrcode mr-2"></i> <?= l('status_page_qr.menu') ?></a>
        <a class="dropdown-item" href="<?= url('status-page-statistics/' . $data->id) ?>"><i class="fa fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('status_page_statistics.menu') ?></a>
        <a class="dropdown-item" href="<?= url('status-page-update/' . $data->id) ?>"><i class="fa fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a href="#" data-toggle="modal" data-target="#status_page_delete_modal" data-status-page-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fa fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'status_page',
    'resource_id' => 'status_page_id',
    'has_dynamic_resource_name' => true,
    'path' => 'status-page/delete'
]), 'modals'); ?>
