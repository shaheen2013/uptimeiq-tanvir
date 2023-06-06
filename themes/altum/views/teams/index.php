<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <!-- <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li class="active" aria-current="page"><?= l('teams.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12 col-xl d-flex align-items-center mb-3 mb-xl-0">
            <h1 class="h4 m-0"><?= l('teams.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('teams.subheader') ?>">
                    <i class="fa fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-xl-auto d-flex">
            <div>
                <?php if (\Altum\Teams::get_main_user()->plan_settings->teams_limit != -1 && $data->total_teams >= \Altum\Teams::get_main_user()->plan_settings->teams_limit) : ?>
                    <button type="button" class="btn btn-outline-primary disabled" data-toggle="tooltip" title="<?= l('global.info_message.plan_feature_limit') ?>">
                        <i class="fa fa-fw fa-sm fa-plus"></i> <?= l('teams.create') ?>
                    </button>
                <?php else : ?>
                    <a href="<?= url('team-create') ?>" class="btn btn-outline-primary"><i class="fa fa-fw fa-sm fa-plus"></i> <?= l('teams.create') ?></a>
                <?php endif ?>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>">
                        <i class="fa fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('teams?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                            <i class="fa fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('teams?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item">
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

                            <?php if (count($data->filters->get)) : ?>
                                <a href="<?= url('teams') ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="search" class="small"><?= l('global.filters.search') ?></label>
                                <input type="search" name="search" id="search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                            </div>

                            <div class="form-group px-4">
                                <label for="search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                <select name="search_by" id="search_by" class="form-control form-control-sm">
                                    <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('teams.input.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="order_by" class="form-control form-control-sm">
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('teams.input.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="order_type" class="form-control form-control-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="results_per_page" class="form-control form-control-sm">
                                    <?php foreach ($data->filters->allowed_results_per_page as $key) : ?>
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
    </div> -->

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('teams-system') ?>"><?= l('teams_system.breadcrumb') ?></a>
            </li>
        </ol>
    </nav>

    <?php
    if (\Altum\Teams::get_main_user()->type == 1) { ?>
        <div class="offset-md-8 col-md-4">
            <a class="btn btn-info" href="<?= url('team-create') ?>">Create Team</a>
            <a class="btn btn-success" href="<?= url('team-member-create/invite') ?>">Invite Member</a>
        </div>
    <?php }elseif (\Altum\Teams::get_main_user()->type == 2) {?>
        <div class="offset-md-10 col-md-2">
            <a class="btn btn-success" href="<?= url('team-member-create/invite') ?>">Invite Member</a>
        </div>
    <?php }
    ?>

    <?php if (count($data->teams_member)) : ?>
        <div class="table-responsive table-custom-container mt-4">
            <h4 class="p-2 pl-3">Team Invitation</h4>
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th><?= l('teams_member.table.team') ?></th>
                        <th><?= l('team_members.input.access') ?></th>
                        <th><?= l('team_members.table.status') ?></th>
                        <th><?= l('teams_member.table.datetime') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data->teams_member as $row) : ?>
                        <tr>
                            <td class="text-nowrap">
                                <?php if ($row->status) : ?>
                                    <a href="#" data-toggle="modal" data-target="#teams_member_login_modal" data-team-member-id="<?= $row->team_member_id ?>">
                                        <span data-toggle="tooltip" data-placement="right" title="Click to login"><?= $row->name ?></span>
                                    </a>
                                <?php else : ?>
                                    <span data-toggle="tooltip" data-placement="right" title="Join team"><?= $row->name ?></span>
                                <?php endif ?>

                            </td>

                            <td class="text-nowrap">
                                <?php
                                $access_html = [];
                                foreach ($data->teams_access as $key => $value) {
                                    $access_html[$key] = '';
                                    foreach ($data->teams_access[$key] as $access_key => $access_translation) {
                                        $access_html[$key] .= ($row->access->{$access_key} ? $access_translation : '<s>' . $access_translation . '</s>') . '<br />';
                                    }
                                }
                                ?>
                                <span class="badge badge-secondary mx-2" data-toggle="tooltip" data-html="true" title="<?= $access_html['read'] ?>">
                                    <i class="fa fa-fw fa-sm fa-eye"></i> <?= l('team_members.input.access.read') ?>
                                </span>

                                <span class="badge badge-success mx-2" data-toggle="tooltip" data-html="true" title="<?= $access_html['create'] ?>">
                                    <i class="fa fa-fw fa-sm fa-plus"></i> <?= l('team_members.input.access.create') ?>
                                </span>

                                <span class="badge badge-info mx-2" data-toggle="tooltip" data-html="true" title="<?= $access_html['update'] ?>">
                                    <i class="fa fa-fw fa-sm fa-pencil-alt"></i> <?= l('team_members.input.access.update') ?>
                                </span>

                                <span class="badge badge-danger mx-2" data-toggle="tooltip" data-html="true" title="<?= $access_html['delete'] ?>">
                                    <i class="fa fa-fw fa-sm fa-trash-alt"></i> <?= l('team_members.input.access.delete') ?>
                                </span>
                            </td>

                            <td class="text-nowrap">
                                <?php if ($row->status) : ?>
                                    <span class="badge badge-success"><?= l('team_members.table.status_accepted') ?></span>
                                <?php else : ?>
                                    <span class="badge badge-warning"><?= l('team_members.table.status_invited') ?></span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap"><span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($row->datetime, 1) ?>"><?= \Altum\Date::get_timeago($row->datetime) ?></span></td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/teams-member/teams_member_dropdown_button.php', ['id' => $row->team_member_id, 'status' => $row->status]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $data->team_pagination ?></div>
    <?php endif ?>


    <!-- <?php if (count($data->teams)) : ?>
        <div class="table-responsive table-custom-container">
            <h4 class="p-2 pl-3">My Teams</h4>
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th><?= l('teams.input.name') ?></th>
                        <th><?= l('teams.table.members') ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data->teams as $row) : ?>
                        <tr>
                            <td class="text-nowrap">
                                <a data-toggle="tooltip" data-placement="right" title="Invite team members" href="<?= url('team/' . $row->team_id) ?>"><?= $row->name ?></a>
                            </td>

                            <td class="text-nowrap">
                                <?= nr($row->members) ?>
                            </td>

                            <td class="text-nowrap">
                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>') ?>">
                                    <i class="fa fa-fw fa-calendar text-muted"></i>
                                </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' : '-')) ?>">
                                    <i class="fa fa-fw fa-history text-muted"></i>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/team/team_dropdown_button.php', ['id' => $row->team_id, 'resource_name' => $row->name]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php endif ?> -->

    <?php if (!count($data->teams_member)) : ?>
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-4 mb-3" alt="<?= l('teams.no_data') ?>" />
                    <h2 class="h4 text-muted">There are no invitation available</h2>
                    <p class="text-muted"><?= l('teams.no_data_help') ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'team',
    'resource_id' => 'team_id',
    'has_dynamic_resource_name' => true,
    'path' => 'teams/delete'
]), 'modals'); ?>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/teams-member/teams_member_delete_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/teams-member/teams_member_join_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/teams-member/teams_member_login_modal.php'), 'modals'); ?>