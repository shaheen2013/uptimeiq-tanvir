<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="mb-3 d-flex justify-content-between">
        <div>
            <h1 class="h4 mb-0 text-truncate"><?= l('dashboard.header') ?></h1>
        </div>
    </div>

    <div class="my-4">
        <div class="d-flex align-items-center mb-3">
            <h2 class="h6 text-uppercase text-muted mb-0 mr-3"><i class="fa fa-fw fa-sm fa-list mr-1"></i> <?= l('dashboard.summary.header') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <div class="row">
	    <?= (new \Altum\View('dashboard/two_column_widget', (array) $this))->run([
		    'url' => 'monitors',
		    'header' => l('index.monitors.header'),
		    'col1title' => l('dashboard.total'),
		    'col2title' => l('dashboard.total_alerts'),
		    'total' => $data->monitors_total,
		    'alerts' => $data->monitors_down
	    ]) ?>

	    <?= (new \Altum\View('dashboard/two_column_widget', (array) $this))->run([
		    'url' => 'heartbeats',
		    'header' => l('index.heartbeats.header'),
		    'col1title' => l('dashboard.total'),
		    'col2title' => l('dashboard.total_alerts'),
		    'total' => $data->heartbeats_total,
		    'alerts' => $data->heartbeats_down
	    ]) ?>

	    <?= (new \Altum\View('dashboard/two_column_widget', (array) $this))->run([
		    'url' => 'thresholds',
		    'header' => l('index.thresholds.header'),
		    'col1title' => l('dashboard.total'),
		    'col2title' => l('dashboard.total_alerts'),
		    'total' => $data->thresholds_total,
		    'alerts' => $data->thresholds_down
	    ]) ?>
        </div>
    </div>

    <div class="my-4">
        <div class="d-flex align-items-center mb-3">
            <h2 class="h6 text-uppercase text-muted mb-0 mr-3"><i class="fa fa-fw fa-sm fa-cloud mr-1"></i> <?= l('dashboard.cloud.header') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <div class="row">
            <?= (new \Altum\View('dashboard/two_column_widget', (array) $this))->run([
                'url' => 'cloudcost',
                'header' => l('index.cloudcost.header'),
                'col1title' => l('dashboard.daily'),
                'col2title' => l('dashboard.monthly'),
                'total' => "$" . $data->daily_cost,
                'alerts' => "$" . $data->monthly_forecast
            ]) ?>

            <?= (new \Altum\View('dashboard/one_column_widget', (array) $this))->run([
                'url' => 'unusedebs',
                'header' => l('index.cloudebs.header'),
                'total' => $data->unattached_ebs
            ]) ?>

            <?= (new \Altum\View('dashboard/one_column_widget', (array) $this))->run([
                'url' => 'cloudrds',
                'header' => l('index.cloudrds.header'),
                'total' => $data->all_rds
            ]) ?>

            <?= (new \Altum\View('dashboard/two_column_widget', (array) $this))->run([
                'url' => 'health',
                'header' => l('index.cloudec2.header'),
                'col1title' => l('dashboard.total'),
                'col2title' => l('dashboard.running'),
                'total' => $data->all_ec2,
                'alerts' => $data->running_ec2
            ]) ?>
        </div>
    </div>

    <div class="my-4">
        <div class="d-flex align-items-center mb-3">
            <h2 class="h6 text-uppercase text-muted mb-0 mr-3"><i class="fa fa-fw fa-sm fa-wrench mr-1"></i> <?= l('dashboard.maintenance.header') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <div class="row">
            <?= (new \Altum\View('dashboard/one_column_widget', (array) $this))->run([
                'url' => 'unusedebs',
                'header' => l('index.patchage.header'),
                'total' => $data->unattached_ebs
            ]) ?>

            <?= (new \Altum\View('dashboard/two_column_widget', (array) $this))->run([
                'url' => 'cloudcost',
                'header' => l('index.ec2backups.header'),
                'col1title' => l('dashboard.daily'),
                'col2title' => l('dashboard.monthly'),
                'total' => $data->daily_cost,
                'alerts' => $data->monthly_forecast
            ]) ?>

            <?= (new \Altum\View('dashboard/two_column_widget', (array) $this))->run([
                'url' => 'health',
                'header' => l('index.loadbalancers.header'),
                'col1title' => l('dashboard.total'),
                'col2title' => l('dashboard.running'),
                'total' => $data->all_ec2,
                'alerts' => $data->running_ec2
            ]) ?>

            <?= (new \Altum\View('dashboard/one_column_widget', (array) $this))->run([
                'url' => 'cloudrds',
                'header' => l('index.critpatch.header'),
                'total' => $data->all_rds
            ]) ?>
        </div>
    </div>

    <div class="my-4">

        <?php if(!count($data->monitors) && !count($data->heartbeats)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center justify-content-center py-3">
                        <img src="<?= ASSETS_FULL_URL . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-4 mb-3" alt="<?= l('heartbeats.no_data') ?>" />
                        <h2 class="h4 text-muted"><?= l('dashboard.no_data') ?></h2>
                        <p class="text-muted"><?= l('dashboard.no_data_help') ?></p>
                        <div class="ml-3">
                            <a href="<?= url('monitor-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-fw fa-sm fa-plus"></i> <?= l('monitors.create') ?></a>
                            <a href="<?= url('heartbeat-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-fw fa-sm fa-plus"></i> <?= l('heartbeats.create') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>

        <?php if(count($data->monitors)): ?>
        <div class="d-flex align-items-center mb-3">
            <h2 class="h6 text-uppercase text-muted mb-0 mr-3"><i class="fa fa-fw fa-sm fa-server mr-1"></i> <?= l('dashboard.monitors.header') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>

            <div class="ml-3">
                <a href="<?= url('monitor-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-fw fa-sm fa-plus"></i> <?= l('monitors.create') ?></a>
            </div>
        </div>

        <div class="row">
            <?php foreach($data->monitors as $row): ?>
                <?= (new \Altum\View('monitors/monitor_widget', (array) $this))->run(['monitor' => $row, 'projects' => $data->projects]) ?>
            <?php endforeach ?>

            <div class="col-12 col-md-6 col-xl-4 mb-4">
                <div class="card h-100 position-relative">
                    <div class="card-body d-flex justify-content-center align-items-center h-100">
                        <span class="h6 m-0 card-title">
                            <a href="<?= url('monitors') ?>" class="stretched-link text-muted"><?= sprintf(l('dashboard.view_all_amount'), nr($data->monitors_total)) ?></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(count($data->heartbeats)): ?>
    <div class="my-4">
        <div class="d-flex align-items-center mb-3">
            <h2 class="h6 text-uppercase text-muted mb-0 mr-3"><i class="fa fa-fw fa-sm fa-heartbeat mr-1"></i> <?= l('dashboard.heartbeats.header') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>

            <div class="ml-3">
                <a href="<?= url('heartbeat-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-fw fa-sm fa-plus"></i> <?= l('heartbeats.create') ?></a>
            </div>
        </div>

        <div class="row">

            <?php foreach($data->heartbeats as $row): ?>
                <?= (new \Altum\View('heartbeats/heartbeat_widget', (array) $this))->run(['heartbeat' => $row, 'projects' => $data->projects]) ?>
            <?php endforeach ?>

            <div class="col-12 col-md-6 col-xl-4 mb-4">
                <div class="card h-100 position-relative">
                    <div class="card-body d-flex justify-content-center align-items-center h-100">
                        <span class="h6 m-0 card-title">
                            <a href="<?= url('heartbeats') ?>" class="stretched-link text-muted"><?= sprintf(l('dashboard.view_all_amount'), nr($data->heartbeats_total)) ?></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<!-- threshold dashboard entry begin -->
<?php if(count($data->heartbeats)): ?>
    <div class="my-4">
        <div class="d-flex align-items-center mb-3">
            <h2 class="h6 text-uppercase text-muted mb-0 mr-3"><i class="fa fa-fw fa-sm fa-tachometer-alt mr-1"></i> <?= l('dashboard.thresholds.header') ?></h2>
            
            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
            
            <div class="ml-3">
                <a href="<?= url('threshold-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-fw fa-sm fa-plus"></i> <?= l('thresholds.create') ?></a>
            </div>
        </div>
        
        <div class="row">
            
            <?php foreach($data->thresholds as $row): ?>
                <?= (new \Altum\View('thresholds/threshold_widget', (array) $this))->run(['threshold' => $row, 'projects' => $data->projects]) ?>
            <?php endforeach ?>
                
            <div class="col-12 col-md-6 col-xl-4 mb-4">
                <div class="card h-100 position-relative">
                    <div class="card-body d-flex justify-content-center align-items-center h-100">
                        <span class="h6 m-0 card-title">
                            <a href="<?= url('thresholds') ?>" class="stretched-link text-muted"><?= sprintf(l('dashboard.view_all_amount'), nr($data->thresholds_total)) ?></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>
<!-- threshold dashboard entry begin -->
        
<?php if(count($data->monitors)): ?>
    <div class="my-4">
        <div class="d-flex align-items-center mb-3">
            <h2 class="h6 text-uppercase text-muted mb-0 mr-3"><i class="fa fa-fw fa-sm fa-wifi mr-1"></i> <?= l('dashboard.status_pages.header') ?></h2>
            
            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
            
            <div class="ml-3">
                <a href="<?= url('status-page-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-fw fa-sm fa-plus"></i> <?= l('status_pages.create') ?></a>
            </div>
        </div>

        <?php if(count($data->status_pages)): ?>
            <div class="row">

                <?php foreach($data->status_pages as $row): ?>
                    <?= (new \Altum\View('status-pages/status_page_widget', (array) $this))->run(['status_page' => $row, 'projects' => $data->projects]) ?>
                <?php endforeach ?>

                <div class="col-12 col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 position-relative">
                        <div class="card-body d-flex justify-content-center align-items-center h-100">
                            <span class="h6 m-0 card-title">
                                <a href="<?= url('status-pages') ?>" class="stretched-link text-muted"><?= sprintf(l('dashboard.view_all_amount'), nr($data->status_pages_total)) ?></a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <div class="d-flex flex-column align-items-center justify-content-center py-3">
                <h2 class="h4 text-muted"><?= l('status_pages.no_data') ?></h2>
                <p class="text-muted"><?= l('status_pages.no_data_help') ?></p>
            </div>

        <?php endif ?>
    </div>
<?php endif ?>

<?php endif ?>
</div>

