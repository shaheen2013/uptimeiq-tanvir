<?php defined('ALTUMCODE') || die() ?>

<nav aria-label="breadcrumb">
    <ol class="custom-breadcrumbs small">
        <li>
            <a href="<?= url('admin/users') ?>"><?= l('admin_users.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
        </li>
        <li class="active" aria-current="page"><?= l('admin_user_create.breadcrumb') ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between mb-4">
    <h1 class="h3 m-0"><i class="fa fa-fw fa-xs fa-user text-primary-900 mr-2"></i> <?= l('admin_user_create.header') ?></h1>
</div>

<?= \Altum\Alerts::output_alerts() ?>

<div class="card <?= \Altum\Alerts::has_field_errors() ? 'border-danger' : null ?>">
    <div class="card-body">

        <form action="" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

            <div class="form-group">
                <label for="name"><i class="fa fa-fw fa-sm fa-signature text-muted mr-1"></i><?= l('admin_users.main.name') ?></label>
                <input id="name" type="text" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                <?= \Altum\Alerts::output_field_error('name') ?>
            </div>

            <div class="form-group">
                <label for="email"><i class="fa fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= l('admin_users.main.email') ?></label>
                <input id="email" type="email" name="email" class="form-control <?= \Altum\Alerts::has_field_errors('email') ? 'is-invalid' : null ?>" value="<?= $data->values['email'] ?>" required="required" />
                <?= \Altum\Alerts::output_field_error('email') ?>
            </div>

            <div class="form-group">
                <label for="role"><i class="fas fa-user-shield text-muted mr-1  "></i> Role</label>
                <select id="role" class="form-control" name="role" required>
                    <option selected disabled>Select option</option>
                    <option value="2">Admin</option>
                    <option value="3">Operator</option>
                    <option value="0">Auditor</option>
                </select>
                <?= \Altum\Alerts::output_field_error('role') ?>
            </div>

            <div class="row">
            <div class="form-group col-md-10">
                <label for="team"><i class="fas fa-user-shield text-muted mr-1"></i> Team</label>
                <select id="team" class="form-control" name="team">
                    <option selected disabled>Select option</option>
                    <?php
                    foreach ($data->values['team'] as $key => $val) { ?>
                        <option value="<?= $val->team_id ?>"><?= $val->name ?></option>
                    <?php
                    }
                    ?>
                </select>
                <?= \Altum\Alerts::output_field_error('team') ?>
            </div>

            <div class="from-group col-md-2 pt-2">

                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-info mt-4" data-toggle="modal" data-target="#teamCreate" style="width: 100%;">
                        Create team
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="teamCreate" tabindex="-1" role="dialog" aria-labelledby="teamCreateLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="teamCreateLabel">Create Team</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <label for="team_name"><i class="fa-solid fa-users text-muted mr-1"></i>Team Name</label>
                                    <input id="team_name" type="text" name="team_name" class="form-control" />
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="team_add">Add team</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                </div>

            <button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.create') ?></button>
        </form>

    </div>
</div>