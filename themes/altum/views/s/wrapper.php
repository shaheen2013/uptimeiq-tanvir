<?php defined('ALTUMCODE') || die() ?>
<!DOCTYPE html>
<html lang="<?= \Altum\Language::$code ?>" dir="<?= l('direction') ?>">
    <head>
        <title><?= \Altum\Title::get() ?></title>
        <base href="<?= SITE_URL ?>">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

        <?php if(\Altum\Meta::$description): ?>
            <meta name="description" content="<?= \Altum\Meta::$description ?>" />
        <?php endif ?>
        <?php if(\Altum\Meta::$keywords): ?>
            <meta name="keywords" content="<?= \Altum\Meta::$keywords ?>" />
        <?php endif ?>

        <?php if(\Altum\Meta::$open_graph['url']): ?>
            <!-- Open Graph / Facebook -->
            <?php foreach(\Altum\Meta::$open_graph as $key => $value): ?>
                <?php if($value): ?>
                    <meta property="og:<?= $key ?>" content="<?= $value ?>" />
                <?php endif ?>
            <?php endforeach ?>
        <?php endif ?>

        <?php if(\Altum\Meta::$twitter['url']): ?>
            <!-- Twitter -->
            <?php foreach(\Altum\Meta::$open_graph as $key => $value): ?>
                <?php if($value): ?>
                    <meta property="twitter:<?= $key ?>" content="<?= $value ?>" />
                <?php endif ?>
            <?php endforeach ?>
        <?php endif ?>

        <?php if(isset($this->status_page) && $this->status_page_user->plan_settings->search_engine_block_is_enabled && !$this->status_page->is_se_visible): ?>
            <meta name="robots" content="noindex">
        <?php endif ?>

        <?php if(isset($this->status_page) && $this->status_page->favicon): ?>
            <link href="<?= UPLOADS_FULL_URL . 'status_pages_favicons/' . $this->status_page->favicon ?>" rel="shortcut icon" />
        <?php else: ?>

            <?php if(!empty(settings()->main->favicon)): ?>
                <link href="<?= UPLOADS_FULL_URL . 'main/' . settings()->main->favicon ?>" rel="shortcut icon" />
            <?php endif ?>

        <?php endif ?>

        <link href="<?= ASSETS_FULL_URL . 'css/' . \Altum\ThemeStyle::get_file() . '?v=' . PRODUCT_CODE ?>" id="css_theme_style" rel="stylesheet" media="screen,print">
        <?php foreach(['status-page-custom.css'] as $file): ?>
            <link href="<?= ASSETS_FULL_URL . 'css/' . $file . '?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
        <?php endforeach ?>

        <?= \Altum\Event::get_content('head') ?>

        <?php if(!empty(settings()->custom->head_js_status_page)): ?>
            <?= settings()->custom->head_js_status_page ?>
        <?php endif ?>

        <?php if(!empty(settings()->custom->head_css_status_page)): ?>
            <style><?= settings()->custom->head_css_status_page ?></style>
        <?php endif ?>

        <?php if(!empty($this->status_page->custom_css) && $this->status_page_user->plan_settings->custom_css_is_enabled): ?>
            <style><?= $this->status_page->custom_css ?></style>
        <?php endif ?>

        <?php if($this->status_page->settings->font_family): ?>
            <?php $fonts = require APP_PATH . 'includes/s/fonts.php' ?>
            <?php if($fonts[$this->status_page->settings->font_family]['font_css_url']): ?>
                <link href="<?= $fonts[$this->status_page->settings->font_family]['font_css_url'] ?>" rel="stylesheet">
            <?php endif ?>

            <?php if($fonts[$this->status_page->settings->font_family]['font-family']): ?>
                <style>html, body {font-family: '<?= $fonts[$this->status_page->settings->font_family]['name'] ?>' !important;}</style>
            <?php endif ?>
        <?php endif ?>
        <style>html {font-size: <?= (int) ($this->status_page->settings->font_size ?? 16) . 'px' ?> !important;}</style>
    </head>

    <body class="<?= l('direction') == 'rtl' ? 'rtl' : null ?> <?= $this->status_page->theme ?>" data-theme-style="<?= \Altum\ThemeStyle::get() ?>">
        <?php require THEME_PATH . 'views/partials/cookie_consent.php' ?>

        <?php require THEME_PATH . 'views/s/partials/ads_header.php' ?>

        <main class="altum-animate altum-animate-fill-none altum-animate-fade-in">
            <?= $this->views['content'] ?>
        </main>

        <?php require THEME_PATH . 'views/s/partials/ads_footer.php' ?>

        <?= $this->views['footer'] ?>

        <?= \Altum\Event::get_content('modals') ?>

        <?php require THEME_PATH . 'views/partials/js_global_variables.php' ?>

        <?php foreach(['custom.js'] as $file): ?>
            <script src="<?= ASSETS_FULL_URL ?>js/<?= $file ?>?v=<?= PRODUCT_CODE ?>"></script>
        <?php endforeach ?>

        <?= \Altum\Event::get_content('javascript') ?>

        <?php if(!empty($this->status_page->custom_js) && $this->status_page_user->plan_settings->custom_js_is_enabled): ?>
            <?= $this->status_page->custom_js ?>
        <?php endif ?>
    </body>
</html>
