<?php defined('ALTUMCODE') || die() ?>

<div class="">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('sub-admin/teams') ?>">Teams</a>
            </li>
        </ol>
    </nav>

    
    <div class="col-md-12  text-right">
            <a class="btn btn-success text-right" href="<?= url('sub-admin/team-invite-member') ?>">Invite Member</a>
        </div>

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