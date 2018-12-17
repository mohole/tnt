<?php
/*
Plugin Name: Tiny News Ticker (TNT)
Plugin URI: https://www.moholepeople.it/tnt
Description: Semplice e sexy news ticker che recupera dati da una fonte json e li mostra in un widget scorrevole.
Version: 1.0.0
Author: Scuola Mohole
Author URI: https://scuola.mohole.it
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: tnt

Tiny News Ticker (TNT) is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Tiny News Ticker (TNT) is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Tiny News Ticker (TNT). If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

// stop if plugin is directly called
if (!defined('WPINC')) die;

// plugin activation
register_activation_hook(__FILE__, 'tnt_activate');
function tnt_activate() {

    add_option('tnt_data_source', array(
        array(
            'title' => 'Planets',
            'source' => 'https://mysafeinfo.com/api/data?list=planets&format=json&abbreviate=false&case=default',
            'template' => 'planets'

        ),
        array(
            'title' => 'Bird Species',
            'source' => 'https://mysafeinfo.com/api/data?list=birdgroups&format=json&abbreviate=false&case=default',
            'template' => 'bird'
        ),
        array(
            'title' => 'Rocket Launches',
            'source' => 'http://launchlibrary.net/1.3/launch',
            'template' => 'rocket-launches'
        ),
    ));
    add_option('tnt_data_source_selected', 'Planets');
}

//deactivation plugin
register_deactivation_hook(__FILE__, 'tnt_deactivate');
function tnt_deactivate() {
    delete_option('tnt_data_source');
    delete_option('tnt_data_source_selected');
}

/**
 * tnt_init
 * add CSS
 */
add_action('wp_enqueue_scripts', 'tnt_init');
function tnt_init() {
    wp_enqueue_style('tnt', plugin_dir_url( __FILE__ ) . 'assets/css/tnt.css', array(), '1');
    wp_enqueue_style( 'Unlock',  'https://fonts.googleapis.com/css?family=Unlock' , array(), '1');
    wp_enqueue_style( 'FA',  'https://use.fontawesome.com/releases/v5.6.1/css/all.css' , array());
}

/**
 * tnt_render
 * render setting pages
 */
add_shortcode('tnt','tnt_render');
function tnt_render() {
    ob_start();
    $data = tnt_get_data();
    if ($data) {
        define('TNT_TEMPLATES_FOLDER', plugin_dir_path( __FILE__ ) . 'assets/templates');
        $source = get_option('tnt_data_source_selected');
        $sources = get_option('tnt_data_source');
        for ($i = 0; $i < count($sources); $i++) {
            if ($sources[$i]['title'] == $source) $template = $sources[$i]['template'];
        }
        $tmpl_url = TNT_TEMPLATES_FOLDER . '/' . $template.'.html';
        $template_content = file_get_contents($tmpl_url);
        $template_repeated_section = tnt_get_string_between($template_content, '[+tnt+]', '[+/tnt+]');

        switch (get_option('tnt_data_source_selected')) {
            case 'Planets':
                break;
            case 'Rocket Launches':
                $data = $data->launches;
                break;
            case 'Bird Species':
                break;
        }
        $html = '';
        for ($i = 0; $i < count($data); $i++) {
            $dummy = $template_repeated_section;
            foreach ($data[$i] as $key => $value) {
                $dummy = str_replace('[+' . $key . '+]', $value, $dummy);
            }
            $html .= $dummy;
        }
        ob_start();
        echo str_replace('[+tnt+]' . $template_repeated_section . '[+/tnt+]', $html, $template_content);
        $saved = ob_get_contents();
        echo ob_get_clean();
    } else {
        echo "Fonte dati non disponibile!";
    }
    return ob_get_clean();
}

/**
 * tnt_get_data
 * data from external source
 */
function tnt_get_data() {
    $source = get_option('tnt_data_source_selected');
    $sources = get_option('tnt_data_source');
    $data = false;
    for ($i = 0; $i < count($sources); $i++) {
        if ($sources[$i]['title'] == $source) $data = file_get_contents($sources[$i]['source']);
    }
    if (!data) return false;
    return json_decode($data);
}

/**
 * tnt_add_settings_page
 * add settings page in backend
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
 * render_settings_page
 * render settings
 */
function tnt_render_settings_page() {
    if (isset($_POST['submit']) && wp_verify_nonce($_POST['tnt_modify_data_source_nonce'], 'tnt_modify_data_source')) {
        update_option('tnt_data_source_selected', $_POST['tnt_data_source']);
    }
    ?>
    <div class="wrap">
        <h2>Tiny News Ticker Settings Page</h2>
        <form method="post">
            <label>Select News source</label>
            <select name="tnt_data_source">
                <?php
                $tnt_data_source = get_option('tnt_data_source');
                for ($i = 0; $i < count($tnt_data_source); $i++) {
                    $selected = ($tnt_data_source[$i]['title'] == get_option('tnt_data_source_selected')) ? 'selected' : '';
                    echo '<option ' . $selected . ' value="' . $tnt_data_source[$i]['title']  . '">' . $tnt_data_source[$i]['title'] . '</option>';
                }
                ?>
            </select>
            <?php wp_nonce_field('tnt_modify_data_source', 'tnt_modify_data_source_nonce') ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * tnt_get_string_between
 */
function tnt_get_string_between($string, $start, $end) {
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
