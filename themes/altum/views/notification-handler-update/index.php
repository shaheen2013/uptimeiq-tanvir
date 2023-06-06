<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('notification-handlers') ?>"><?= l('notification_handlers.breadcrumb') ?></a><i class="fa fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('notification_handler_update.breadcrumb') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between mb-4">
        <h1 class="h4 text-truncate mb-0"><?= l('notification_handler_update.header') ?></h1>

        <?= include_view(THEME_PATH . 'views/notification-handlers/notification_handler_dropdown_button.php', ['id' => $data->notification_handler->notification_handler_id, 'resource_name' => $data->notification_handler->name]) ?>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fa fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('notification_handlers.input.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->notification_handler->name ?>" required="required" />
                </div>

                <div class="form-group">
                    <label for="type"><i class="fa fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('notification_handlers.input.type') ?></label>
                    <select id="type" name="type" class="form-control" required="required">
                        <?php foreach(require APP_PATH . 'includes/notification_handlers.php' as $notification_handler): ?>
                            <option value="<?= $notification_handler ?>" <?= $data->notification_handler->type == $notification_handler ? 'selected="selected"' : null ?>><?= l('notification_handlers.input.type_' . $notification_handler) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group" data-type="email">
                    <label for="email"><i class="fa fa-fw fa-sm fa-envelope text-muted mr-1"></i> <?= l('notification_handlers.input.email') ?></label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= $data->notification_handler->settings->email ?? '' ?>" maxlength="512" required="required" />
                    <small class="text-muted"><?= l('notification_handlers.input.email_help') ?></small>
                </div>

                <div class="form-group" data-type="webhook">
                    <label for="webhook"><i class="fa fa-fw fa-sm fa-satellite-dish text-muted mr-1"></i> <?= l('notification_handlers.input.webhook') ?></label>
                    <input type="url" id="webhook" name="webhook" class="form-control" value="<?= $data->notification_handler->settings->webhook ?? '' ?>" maxlength="512" required="required" />
                    <small class="text-muted"><?= l('notification_handlers.input.webhook_help') ?></small>
                </div>

                <div class="form-group" data-type="slack">
                    <label for="slack"><i class="fab fa-fw fa-sm fa-slack text-muted mr-1"></i> <?= l('notification_handlers.input.slack') ?></label>
                    <input type="url" id="slack" name="slack" class="form-control" value="<?= $data->notification_handler->settings->slack ?? '' ?>" maxlength="512" required="required" />
                    <small class="text-muted"><?= l('notification_handlers.input.slack_help') ?></small>
                </div>

                <div class="form-group" data-type="discord">
                    <label for="discord"><i class="fab fa-fw fa-sm fa-discord text-muted mr-1"></i> <?= l('notification_handlers.input.discord') ?></label>
                    <input type="url" id="discord" name="discord" class="form-control" value="<?= $data->notification_handler->settings->discord ?? '' ?>" maxlength="512" required="required" />
                    <small class="text-muted"><?= l('notification_handlers.input.discord_help') ?></small>
                </div>

                <div class="form-group" data-type="twilio">
                    <label for="twilio"><i class="fa fa-fw fa-sm fa-sms text-muted mr-1"></i> <?= l('notification_handlers.input.twilio') ?></label>
                    <input type="tel" id="twilio" name="twilio" class="form-control" value="<?= $data->notification_handler->settings->twilio ?? '' ?>" maxlength="512" required="required" />
                    <small class="text-muted"><?= l('notification_handlers.input.twilio_help') ?></small>
                </div>

                <div class="form-group" data-type="telegram">
                    <label for="telegram"><i class="fab fa-fw fa-sm fa-telegram text-muted mr-1"></i> <?= l('notification_handlers.input.telegram') ?></label>
                    <input type="tel" id="telegram" name="telegram" class="form-control" value="<?= $data->notification_handler->settings->telegram ?? '' ?>" maxlength="512" required="required" />
                    <small class="text-muted"><?= l('notification_handlers.input.telegram_help') ?></small>
                </div>

                <div class="form-group" data-type="telegram">
                    <label for="telegram_chat_id"><i class="fa fa-fw fa-sm fa-comment-alt text-muted mr-1"></i> <?= l('notification_handlers.input.telegram_chat_id') ?></label>
                    <input type="tel" id="telegram_chat_id" name="telegram_chat_id" class="form-control" value="<?= $data->notification_handler->settings->telegram_chat_id ?? '' ?>" maxlength="512" required="required" />
                    <small class="text-muted"><?= l('notification_handlers.input.telegram_chat_id_help') ?></small>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->notification_handler->is_enabled ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="is_enabled"><?= l('notification_handlers.input.is_enabled') ?></label>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    /* Type handler */
    let type_handler = () => {
        let type = document.querySelector('select[name="type"]').value;

        document.querySelectorAll(`[data-type]:not([data-type="${type}"])`).forEach(element => {
            element.classList.add('d-none');

            element.querySelector('input').setAttribute('disabled', 'disabled');
            element.querySelector('input').removeAttribute('required');
        });

        document.querySelectorAll(`[data-type="${type}"]`).forEach(element => {
            element.classList.remove('d-none');

            element.querySelector('input').removeAttribute('disabled');
            element.querySelector('input').setAttribute('required', 'required');
        });
    }

    type_handler();

    document.querySelector('select[name="type"]') && document.querySelector('select[name="type"]').addEventListener('change', type_handler);
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
