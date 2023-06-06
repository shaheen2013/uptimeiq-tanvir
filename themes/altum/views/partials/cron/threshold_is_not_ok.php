<?php defined('ALTUMCODE') || die() ?>

<p><?= sprintf(l('cron.is_not_ok.p2', $data->row->language), $data->row->name) ?></p>

<div>
    <table>
        <tbody>
            <tr>
                <th><?= l('cron.is_not_ok.start_datetime', $data->row->language) ?></th>
                <td>
                    <span class="text-muted">
                        <?= \Altum\Date::get() ?>
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
                            <a href="<?= url('threshold/' . $data->row->threshold_id) ?>">
                                <?= l('cron.is_not_ok.button', $data->row->language) ?>
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
    <small class="text-muted"><?= sprintf(l('cron.is_not_ok.notice', $data->row->language), '<a href="' . url('threshold-update/' . $data->row->threshold_id) . '">', '</a>') ?></small>
</p>
