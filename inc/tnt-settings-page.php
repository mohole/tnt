<?php

/**
 * tnt_add_settings_page_page
 * funzione che aggiunge la pagina di impostazioni nel backend
 */
add_action('admin_menu', 'tnt_add_settings_page');
function tnt_add_settings_page() {
    add_options_page(
        'Tiny News Ticker',
        'TNT',
        'manage_options',
        'tnt_settings_page',
        'tnt_render_settings_page'
    );
}

/**
 * tnt_add_settings_page
 * funzione che aggiunge le impostazioni nel database
 */
function tnt_add_settings() {
    add_option('tnt_data_source', array(
        array(
            'title' => 'English Monarch',
            'source' => 'http://mysafeinfo.com/api/data?list=englishmonarchs&format=json',
            'template' => 'tnt-englishmonarch'
        ),
        array(
            'title' => 'Dinosaurs',
            'source' => 'http://mysafeinfo.com/api/data?list=dinosaurs&format=json',
            'template' => 'tnt-dinosaurs'
        ),
        array(
            'title' => 'Dog breeds',
            'source' => 'http://mysafeinfo.com/api/data?list=dogbreeds&format=json',
            'template' => 'tnt-dogs'
        ),
        array(
            'title' => 'Music Styles',
            'source' => 'http://mysafeinfo.com/api/data?list=musicstyles&format=json',
            'template' => 'tnt-music-styles'
        ),
    ));
    add_option('tnt_data_source_selected', '');
}

/**
 * tnt_remove_settings
 * funzione che rimuove le impostazioni dal database
 */
function tnt_remove_settings() {
    delete_option('tnt_data_source');
    delete_option('tnt_data_source_selected');
}

/**
 * tnt_add_settings_page
 * funzione che renderizza la pagina delle impostazioni
 */
function tnt_render_settings_page() {
    if (!current_user_can('manage_options')) wp_die('Non possiedi i permessi per accedere a questa pagina');
    ?>
    <div class="wrap">
        <h2>Tiny News Ticker Settings Page</h2>
        <?php
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['tnt_modify_data_source_nonce'], 'tnt_modify_data_source')) {
            update_option('tnt_data_source_selected', $_POST['tnt_data_source']);
        }
        ?>
        <form method="post">
            <label>Scegli la fonte dei dati</label>
            <select name="tnt_data_source">
                <?php 
                $tnt_data_source = get_option('tnt_data_source'); 
                for ($i = 0; $i < count($tnt_data_source); $i++) {
                    $selected = ($tnt_data_source[$i]['template'] == get_option('tnt_data_source_selected')) ? 'selected' : '';
                    echo '<option ' . $selected . ' value="' . $tnt_data_source[$i]['template']  . '">' . $tnt_data_source[$i]['title'] . '</option>';
                }
                ?>
            </select>
            <?php wp_nonce_field('tnt_modify_data_source', 'tnt_modify_data_source_nonce') ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}