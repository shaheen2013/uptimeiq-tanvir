<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li><a href="<?= url() ?>"><?= l('index.breadcrumb') ?></a> <i class="fa fa-fw fa-angle-right"></i></li>
            <li class="active" aria-current="page"><?= l('api_documentation.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4"><?= l('api_documentation.header') ?></h1>
    <p class="text-muted"><?= l('api_documentation.subheader') ?></p>

    <div class="card mb-5">
        <div class="card-body">
            <div class="mb-5">
                <?php if(\Altum\Authentication::check()): ?>
                    <div class="form-group">
                        <label for="api_key"><?= l('api_documentation.api_key') ?></label>
                        <input type="text" id="api_key" value="<?= $this->user->api_key ?>" class="form-control" onclick="this.select();" readonly="readonly" />
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <a href="<?= url('account-api') ?>" target="_blank" class="btn btn-block btn-outline-primary"><?= l('api_documentation.api_key') ?></a>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <label for="base_url"><?= l('api_documentation.base_url') ?></label>
                    <input type="text" id="base_url" value="<?= SITE_URL . 'api' ?>" class="form-control" onclick="this.select();" readonly="readonly" />
                </div>
            </div>

            <div class="mb-4">
                <h2 class="h5"><?= l('api_documentation.authentication.header') ?></h2>
                <p class="text-muted"><?= l('api_documentation.authentication.subheader') ?></p>
            </div>

            <div class="form-group">
                <label><?= l('api_documentation.example') ?></label>
                <div class="card bg-gray-50 border-0">
                    <div class="card-body">
                        curl --request GET \<br />
                        --url '<?= SITE_URL . 'api/' ?><span class="text-primary">{endpoint}</span>' \<br />
                        --header 'Authorization: Bearer <span class="text-primary">{api_key}</span>' \
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/user') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-user text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.user.header') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/monitors') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-server text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.monitors.header') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/heartbeats') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-heartbeat text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.heartbeats.header') ?>
                </div>
            </div>
        </div>
        
        <!-- threshold doc click begin -->
        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/thresholds') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-tachometer-alt text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.thresholds.header') ?>
                </div>
            </div>
        </div>
        <!-- threshold doc click end -->
        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/domain-names') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-network-wired text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.domain_names.header') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/notification-handlers') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-bell text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.notification_handlers.header') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/status-pages') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-wifi text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.status_pages.header') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/statistics') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-chart-bar text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.statistics.header') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/projects') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-project-diagram text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.projects.header') ?>
                </div>
            </div>
        </div>

        <?php if(settings()->status_pages->domains_is_enabled): ?>
        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/domains') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-globe text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.domains.header') ?>
                </div>
            </div>
        </div>
        <?php endif ?>

        <?php if(\Altum\Plugin::is_active('teams')): ?>
            <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('api-documentation/teams') ?>" class="stretched-link">
                            <i class="fa fa-fw fa-user-cog text-primary-600"></i>
                        </a>
                    </div>

                    <div class="card-body">
                        <?= l('api_documentation.teams.header') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('api-documentation/team-members') ?>" class="stretched-link">
                            <i class="fa fa-fw fa-users-cog text-primary-600"></i>
                        </a>
                    </div>

                    <div class="card-body">
                        <?= l('api_documentation.team_members.header') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('api-documentation/teams-member') ?>" class="stretched-link">
                            <i class="fa fa-fw fa-user-tag text-primary-600"></i>
                        </a>
                    </div>

                    <div class="card-body">
                        <?= l('api_documentation.teams_member.header') ?>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/payments') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-dollar-sign text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.payments.header') ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4 position-relative">
            <div class="card d-flex flex-row h-100 overflow-hidden">
                <div class="border-right border-gray-100 px-3 d-flex flex-column justify-content-center">
                    <a href="<?= url('api-documentation/users-logs') ?>" class="stretched-link">
                        <i class="fa fa-fw fa-scroll text-primary-600"></i>
                    </a>
                </div>

                <div class="card-body">
                    <?= l('api_documentation.users_logs.header') ?>
                </div>
            </div>
        </div>
    </div>
</div>
