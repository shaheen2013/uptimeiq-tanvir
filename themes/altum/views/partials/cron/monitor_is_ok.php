<?php defined('ALTUMCODE') || die() ?>

<p><?= sprintf(l('cron.is_ok.p1', $data->row->language), $data->row->name) ?></p>

<div>
    <table>
        <tbody>
            <tr>
                <th><?= l('cron.is_ok.start_datetime', $data->row->language) ?></th>
                <td>
                    <span class="text-muted">
                        <?= \Altum\Date::get($data->monitor_incident->start_datetime, 1) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th><?= l('cron.is_ok.end_datetime', $data->row->language) ?></th>
                <td>
                    <span class="text-muted">
                        <?= \Altum\Date::get($data->monitor_incident->end_datetime, 1) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th><?= l('cron.is_ok.length', $data->row->language) ?></th>
                <td>
                    <span class="text-muted">
                        <?= \Altum\Date::get_elapsed_time($data->monitor_incident->start_datetime, $data->monitor_incident->end_datetime) ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div style="margin-top: 30px">
    <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
        <tbody>
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr>
                        <td>
                            <a href="<?= url('monitor/' . $data->row->monitor_id) ?>">
                                <?= l('cron.is_ok.button', $data->row->language) ?>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<p>
    <small class="text-muted"><?= sprintf(l('cron.is_ok.notice', $data->row->language), '<a href="' . url('monitor-update/' . $data->row->monitor_id) . '">', '</a>') ?></small>
</p>
