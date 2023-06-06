<?php defined('ALTUMCODE') || die() ?>
<!DOCTYPE html>
<html lang="<?= \Altum\Language::$code ?>" dir="<?= l('direction') ?>">

<head>
    <title><?= \Altum\Title::get() ?></title>
    <base href="<?= SITE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <?php if (\Altum\Meta::$description) : ?>
        <meta name="description" content="<?= \Altum\Meta::$description ?>" />
    <?php endif ?>
    <?php if (\Altum\Meta::$keywords) : ?>
        <meta name="keywords" content="<?= \Altum\Meta::$keywords ?>" />
    <?php endif ?>

    <?php if (!settings()->main->se_indexing) : ?>
        <meta name="robots" content="noindex">
    <?php endif ?>

    <link rel="alternate" href="<?= SITE_URL . \Altum\Router::$original_request ?>" hreflang="x-default" />
    <?php if (count(\Altum\Language::$active_languages) > 1) : ?>
        <?php foreach (\Altum\Language::$active_languages as $language_name => $language_code) : ?>
            <?php if (settings()->main->default_language != $language_name) : ?>
                <link rel="alternate" href="<?= SITE_URL . $language_code . '/' . \Altum\Router::$original_request ?>" hreflang="<?= $language_code ?>" />
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>

    <?php if (!empty(settings()->main->favicon)) : ?>
        <link href="<?= UPLOADS_FULL_URL . 'main/' . settings()->main->favicon ?>" rel="shortcut icon" />
    <?php endif ?>
    <!-- sumoselect -->
    <link rel="stylesheet" href="<?= ASSETS_FULL_URL ?>css/sumoselect.min.css">
    <!-- font-awesome -->
    <link href="<?= ASSETS_FULL_URL . 'css/fontawesome-free-6.3.0_all.min.css' ?>" rel="stylesheet">

    <link href="<?= ASSETS_FULL_URL . 'css/' . \Altum\ThemeStyle::get_file() . '?v=' . PRODUCT_CODE ?>" id="css_theme_style" rel="stylesheet" media="screen,print">
    <?php foreach (['custom.css'] as $file) : ?>
        <link href="<?= ASSETS_FULL_URL . 'css/' . $file . '?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
    <?php endforeach ?>

    <?= \Altum\Event::get_content('head') ?>

    <?php if (!empty(settings()->custom->head_js)) : ?>
        <?= settings()->custom->head_js ?>
    <?php endif ?>

    <?php if (!empty(settings()->custom->head_css)) : ?>
        <style>
            <?= settings()->custom->head_css ?>
        </style>
    <?php endif ?>
</head>

<body class="<?= l('direction') == 'rtl' ? 'rtl' : null ?> app <?= \Altum\ThemeStyle::get() == 'dark' ? 'c_darkmode' : null ?>" data-theme-style="<?= \Altum\ThemeStyle::get() ?>">
    <div id="app_overlay" class="app-overlay" style="display: none"></div>

    <div class="app-container">
        <?= $this->views['app_sidebar'] ?>

        <section class="app-content">
            <?php //ALTUMCODE:DEMO if(DEMO) echo include_view(THEME_PATH . 'views/partials/ac_banner.php', ['demo_url' => 'https://66uptime.com/demo/', 'product_name' => PRODUCT_NAME, 'product_url' => PRODUCT_URL]) 
            ?>

            <?php require THEME_PATH . 'views/partials/admin_impersonate_user.php' ?>
            <?php require THEME_PATH . 'views/partials/team_delegate_access.php' ?>
            <?php require THEME_PATH . 'views/partials/announcements.php' ?>
            <?php require THEME_PATH . 'views/partials/cookie_consent.php' ?>

            <?= $this->views['app_menu'] ?>

            <div class="py-3 p-lg-5">
                <?php require THEME_PATH . 'views/partials/ads_header.php' ?>

                <main class="altum-animate altum-animate-fill-none altum-animate-fade-in">
                    <?= $this->views['content'] ?>
                </main>

                <?php require THEME_PATH . 'views/partials/ads_footer.php' ?>
            </div>

            <div class="py-3 p-lg-5">
                <div class="container d-print-none">
                    <footer class="app-footer">
                        <?= $this->views['footer'] ?>
                    </footer>
                </div>
            </div>

        </section>
    </div>

    <?= \Altum\Event::get_content('modals') ?>

    <?php require THEME_PATH . 'views/partials/js_global_variables.php' ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php foreach (['libraries/jquery.min.js', 'libraries/popper.min.js', 'libraries/bootstrap.min.js', 'custom.js', 'libraries/fontawesome.min.js', 'libraries/fontawesome-solid.min.js', 'libraries/fontawesome-brands.modified.js'] as $file) : ?>
        <script src="<?= ASSETS_FULL_URL ?>js/<?= $file ?>?v=<?= PRODUCT_CODE ?>"></script>
    <?php endforeach ?>


    <?= \Altum\Event::get_content('javascript') ?>

    <script>
        let toggle_app_sidebar = () => {
            /* Open sidebar menu */
            let body = document.querySelector('body');
            body.classList.toggle('app-sidebar-opened');

            /* Toggle overlay */
            let app_overlay = document.querySelector('#app_overlay');
            app_overlay.style.display == 'none' ? app_overlay.style.display = 'block' : app_overlay.style.display = 'none';

            /* Change toggle button content */
            let button = document.querySelector('#app_menu_toggler');

            if (body.classList.contains('app-sidebar-opened')) {
                button.innerHTML = `<i class="fa fa-fw fa-times"></i>`;
            } else {
                button.innerHTML = `<i class="fa fa-fw fa-bars"></i>`;
            }
        };

        /* Toggler for the sidebar */
        document.querySelector('#app_menu_toggler').addEventListener('click', event => {
            event.preventDefault();

            toggle_app_sidebar();

            let app_sidebar_is_opened = document.querySelector('body').classList.contains('app-sidebar-opened');

            if (app_sidebar_is_opened) {
                document.querySelector('#app_overlay').removeEventListener('click', toggle_app_sidebar);
                document.querySelector('#app_overlay').addEventListener('click', toggle_app_sidebar);
            } else {
                document.querySelector('#app_overlay').removeEventListener('click', toggle_app_sidebar);
            }
        });
    </script>
    <!-- sumoselect -->
    <script src="<?= ASSETS_FULL_URL ?>js/jquery.sumoselect.min.js"></script>

    <script>
        $('#filters_tag').SumoSelect({
            csvDispCount: 6,
            search: true,
            searchText: 'Select tags',
            okCancelInMulti: true,
        });
    </script>

    <script>
        

        $(".member_invite").submit(function(e) {
            e.preventDefault();
            var user_email = $('#user_email').val();
            var team_id = $('#team_id').val();
            var access = $('#access').val();

            $(".error").html('');
            var validation = true;
            if (user_email.length < 1) {
                $('.user_email_error').html('Email field required!');
                validation = false;
            }
            if (team_id == null) {
                $('.team_id_error').html('Select a team!');
                validation = false;
            }
            if (access == null) {
                $('.access_error').html('Select a role!');
                validation = false;
            }

            if (validation) {
                var data = {
                    'user_email': user_email,
                    'team_id': team_id,
                    'access': access
                }
                $.ajax({
                    type: "POST",
                    url: "<?= url('team-member-create/memberInvite') ?>",
                    data: data,
                    dataType: "json",
                    success: function(response) {
                        if (response == 'invalid_user') {
                            Toast.fire({
                                icon: 'error',
                                title: 'Invalid user!'
                            })
                        } else if (response != null) {
                            Toast.fire({
                                icon: 'success',
                                title: 'User Invited!'
                            })
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'User can not invited!'
                            })
                        }

                    }
                });
            }
        });
    </script>
    <!-- sumoselect -->
    <script src="<?= ASSETS_FULL_URL ?>js/jquery.sumoselect.min.js"></script>
        
    <script>
        $('#filters_tag').SumoSelect({
            csvDispCount: 6, 
            search: true, 
            searchText: 'Select tags',
            okCancelInMulti:true,
        });
    </script>
    <script src="<?= ASSETS_FULL_URL . 'js/views.js' ?>"></script>
</body>
</html>
