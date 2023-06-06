<?php defined('ALTUMCODE') || die() ?>

<div class="container d-flex flex-column flex-md-row align-items-md-center my-5">
    <span class="text-muted mb-2 mb-md-0 mr-md-3"><?= l('s_status_page.share') ?></span>

    <a href="mailto:?body=<?= $data->external_url ?>" target="_blank" title="Email" class="btn btn-blue-50 mb-2 mb-md-0 mr-md-3">
        <div class="svg-sm d-flex"><?= include_view(ASSETS_PATH . '/images/s/email.svg') ?></div>
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $data->external_url ?>" target="_blank" title="Facebook" class="btn btn-blue-50 mb-2 mb-md-0 mr-md-3">
        <div class="svg-sm d-flex"><?= include_view(ASSETS_PATH . '/images/s/facebook.svg') ?></div>
    </a>
    <a href="https://twitter.com/share?url=<?= $data->external_url ?>" target="_blank" title="Twitter" class="btn btn-blue-50 mb-2 mb-md-0 mr-md-3">
        <div class="svg-sm d-flex"><?= include_view(ASSETS_PATH . '/images/s/twitter.svg') ?></div>
    </a>
    <a href="https://pinterest.com/pin/create/link/?url=<?= $data->external_url ?>" target="_blank" title="Pinterest" class="btn btn-blue-50 mb-2 mb-md-0 mr-md-3">
        <div class="svg-sm d-flex"><?= include_view(ASSETS_PATH . '/images/s/pinterest.svg') ?></div>
    </a>
    <a href="https://linkedin.com/shareArticle?url=<?= $data->external_url ?>" target="_blank" title="LinkedIn" class="btn btn-blue-50 mb-2 mb-md-0 mr-md-3">
        <div class="svg-sm d-flex"><?= include_view(ASSETS_PATH . '/images/s/linkedin.svg') ?></div>
    </a>
    <a href="https://www.reddit.com/submit?url=<?= $data->external_url ?>" target="_blank" title="Reddit" class="btn btn-blue-50 mb-2 mb-md-0 mr-md-3">
        <div class="svg-sm d-flex"><?= include_view(ASSETS_PATH . '/images/s/reddit.svg') ?></div>
    </a>
    <a href="https://wa.me/?text=<?= $data->external_url ?>" target="_blank" title="Whatsapp" class="btn btn-blue-50 mb-2 mb-md-0 mr-md-3">
        <div class="svg-sm d-flex"><?= include_view(ASSETS_PATH . '/images/s/whatsapp.svg') ?></div>
    </a>
</div>
