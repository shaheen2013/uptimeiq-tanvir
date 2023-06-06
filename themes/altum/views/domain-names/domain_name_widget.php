<?php defined('ALTUMCODE') || die() ?>

<div class="col-12 col-md-6 col-xl-4 mb-4">
    <div class="card h-100" <?= $data->domain_name->project_id ? 'style="border-color: ' . $data->projects[$data->domain_name->project_id]->color . ';"' : null ?>>
        <div class="card-body d-flex flex-column justify-content-between">
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <h2 class="h5 m-0 card-title text-truncate">
                        <span><?= $data->domain_name->name ?></span>
                    </h2>

                    <?= include_view(THEME_PATH . 'views/domain-name/domain_name_dropdown_button.php', ['id' => $data->domain_name->domain_name_id, 'resource_name' => $data->domain_name->name]) ?>
                </div>

                <div class="text-truncate">
                    <small class="text-muted">
                        <img src="https://external-content.duckduckgo.com/ip3/<?= $data->domain_name->target ?>.ico" class="img-fluid icon-favicon mr-1" />

                        <a href="<?= 'https://' . $data->domain_name->target ?>" class="text-muted" target="_blank" rel="noreferrer"><?= $data->domain_name->target ?></a>
                    </small>
                </div>
            </div>

            <?php if(!$data->domain_name->last_check_datetime): ?>
                <div>
                    <small class="text-muted">
                        <i class="fa fa-fw fa-sm fa-clock text-muted mr-1"></i>

                        <span><?= l('domain_name.pending_check') ?></span>
                    </small>
                </div>
            <?php elseif(!property_exists($data->domain_name->whois, 'end_datetime') && !property_exists($data->domain_name->whois, 'start_datetime') && !property_exists($data->domain_name->whois, 'updated_datetime')): ?>
                <div>
                    <small class="text-muted">
                        <i class="fa fa-fw fa-sm fa-info-circle text-muted mr-1"></i>

                        <span><?= l('domain_name.not_registered') ?></span>
                    </small>
                </div>
            <?php else: ?>

                <div>
                    <small class="text-muted">
                        <i class="fa fa-fw fa-sm fa-calendar text-muted mr-1"></i>

                        <span><?= l('domain_name.whois_start_datetime') ?></span>

                        <span class="font-weight-bold" data-toggle="tooltip" title="<?= is_null($data->domain_name->whois->start_datetime) ? l('domain_name.no_data') : \Altum\Date::get($data->domain_name->whois->start_datetime, 1) ?>">
                            <?= is_null($data->domain_name->whois->start_datetime) ? l('domain_name.no_data') : \Altum\Date::get($data->domain_name->whois->start_datetime, 2) ?>
                        </span>
                    </small>
                </div>

                <div>
                    <small class="text-muted">
                        <i class="fa fa-fw fa-sm fa-sync-alt text-muted mr-1"></i>

                        <span><?= l('domain_name.whois_updated_datetime') ?></span>

                        <span class="font-weight-bold" data-toggle="tooltip" title="<?= is_null($data->domain_name->whois->updated_datetime) ? l('domain_name.no_data') : \Altum\Date::get($data->domain_name->whois->updated_datetime, 1) ?>">
                            <?= is_null($data->domain_name->whois->updated_datetime) ? l('domain_name.no_data') : \Altum\Date::get($data->domain_name->whois->updated_datetime, 2) ?>
                        </span>
                    </small>
                </div>

                <div>
                    <small class="text-muted">
                        <i class="fa fa-fw fa-sm fa-calendar text-muted mr-1"></i>

                        <span><?= l('domain_name.whois_end_datetime') ?></span>

                        <span class="font-weight-bold" data-toggle="tooltip" title="<?= is_null($data->domain_name->whois->end_datetime) ? l('domain_name.no_data') : \Altum\Date::get($data->domain_name->whois->end_datetime, 1) ?>">
                            <?= is_null($data->domain_name->whois->end_datetime) ? l('domain_name.no_data') : \Altum\Date::get($data->domain_name->whois->end_datetime, 2) ?>
                        </span>
                    </small>
                </div>

                <?php if($data->domain_name->whois && property_exists($data->domain_name->whois, 'registrar') && !empty($data->domain_name->whois->registrar)): ?>
                    <div>
                        <small class="text-muted">
                            <i class="fa fa-fw fa-sm fa-atlas mr-1"></i>
                            <span><?= sprintf(l('domain_name.registrar'), $data->domain_name->whois->registrar) ?></span>
                            <span data-toggle="tooltip" title="<?= implode(', ', $data->domain_name->whois->nameservers) ?>">
                                <i class="fa fa-fw fa-sm fa-info-circle text-muted mr-1"></i>
                            </span>
                        </small>
                    </div>
                <?php endif ?>

                <?php if($data->domain_name->ssl && property_exists($data->domain_name->ssl, 'end_datetime')): ?>
                    <div>
                        <small class="text-muted">
                            <i class="fa fa-fw fa-sm fa-lock text-muted mr-1"></i>

                            <span><?= l('domain_name.ssl_end_datetime') ?></span>

                            <span class="font-weight-bold" data-toggle="tooltip" title="<?= \Altum\Date::get($data->domain_name->ssl->end_datetime, 1) ?>">
                                <?= \Altum\Date::get($data->domain_name->ssl->end_datetime, 2) ?>
                            </span>

                            <span data-toggle="tooltip" title="<?= $data->domain_name->ssl->issuer_full ?>">
                                <i class="fa fa-fw fa-sm fa-info-circle text-muted mr-1"></i>
                            </span>
                        </small>
                    </div>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>
</div>
