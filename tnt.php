<?php
/**
 * Plugin Name: Tiny News Ticker (TNT)
 * Plugin URI:
 * Description: Semplice news ticker che recupera dati da una fonte json e li mostra in un widget scorrevole.
 * Version: 1.0.0
 * Author: Scuola Mohole
 * Author URI: https://scuola.mohole.it
 */

// termino l'esecuzione se il plugin è richiamato direttamente
if (!defined('WPINC')) die;

// attivazione del plugin
register_activation_hook(__FILE__, 'tnt_activate');
function tnt_activate() {/*
    add_option('tnt_data_source', 'TEST');*/
	add_option('tnt_data_source', array(
		array(
			'title' => 'English Monarch',
			'source' => 'http://mysafeinfo.com/api/data?list=englishmonarchs&format=json',
			'template' => 'english-monarch',
		),
		array(
			'title' => 'Studio Ghibli Film',
			'source' => 'http://ghibliapi.herokuapp.com/films',
			'template' => 'studio-ghibli-films',
		),
		array(
			'title' => 'Launch Library',
			'source' => 'http://launchlibrary.net/1.3/launch',
			'template' => 'rocket-launches',
		),
		array(
			'title' => 'SpaceX Launches',
			'source' => 'https://api.spacexdata.com/v3/launches',
			'template' => 'space-x-launches',
		)
	));
	add_option('tnt_data_source_selected', 'English Monarch');
}

//disattivazione del plugin
register_deactivation_hook(__FILE__, 'tnt_deactivate');
function tnt_deactivate() {
    delete_option('tnt_data_source');
}

/**
 * tnt_init
 * aggiunge i CSS
 */
add_action('wp_enqueue_scripts', 'tnt_init');
function tnt_init() {
    wp_enqueue_style('tnt', plugin_dir_url( __FILE__ ) . 'assets/css/tnt.css', array(), '1');
}

/**
 * tnt_render
 * renderizza la pagina delle impostazioni
 */
add_shortcode('tnt','tnt_render');
function tnt_render() {
    ob_start();
    $data = tnt_get_data();
    if ($data) {
        $result = '<div class="tnt">';
        switch (get_option('tnt_data_source_selected')) {
            case 'English Monarch':
                for ($i = 0; $i < count($data); $i++) {
                    $result .= '<span class="tnt__item">';
                    $result .= $data[$i]->nm . " - " . $data[$i]->cty . ' | ';
                    $result .= '</span>';
                }
            break;
            case 'Studio Ghibli Film':
				for ($i = 0; $i < count($data); $i++) {
					$result .= '<span class="tnt__item">';
					$result .= $data[$i]->title . " - " . $data[$i]->release_date . ' | ';
					$result .= '</span>';
				}	
			break;
			case 'Launch Library':
				$data = $data->launches;
				for ($i = 0; $i < count($data); $i++) {
					$result .= '<span class="tnt__item">';
					$result .= $data[$i]->name . " - " . $data[$i]->net . ' | ';
					$result .= '</span>';
				}	
			break;
			case 'SpaceX Launches':
				for ($i = 0; $i < count($data); $i++) {
					$result .= '<span class="tnt__item">';
					$result .= $data[$i]->mission_name . " - " . $data[$i]->launch_year . ' | ';
					$result .= '</span>';
				}	
			break;
        }
        $result .= "</div>";
        echo $result;
    }
    return ob_get_clean();
}
/**
 * tnt_get_data
 * recupera i dati da una fonte esterna
 */
function tnt_get_data() {
    /*$data = 'http://mysafeinfo.com/api/data?list=englishmonarchs&format=json';
    return json_decode($data);*/
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
 * aggiunge una pagina di impostazioni nel backend
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
 * renderizza la pagina delle impostazioni
 */
function tnt_render_settings_page() {
    if (isset($_POST['submit']) && wp_verify_nonce($_POST['tnt_modify_data_source_nonce'], 'tnt_modify_data_source')) {
        /*echo "inviato!";*/
		update_option('tnt_data_source_selected', $_POST['tnt_data_source']);
    }
    ?>
    <div class="wrap">
        <h2>Tiny News Ticker Settings Page</h2>
        <form method="post">
            <label>Scegli la fonte dei dati</label>
            <select name="tnt_data_source">
               
                <!--<option value="1">UNO</option>-->
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
 * funzione di servizio che estrae una sottostringa da una stringa
 */
function tnt_get_string_between($string, $start, $end) {
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}