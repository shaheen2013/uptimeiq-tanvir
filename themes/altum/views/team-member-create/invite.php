<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('teams-system') ?>"><?= l('teams_system.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('team_member_create.breadcrumb') ?></li>
        </ol>
    </nav>

    <h1 class="h4 mb-4 text-truncate"><?= l('team_member_create.header') ?></h1>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form" class="member_invite">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="user_email"><i class="fa fa-fw fa-envelope fa-sm text-muted mr-1"></i> <?= l('team_members.input.user_email') ?></label>
                    <input type="email" id="user_email" name="user_email" class="form-control <?= \Altum\Alerts::has_field_errors('user_email') ? 'is-invalid' : null ?>" value="<?= $data->values['user_email'] ?>" />
                    <span class="error user_email_error text-danger"></span>
                </div>

                <div class="form-group">
                    <label for="team_id"><i class="fas fa-users text-muted mr-1"></i> Select Team</label>
                    <select class="form-control" id="team_id" name="team_id">
                        <option selected disabled>Select</option>
                        <?php
                        foreach ($data->values['teams'] as $key => $value) {?>
                            <option value="<?= $value->team_id ?>"><?= $value->name ?></option>
                        <?php }
                        ?>
                    </select>
                    <span class="error team_id_error text-danger"></span>
                </div>

                <div class="form-group">
                    <label for="access"><i class="fa fa-fw fa-sm fa-check-double text-muted mr-1"></i> Select Role</label>
                    <select class="form-control" id="access" name="access">
                        <option selected disabled>Select</option>
                        <option value="admin">Admin</option>
                        <option value="operator">Operator</option>
                        <option value="author">Author</option>
                    </select>
                    <span class="error access_error text-danger"></span>
                    <small class="form-text text-muted"><?= l('team_members.input.access_help') ?></small>
                </div>

                <div class="alert alert-info"><?= l('team_members.info_message.access') ?></div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-3 "><?= l('team_member_create.submit') ?></button>
            </form>

        </div>
    </div>
</div>
