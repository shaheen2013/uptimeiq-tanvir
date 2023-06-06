<?php defined('ALTUMCODE') || die() ?>

<div class="app-sidebar">
    <div class="app-sidebar-title text-truncate">
        <a href="<?= url() ?>" data-logo data-light-value="<?= settings()->main->logo_light != '' ? \Altum\Uploads::get_full_url('logo_light') . settings()->main->logo_light : settings()->main->title ?>" data-light-class="<?= settings()->main->logo_light != '' ? 'img-fluid navbar-logo' : '' ?>" data-dark-value="<?= settings()->main->logo_dark != '' ? \Altum\Uploads::get_full_url('logo_dark') . settings()->main->logo_dark : settings()->main->title ?>" data-dark-class="<?= settings()->main->logo_dark != '' ? 'img-fluid navbar-logo' : '' ?>">
            <?php if (settings()->main->{'logo_' . \Altum\ThemeStyle::get()} != '') : ?>
                <img src="<?= \Altum\Uploads::get_full_url('logo_' . \Altum\ThemeStyle::get()) . settings()->main->{'logo_' . \Altum\ThemeStyle::get()} ?>" class="img-fluid navbar-logo" alt="<?= l('global.accessibility.logo_alt') ?>" />
            <?php else : ?>
                <?= settings()->main->title ?>
            <?php endif ?>
        </a>
    </div>

    <div class="overflow-auto flex-grow-1">
        <ul class="app-sidebar-links">
            <?php if (\Altum\Authentication::check()) : ?>
                <li class="<?= \Altum\Router::$controller == 'Dashboard' ? 'active' : null ?>">
                    <a href="<?= url('dashboard') ?>"><i class="fa fa-fw fa-sm fa-th mr-2"></i> <?= l('dashboard.menu') ?></a>
                </li>

                <li class="<?= \Altum\Router::$controller == 'Monitors' ? 'active' : null ?>">
                    <a href="<?= url('monitors') ?>"><i class="fa fa-fw fa-sm fa-server mr-2"></i> <?= l('monitors.menu') ?></a>
                </li>

                <li class="<?= \Altum\Router::$controller == 'Heartbeats' ? 'active' : null ?>">
                    <a href="<?= url('heartbeats') ?>"><i class="fa fa-fw fa-sm fa-heartbeat mr-2"></i> <?= l('heartbeats.menu') ?></a>
                </li>

                <!-- threshold sidebar entry  -->
                <li class="<?= \Altum\Router::$controller == 'Thresholds' ? 'active' : null ?>">
                    <a href="<?= url('thresholds') ?>"><i class="fa fa-fw fa-sm fa-tachometer-alt mr-2"></i> <?= l('thresholds.menu') ?></a>
                </li>

                <li class="<?= \Altum\Router::$controller == 'DomainNames' ? 'active' : null ?>">
                    <a href="<?= url('domain-names') ?>"><i class="fa fa-fw fa-sm fa-network-wired mr-2"></i> <?= l('domain_names.menu') ?></a>
                </li>

                <li class="<?= \Altum\Router::$controller == 'StatusPages' ? 'active' : null ?>">
                    <a href="<?= url('status-pages') ?>"><i class="fa fa-fw fa-sm fa-wifi mr-2"></i> <?= l('status_pages.menu') ?></a>
                </li>

                <li class="<?= \Altum\Router::$controller == 'Projects' ? 'active' : null ?>">
                    <a href="<?= url('projects') ?>"><i class="fa fa-fw fa-sm fa-project-diagram mr-2"></i> <?= l('projects.menu') ?></a>
                </li>

                <?php if (settings()->status_pages->domains_is_enabled) : ?>
                    <li class="<?= \Altum\Router::$controller == 'Domains' ? 'active' : null ?>">
                        <a href="<?= url('domains') ?>"><i class="fa fa-fw fa-sm fa-globe mr-2"></i> <?= l('domains.menu') ?></a>
                    </li>
                <?php endif ?>

                <li class="<?= \Altum\Router::$controller == 'NotificationHandlers' ? 'active' : null ?>">
                    <a href="<?= url('notification-handlers') ?>"><i class="fa fa-fw fa-sm fa-bell mr-2"></i> <?= l('notification_handlers.menu') ?></a>
                </li>
            <?php endif ?>

            <?php if (settings()->tools->is_enabled && (settings()->tools->access == 'everyone' || (settings()->tools->access == 'users' && \Altum\Authentication::check()))) : ?>
                <li class="<?= \Altum\Router::$controller == 'Tools' ? 'active' : null ?>">
                    <a href="<?= url('tools') ?>"><i class="fa fa-fw fa-sm fa-tools mr-2"></i> <?= l('tools.menu') ?></a>
                </li>
            <?php endif ?>

            <?php foreach ($data->pages as $data) : ?>
                <li>
                    <a href="<?= $data->url ?>" target="<?= $data->target ?>"><?= $data->title ?></a>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

    <?php if (\Altum\Authentication::check()) : ?>

        <ul class="app-sidebar-links">

            <?php if (\Altum\Plugin::is_active('teams')) : ?>

                <?php if (\Altum\Teams::invitation(\Altum\Teams::get_main_user()->user_id) != 0) : ?>
                    <li>
                        <a class="dropdown-item" href="<?php
                                                        if (\Altum\Authentication::is_sub_admin()) {
                                                            echo url('sub-admin/team-invitation');
                                                        } elseif (\Altum\Authentication::is_admin()) {
                                                            echo url('admin/team-invitation');
                                                        } else {
                                                            echo url('teams');
                                                        } ?> "><i class="fa-solid fa-user-tag mr-2"></i> Team Invitation <?php if (\Altum\Teams::invitation(\Altum\Teams::get_main_user()->user_id) != 0) : ?>
                                <span class="badge badge-warning ml-1"> <?= \Altum\Teams::invitation(\Altum\Teams::get_main_user()->user_id); ?></span>
                            <?php endif ?></a>
                    </li>

                <?php endif ?>

                <?php if (\Altum\Teams::team_joined_count(\Altum\Teams::get_main_user()->user_id) > 1) { ?>
                    <li class="dropdown dropup">
                        <a href="#" class="dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-solid fa-users mr-1"></i>Teams
                        </a>

                        <div class="dropdown-menu" style="width: 90%;">
                            <?php
                            $teams = \Altum\Teams::team_joined($_SESSION['user_id']);
                            $token = \Altum\Csrf::get();
                            foreach ($teams as $key => $value) { ?>
                            
                                <a class="dropdown-item <?= $value->team_member_id == \Altum\Teams::$team_member->team_member_id ? 'active' : '' ?>" href="<?= url("teams-members/login?team_member_id=$value->team_member_id&token=$token") ?>"><?= $value->name ?></a>

                            <?php } ?>
                        </div>
                    </li>

                <?php } ?>

            <?php endif ?>
        </ul>

        <ul class="app-sidebar-links">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="d-flex align-items-center app-sidebar-footer-block">
                        <img src="<?= get_gravatar(\Altum\Teams::get_main_user()->email) ?>" class="app-sidebar-avatar mr-3" loading="lazy" />

                        <div class="app-sidebar-footer-text d-flex flex-column text-truncate">
                            <span class="text-truncate"><?= \Altum\Teams::get_main_user()->name ?></span>
                            <small class="text-truncate"><?= \Altum\Teams::get_main_user()->email ?></small>
                            <?php if (\Altum\Authentication::check() && \Altum\Teams::is_delegated()) : ?>
                                    <div style="white-space: normal; font-size: 12px;" class="">Logged in via <strong><?= \Altum\Teams::$team->name ?></strong></div>
                                <?php endif ?>
                        </div>
                    </div>
                </a>

                <div class="dropdown-menu dropdown-menu-right">
                    <?php if (\Altum\Authentication::is_admin() || \Altum\Authentication::is_sub_admin()) : ?>
                        <a target="_self" class="dropdown-item" href="<?= \Altum\Authentication::is_admin() ? url('admin') : url('sub-admin') ?>"><i class="fa fa-fw fa-sm fa-fingerprint mr-2"></i> <?= \Altum\Authentication::is_admin() ? "Site-admin" : l('global.menu.admin') ?></a>
                        <div class="dropdown-divider"></div>
                    <?php endif ?>

                    <a class="dropdown-item" href="<?= url('account') ?>"><i class="fa fa-fw fa-sm fa-wrench mr-2"></i> <?= l('account.menu') ?></a>


                    <a class="dropdown-item" href="<?= url('account-plan') ?>"><i class="fa fa-fw fa-sm fa-box-open mr-2"></i> <?= l('account_plan.menu') ?></a>

                    <?php if (settings()->payment->is_enabled) : ?>
                        <a class="dropdown-item" href="<?= url('account-payments') ?>"><i class="fa fa-fw fa-sm fa-dollar-sign mr-2"></i> <?= l('account_payments.menu') ?></a>

                        <?php if (settings()->affiliate->is_enabled) : ?>
                            <a class="dropdown-item" href="<?= url('referrals') ?>"><i class="fa fa-fw fa-sm fa-wallet mr-2"></i> <?= l('referrals.menu') ?></a>
                        <?php endif ?>
                    <?php endif ?>

                    <a class="dropdown-item" href="<?= url('account-api') ?>"><i class="fa fa-fw fa-sm fa-code mr-2"></i> <?= l('account_api.menu') ?></a>

                    <a class="dropdown-item" href="<?= url('logout') ?>"><i class="fa fa-fw fa-sm fa-sign-out-alt mr-2"></i> <?= l('global.menu.logout') ?></a>
                </div>
            </li>
        </ul>

    <?php else : ?>

        <ul class="app-sidebar-links">
            <li>
                <a class="nav-link" href="<?= url('login') ?>"><i class="fa fa-fw fa-sm fa-sign-in-alt mr-2"></i> <?= l('login.menu') ?></a>
            </li>

            <?php if (settings()->users->register_is_enabled) : ?>
                <li><a class="nav-link" href="<?= url('register') ?>"><i class="fa fa-fw fa-sm fa-user-plus mr-2"></i> <?= l('register.menu') ?></a></li>
            <?php endif ?>
        </ul>

    <?php endif ?>
</div>