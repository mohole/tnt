<?php
/*
Plugin Name: Tiny New Ticker (TNT)
Plugin URI: http://www.mohole.it
Description: Semplice news ticker che recupera dati da una fonte json e li mostra in un widget scorrevole.
Version: 1.0
Author: Scuola Mohole
Author URI: https://scuola.mohole.it
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: TNT
*/

//Termino l'esecuzione se il plugin è richiamato direttamente e non tramite da wordpress
if(!defined('WPINC')) die;

add_action('wp_enqueue_scripts', 'tnt_init');

function tnt_init(){
    wp_enqueue_style('tnt', plugin_dir_url( __FILE__ ) . 'assets/css/tnt.css', array() , '1');
}
function tnt_reader(){
    ob_start(); //questo e le ultime funzioni permettono all'utente di inserire il plug dove vuole nella pagina
    ?>
    <!-- <div class="tnt">
        <div class="tnt__item">UNO</div>
        <div class="tnt__item">DUE</div>
        <div class="tnt__item">TRE</div>
    </div> -->
    <?php
   
    $data=tnt_get_data();
    if($data){
        $result="";
        ?>
        <div class="tnt">
        <?php
        for($i=0; $i<count($data) ;$i++){
            //var_dump($data[$i]);//stampa il contenuto dicendo che cosa è
            /* $result .= "<div class='tnt__item'>".$data[$i]->nm." - ".$data[$i]->dsc."</div><br>";       */
            ?>
            <div class="tnt__item">
                <?php echo $data[$i]->nm." - ".$data[$i]->dsc; ?>
            </div>
            <?php
        }
        echo $result;
        ?>
        </div><?php
       
    }
    
    $html=ob_get_contents();
    ob_end_clean();
    return $html;
}
add_shortcode( 'tnt', 'tnt_reader');

function tnt_get_data(){
    
        $data='[
            {
                "nm": "Back Door",
                "dsc": "Opening left in a functional piece of software that allows unknown entry into the system / or application without the owners knowledge."
                },
                {
                "nm": "Birthday",
                "dsc": "A name used to refer to a class of brute-force attacks"
                },
                {
                "nm": "Brute Force",
                "dsc": "Will try every single key combination known to crack your password."
                },
                {
                "nm": "Buffer Overflow",
                "dsc": "Attacks take advantage of poorly written code"
                }
        ]';
        return json_decode($data);
}
add_action('admin_menu','tnt_add_settings_page');
function tnt_add_settings_page(){
    add_options_page(
        'Tiny News Ticker',
        'TNT',
        'manage_options',
        'test_setting_page',
        'tnt_render_settings_page'
    );
}

function tnt_render_settings_page(){
    if(isset($_POST['submit']) && wp_verify_nonce($_POST['tnt_modify_data_source_nonce'],'tnt_modify_data_source')){
        echo "inviato";
    }
    ?>
    <div class="wrap">
        <h2>Tiny News Settings Page</h2>
        <form method="post">
            <label>Scegli la fonte dati</label>
            <select name="tnt_data_source">
                <option value="1">UNO</option>
                <option value="2">DUE</option>
            
            </select>
        <?php wp_nonce_field('tnt_modify_data_source', 'tnt_modify_data_source_nonce'); ?>
        <?php submit_button(); ?>
        </form>
    </div>
    <?php

 

}
register_activation_hook(__FILE__, 'tnt_activate');
function tnt_activate() {
    add_option('tnt_data_source', 'TEST');
}
register_deactivation_hook(__FILE__, 'tnt_deactivate');
function tnt_deactivate() {
    delete_option('tnt_data_source');
}

/* register_activation_hook(__FILE__,'tnt_activate');
function tnt_activate(){
    add_option('tnt_data_source','TEST');
}
register_deactivation_hook(__FILE__,'tnt_deactivate');
function tnt_deactivate(){
    delete_option('tnt_data_source');
} */
