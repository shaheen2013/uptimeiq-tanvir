<?php defined('ALTUMCODE') || die() ?>

<?php ob_start() ?>
<script>
    let view_style = 'table';
    if(get_cookie('view_style')) {
        view_style = get_cookie('view_style')
    }

    document.querySelector('#toggle_view').addEventListener('click', event => {
        let new_view_style = view_style == 'list' ? 'table' : 'list';

        /* Set a cookie with the new theme style */
        set_cookie('view_style', new_view_style, 30, <?= json_encode(COOKIE_PATH) ?>);

        document.querySelector('#toggle_view svg').classList.remove('fa-'+view_style);
        document.querySelector('#toggle_view svg').classList.add('fa-'+new_view_style);
        //document.querySelector(`body[data-view-style]`).setAttribute('data-view-style', new_view_style);

	/*
        switch(new_view_style) {
            case 'list':
                css.setAttribute('href', <?= json_encode(ASSETS_FULL_URL . 'css/' . (\Altum\Router::$path == 'admin' ? 'admin-' : null) . \Altum\ThemeStyle::$themes['dark'][l('direction')] . '?v=' . PRODUCT_CODE) ?>);
                document.body.classList.add('c_darkmode');
                break;

            case 'light':
                css.setAttribute('href', <?= json_encode(ASSETS_FULL_URL . 'css/' . (\Altum\Router::$path == 'admin' ? 'admin-' : null) . \Altum\ThemeStyle::$themes['light'][l('direction')] . '?v=' . PRODUCT_CODE) ?>);
                document.body.classList.remove('c_darkmode');
                break;
        }

        document.querySelectorAll('[data-logo]').forEach(element => {
            let new_brand_value = element.getAttribute(`data-${new_theme_style}-value`);
            let new_brand_class = element.getAttribute(`data-${new_theme_style}-class`);
            let current_logo_element_type = element.firstElementChild.nodeName.toLowerCase();
            let new_brand_html = (new_brand_value.includes('http://') || new_brand_value.includes('https://')) ? `<img src="${new_brand_value}" class="${new_brand_class}" alt="<?= l('global.accessibility.logo_alt') ?>" />` : `<${current_logo_element_type} class="${new_brand_class}">${new_brand_value}</${current_logo_element_type}>`;
            element.innerHTML = new_brand_html;
        });


        document.querySelector(`#switch_theme_style`).setAttribute('data-original-title', document.querySelector(`#switch_theme_style`).getAttribute(`data-title-theme-style-${theme_style}`));
        document.querySelector(`#switch_theme_style [data-theme-style="${new_theme_style}"]`).classList.remove('d-none');
        document.querySelector(`#switch_theme_style [data-theme-style="${theme_style}"]`).classList.add('d-none');
        $(`#switch_theme_style`).tooltip('hide').tooltip('show');
        */

        event.preventDefault();
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
