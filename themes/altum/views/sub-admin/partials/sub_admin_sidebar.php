<?php defined('ALTUMCODE') || die() ?>

<section class="admin-sidebar">
    <div class="admin-sidebar-title">
        <a href="<?= url() ?>" class="h3 m-0 text-decoration-none text-truncate" data-logo data-light-value="<?= settings()->main->logo_light != '' ? \Altum\Uploads::get_full_url('logo_light') . settings()->main->logo_light : settings()->main->title ?>" data-light-class="<?= settings()->main->logo_light != '' ? 'img-fluid admin-navbar-logo' : 'admin-navbar-brand text-truncate' ?>" data-dark-value="<?= settings()->main->logo_dark != '' ? \Altum\Uploads::get_full_url('logo_dark') . settings()->main->logo_dark : settings()->main->title ?>" data-dark-class="<?= settings()->main->logo_dark != '' ? 'img-fluid admin-navbar-logo' : 'admin-navbar-brand text-truncate' ?>">
            <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()} != ''): ?>
                <img src="<?= \Altum\Uploads::get_full_url('logo_' . \Altum\ThemeStyle::get()) . settings()->main->{'logo_' . \Altum\ThemeStyle::get()} ?>" class="img-fluid admin-navbar-logo" alt="<?= l('global.accessibility.logo_alt') ?>" />
            <?php else: ?>
                <div class="admin-navbar-brand text-truncate"><?= settings()->main->title ?></div>
            <?php endif ?>
        </a>
    </div>

    <div class="admin-sidebar-links-wrapper">
        <ul class="admin-sidebar-links">
            <li class="<?= in_array(\Altum\Router::$controller, ['SubAdminIndex']) ? 'active' : null ?>">
                <a class="nav-link text-truncate" href="<?= url('sub-admin/') ?>">
                    <i class="fa fa-fw fa-sm fa-tv mr-2"></i> <?= l('admin_index.menu') ?>
                </a>
            </li>

            <li class="<?= in_array(\Altum\Router::$controller, ['SubAdminUsers', 'SubAdminUserUpdate', 'SubAdminUserCreate', 'SubAdminUserView']) ? 'active' : null ?>">
                <a class="nav-link text-truncate" href="<?= url('sub-admin/users') ?>">
                    <i class="fa fa-fw fa-sm fa-users mr-2"></i> <?= l('admin_users.menu') ?>
                </a>
            </li>

            <?php if(\Altum\Plugin::is_active('teams')): ?>
                <li class="<?= in_array(\Altum\Router::$controller, ['SubAdminTeams', 'SubAdminTeamCreate', 'SubAdminTeamMemberInvite', 'SubAdminTeamInvitation']) ? 'active' : null ?>">
                    <a class="nav-link text-truncate" href="<?= url('sub-admin/teams') ?>">
                        <i class="fa fa-fw fa-sm fa-user-shield mr-2"></i> <?= l('admin_teams.menu') ?>
                    </a>
                </li>
            <?php endif ?>
        </ul>

        <hr />

        <ul class="admin-sidebar-links">
            <li>
                <a target = "_self" class="nav-link text-truncate" target="_blank" href="<?= url('dashboard') ?>">
                    <i class="fa fa-fw fa-sm fa-home mr-2"></i> <?= l('dashboard.menu') ?>
                </a>
            </li>

            <li class="dropdown">
                <a class="nav-link text-truncate dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">
                    <img src="<?= get_gravatar(\Altum\Teams::get_main_user()->email) ?>" class="admin-avatar mr-2" loading="lazy" />
                    <?= \Altum\Teams::get_main_user()->name?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="<?= url('account') ?>"><i class="fa fa-fw fa-sm fa-wrench mr-2"></i> <?= l('account.menu') ?></a>
                    <a class="dropdown-item" href="<?= url('logout') ?>"><i class="fa fa-fw fa-sm fa-sign-out-alt mr-2"></i> <?= l('global.menu.logout') ?></a>
                </div>
            </li>
        </ul>
    </div>
</section>
