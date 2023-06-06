<?php defined('ALTUMCODE') || die() ?>

<div class="col-12 col-md-6 col-xl-2 mb-4">
    <div class="card h-100">
        <div class="card-body d-flex flex-column justify-content-between" style="padding-bottom: 0.5rem;">
            <div class="d-flex justify-content-between mb-0">
                <h2 class="h5 m-0 card-title text-truncate">
                    <a href="<?= url($data->url) ?>"><?= $data->header ?></a>
                </h2>
            </div>

            <div class="d-flex justify-content-center align-items-center mb-0">
                <span class="font-weight-bold" style="font-size: 3rem;">
                    <?= nr($data->total) ?>
                </span>
            </div>
        </div>
    </div>
</div>

