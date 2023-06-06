<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-xl d-flex align-items-center mb-3 mb-xl-0">
            <h1 class="h4 m-0"><?= l('tools.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('tools.subheader') ?>">
                    <i class="fa fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <?php foreach($data->tools as $key => $value): ?>
            <?php if(settings()->tools->available_tools->{$key}): ?>
                <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
                    <div class="card d-flex flex-row h-100 overflow-hidden">
                        <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                            <a href="<?= url('tools/' . $key) ?>" class="stretched-link">
                                <i class="<?= $value['icon'] ?> fa-fw text-primary-600"></i>
                            </a>
                        </div>

                        <div class="card-body">
                            <?= l('tools.' . $key . '.name') ?>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        <?php endforeach ?>
    </div>
</div>

