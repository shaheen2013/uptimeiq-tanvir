<?php
if(
    !empty(settings()->ads->header_status_pages)
    && !$this->status_page_user->plan_settings->no_ads
    && \Altum\Router::$controller_settings['ads']
): ?>
    <div class="container my-3"><?= settings()->ads->header_status_pages ?></div>
<?php endif ?>
