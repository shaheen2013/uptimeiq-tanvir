<?php defined('ALTUMCODE') || die() ?>
<!DOCTYPE html>
<html lang="<?= \Altum\Language::$code ?>" dir="<?= l('direction') ?>" class="w-100 h-100">

<head>
    <title>Admin</title>
    <base href="<?= SITE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

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

    <link href="<?= ASSETS_FULL_URL . 'css/admin-' . \Altum\ThemeStyle::get_file() . '?v=' . PRODUCT_CODE ?>" id="css_theme_style" rel="stylesheet" media="screen,print">
    <?php foreach (['admin-custom.css'] as $file) : ?>
        <link href="<?= ASSETS_FULL_URL ?>css/<?= $file ?>?v=<?= PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
    <?php endforeach ?>

    <?= \Altum\Event::get_content('head') ?>
</head>

<body class="<?= l('direction') == 'rtl' ? 'rtl' : null ?>" data-theme-style="<?= \Altum\ThemeStyle::get() ?>">
    <div id="admin_overlay" class="admin-overlay" style="display: none"></div>

    <div class="admin-container">
        <?= $this->views['sub_admin_sidebar'] ?>

        <section class="admin-content altum-animate altum-animate-fill-none altum-animate-fade-in">
            <?= $this->views['sub_admin_menu'] ?>

            <div class="p-3 p-lg-5 position-relative">
                <?= $this->views['content'] ?>

                <div class="card mt-4">
                    <div class="card-body">
                        <?= $this->views['footer'] ?>
                    </div>
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
        let toggle_admin_sidebar = () => {
            /* Open sidebar menu */
            let body = document.querySelector('body');
            body.classList.toggle('admin-sidebar-opened');

            /* Toggle overlay */
            let admin_overlay = document.querySelector('#admin_overlay');
            admin_overlay.style.display == 'none' ? admin_overlay.style.display = 'block' : admin_overlay.style.display = 'none';

            /* Change toggle button content */
            let button = document.querySelector('#admin_menu_toggler');

            if (body.classList.contains('admin-sidebar-opened')) {
                button.innerHTML = `<i class="fa fa-fw fa-times"></i>`;
            } else {
                button.innerHTML = `<i class="fa fa-fw fa-bars"></i>`;
            }
        };

        /* Toggler for the sidebar */
        document.querySelector('#admin_menu_toggler').addEventListener('click', event => {
            event.preventDefault();

            toggle_admin_sidebar();

            let admin_sidebar_is_opened = document.querySelector('body').classList.contains('admin-sidebar-opened');

            if (admin_sidebar_is_opened) {
                document.querySelector('#admin_overlay').removeEventListener('click', toggle_admin_sidebar);
                document.querySelector('#admin_overlay').addEventListener('click', toggle_admin_sidebar);
            } else {
                document.querySelector('#admin_overlay').removeEventListener('click', toggle_admin_sidebar);
            }
        });
    </script>
    <script>
        $(document).on('click', '#team_add', function() {
            var team_name = $('#team_name').val();
            if (team_name.trim()) {
                var sent_data = {
                    team_name: team_name
                }
                $.ajax({
                    type: "POST",
                    url: "<?= url('team-create/SubAdminTeamCreate') ?>",
                    data: sent_data,
                    dataType: "JSON",
                    success: function(response) {
                        if (response = 'success') {
                            $('#teamCreate').modal('hide');
                            window.location.replace("<?= url('sub-admin/user-create') ?>");
                            Toast.fire({
                                icon: 'success',
                                title: 'Team create successful!'
                            })

                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'Team create unsuccessful!'
                            })
                        }
                    }
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Enter team name!'
                })
            }
        });
    </script>
</body>

</html>