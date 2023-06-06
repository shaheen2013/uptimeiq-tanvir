<?php defined('ALTUMCODE') || die() ?>

<div class="col-12 col-md-6 col-xl-4 mb-4">
    <div class="card h-100">
        <div class="card-body d-flex flex-column justify-content-between" style="padding-bottom: 0.5rem;">
            <div class="d-flex justify-content-between mb-3">
                <h2 class="h5 m-0 card-title text-truncate">
                    <a href="<?= url($data->url) ?>"><?= $data->header ?></a>
                </h2>
            </div>

            <div class="d-flex flex-column flex-xl-row justify-content-xl-between mb-0">
                <div class="d-flex flex-column mb-0">
                    <small class="text-muted"><?= $data->col1title ?></small>
                    <span class="font-weight-bold" style="font-size: 1.5rem;">
                        <?= $data->total ?>
                    </span>
                </div>

                <div class="d-flex flex-column mb-0">
                    <small class="text-muted"><?= $data->col2title ?></small>
                    <span class="font-weight-bold" style="font-size: 1.5rem;">
                        <p class="text-right"><?= $data->alerts ?></p>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

