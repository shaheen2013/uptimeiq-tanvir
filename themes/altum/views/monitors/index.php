<?php defined('ALTUMCODE') || die() ?>

<?php
if(isset($_COOKIE['monitor_view'])) {
    if($_COOKIE['monitor_view'] == "list") {
        $view_image = "fa-th";
    } else {
        $view_image = "fa-list";
    }
} else {
    $view_image = "fa-th";
}
?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="row mb-4">
        <div class="col-12 col-xl d-flex align-items-center mb-3 mb-xl-0">
            <h1 class="h4 m-0"><?= l('monitors.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('monitors.subheader') ?>">
                    <i class="fa fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-xl-auto d-flex">
            <div>
                <?php if($this->user->plan_settings->monitors_limit != -1 && $data->total_monitors >= $this->user->plan_settings->monitors_limit): ?>
                    <button type="button" class="btn btn-outline-primary disabled" data-toggle="tooltip" title="<?= l('global.info_message.plan_feature_limit') ?>">
                        <i class="fa fa-fw fa-sm fa-plus"></i> <?= l('monitors.create') ?>
                    </button>
                <?php else: ?>
                    <a href="<?= url('monitor-create') ?>" class="btn btn-outline-primary"><i class="fa fa-fw fa-sm fa-plus"></i> <?= l('monitors.create') ?></a>
                <?php endif ?>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary" id="toggle_view" data-view-style="table" data-tooltip title="<?= l('global.toggle_view') ?>">
                        <i class="fa fa-fw fa-sm <?= $view_image ?>"></i>
                    </button>
                </div>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>">
                        <i class="fa fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('monitors?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                            <i class="fa fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('monitors?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item">
                            <i class="fa fa-fw fa-sm fa-file-code mr-1"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn <?= count($data->filters->get) ? 'btn-outline-primary' : 'btn-outline-secondary' ?> filters-button dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.filters.header') ?>">
                        <i class="fa fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if(count($data->filters->get)): ?>
                                <a href="<?= url('monitors') ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                                <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_tag" class="small">Tags</label>
                                <select name="filters_tag[]" id="filters_tag" class="form-control form-control-sm" multiple>
                                    <?php
                                    foreach ($data->tags as $key => $value) { ?>
                                        <option value="<?= $value ?>"><?= $value ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                <select name="search_by" id="filters_search_by" class="form-control form-control-sm">
                                    <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('monitors.filters.search_by_name') ?></option>
                                    <option value="target" <?= $data->filters->search_by == 'target' ? 'selected="selected"' : null ?>><?= l('monitors.filters.search_by_target') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_is_enabled" class="small"><?= l('global.filters.status') ?></label>
                                <select name="is_enabled" id="filters_is_enabled" class="form-control form-control-sm">
                                    <option value=""><?= l('global.filters.all') ?></option>
                                    <option value="1" <?= isset($data->filters->filters['is_enabled']) && $data->filters->filters['is_enabled'] == '1' ? 'selected="selected"' : null ?>><?= l('global.active') ?></option>
                                    <option value="0" <?= isset($data->filters->filters['is_enabled']) && $data->filters->filters['is_enabled'] == '0' ? 'selected="selected"' : null ?>><?= l('global.disabled') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_type" class="small"><?= l('monitors.filters.type') ?></label>
                                <select name="type" id="filters_type" class="form-control form-control-sm">
                                    <option value=""><?= l('global.filters.all') ?></option>
                                    <option value="website" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'website' ? 'selected="selected"' : null ?>><?= l('monitors.filters.type_website') ?></option>
                                    <option value="ping" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'ping' ? 'selected="selected"' : null ?>><?= l('monitors.filters.type_ping') ?></option>
                                    <option value="port" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'port' ? 'selected="selected"' : null ?>><?= l('monitors.filters.type_port') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <div class="d-flex justify-content-between">
                                    <label for="filters_project_id" class="small"><?= l('projects.project_id') ?></label>
                                    <a href="<?= url('project-create') ?>" target="_blank" class="small"><i class="fa fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                                </div>
                                <select name="project_id" id="filters_project_id" class="form-control form-control-sm">
                                    <option value=""><?= l('global.filters.all') ?></option>
                                    <?php foreach($data->projects as $project_id => $project): ?>
                                        <option value="<?= $project_id ?>" <?= isset($data->filters->filters['project_id']) && $data->filters->filters['project_id'] == $project_id ? 'selected="selected"' : null ?>><?= $project->name ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="form-control form-control-sm">
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="last_check_datetime" <?= $data->filters->order_by == 'last_check_datetime' ? 'selected="selected"' : null ?>><?= l('monitors.filters.order_by_last_check_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('monitors.filters.order_by_name') ?></option>
                                    <option value="uptime" <?= $data->filters->order_by == 'uptime' ? 'selected="selected"' : null ?>><?= l('monitors.filters.order_by_uptime') ?></option>
                                    <option value="average_response_time" <?= $data->filters->order_by == 'average_response_time' ? 'selected="selected"' : null ?>><?= l('monitors.filters.order_by_average_response_time') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="filters_order_type" class="form-control form-control-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="filters_results_per_page" class="form-control form-control-sm">
                                    <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                        <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4 mt-4">
                                <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($data->monitors)): ?>
        <div class="row"  id="monitor-blocks">

            <?php foreach($data->monitors as $row): ?>
                <?= (new \Altum\View('monitors/monitor_widget', (array) $this))->run(['monitor' => $row, 'projects' => $data->projects]) ?>
            <?php endforeach ?>
        </div>

        <div class="table-responsive table-custom-container" id="monitor-list">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('monitor.table.name') ?></th>
                    <th><?= l('monitor.table.status') ?></th>
		    <th><?= l('monitor.table.uptime') ?></th>
		    <th><?= l('monitor.table.downtime') ?></th>
		    <th><?= l('monitor.table.last_run') ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($data->monitors as $row): ?>

                    <tr>
                        <td class="text-nowrap">
                            <a href="<?= url('monitor/' . $row->monitor_id) ?>"><?= $row->name ?></a>
                        </td>

                        <td class="text-nowrap">
                        <span class="badge badge-light" style="color: <?= $row->color ?> !important;">
                                <?php if($row->is_enabled): ?>
                                    <?php if($row->is_ok): ?>
                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.is_ok') ?>">
                                            <i class="fa fa-fw fa-sm fa-check-circle text-success"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.is_not_ok') ?>">
                                            <i class="fa fa-fw fa-sm fa-times-circle text-danger"></i>
                                        </span>
                                    <?php endif ?>
                                <?php else: ?>
                                    <span class="mr-1" data-toggle="tooltip" title="<?= l('monitor.is_enabled_paused') ?>">
                                        <i class="fa fa-fw fa-sm fa-pause-circle text-warning"></i>
                                    </span>
                                <?php endif ?>
                            </span>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold" data-toggle="tooltip" title="<?= sprintf(l('monitor.total_runs_tooltip'), nr($row->total_runs)) ?>">
                                    <?= nr($row->uptime, 3) . '%' ?>
                                </span>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold" data-toggle="tooltip" title="<?= sprintf(l('monitor.total_missed_runs_tooltip'), nr($row->total_missed_runs)) ?>">
                                    <p class="text-right"><?= nr($row->downtime, 3) . '%' ?></p>
                                </span>
                            </div>
                        </td>

                        <td class="text-truncate text-muted">
                            <span data-toggle="tooltip" title="<?= $row->last_run_datetime ? \Altum\Date::get($row->last_run_datetime, 1) : '' ?>"><?= $row->last_run_datetime ? \Altum\Date::get_timeago($row->last_run_datetime) : '-' ?></span>
                        </td>

                        <td>
                            <div class="d-flex justify-content-end">
                                <?= include_view(THEME_PATH . 'views/monitor/monitor_dropdown_button.php', ['id' => $row->monitor_id, 'resource_name' => $row->name]) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>

                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-4 mb-3" alt="<?= l('monitors.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('monitors.no_data') ?></h2>
                    <p class="text-muted"><?= l('monitors.no_data_help') ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(event){
    set_view('monitor');
});
</script>

