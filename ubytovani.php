<?php
/*
Plugin Name: Ubytování
Plugin URI: https://www.zyxik.cz/
Description: <?php _e('Rezervační systém ubytovaných hostů. S formulářem, kalendářem a správou rezervací v administraci. Včetně generování faktur a exportů.', 'ubytovani'); ?>
Version: 1.4.0
Author: Radek Krejník
Author URI: https://www.zyxik.cz/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ubytovani
Domain Path: /languages
Tested up to: 6.8.1
Requires at least: 6.0
Requires PHP: 8.2
*/

defined('ABSPATH') or die('No script kiddies please!');

// Ověření platnosti licence

// Načtení překladů
add_action('plugins_loaded', function() {
    load_plugin_textdomain('ubytovani', false, dirname(plugin_basename(__FILE__)) . '/languages');
});
add_action('plugins_loaded', 'ubytovani_zkontroluj_licenci');

function ubytovani_zkontroluj_licenci() {
    $kod = get_option('ubytovani_licence_kod');
    $email = get_option('ubytovani_licence_email');

    if (empty($kod) || empty($email)) {
        return; // Neověřujeme, pokud chybí údaje
    }

    $transient_key = 'ubytovani_licence_overeno';
    $overeno = get_transient($transient_key);

   if ($overeno === false) {
    $response = wp_remote_post('https://licence.zyxik.cz/wp-json/ubytovani/v1/overit-licenci', [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode(['kod' => $kod, 'email' => $email]),
        'timeout' => 10,
    ]);

    if (!is_wp_error($response)) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($body['success'])) {
            // Při úspěšném ověření
            set_transient($transient_key, 'ano', 7 * DAY_IN_SECONDS);
            update_option('ubytovani_posledni_overeni_ok', time());
            delete_option('ubytovani_licence_neplatna'); // pro jistotu
        } else {
            // Při neúspěšném ověření odpovědí
            set_transient($transient_key, 'ne', 1 * DAY_IN_SECONDS);
        }
    } else {
        // Chyba komunikace (is_wp_error = true)
        set_transient($transient_key, 'ne', 6 * HOUR_IN_SECONDS);
    }
}

    
    $posledni_ok = get_option('ubytovani_posledni_overeni_ok');
    if ($posledni_ok && time() - $posledni_ok > 30 * DAY_IN_SECONDS) {
        update_option('ubytovani_licence_neplatna', 1);
    }    
}

    register_activation_hook(__FILE__, 'ubytovani_vytvorit_tabulku');
    require_once plugin_dir_path(__FILE__) . 'includes/funkce-licence.php';
    require_once plugin_dir_path(__FILE__) . 'includes/aktivace.php';
    require_once plugin_dir_path(__FILE__) . 'includes/zpracovani-formulare.php';
    require_once plugin_dir_path(__FILE__) . 'includes/frontend-formular.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin-zaznamy.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin-edit.php';
    require_once plugin_dir_path(__FILE__) . 'includes/nastaveni-cen.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-blokace.php';
    require_once plugin_dir_path(__FILE__) . 'includes/ajax-kalendar-data.php';
    require_once plugin_dir_path(__FILE__) . 'includes/ajax-handler.php';
    require_once plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';
    require_once plugin_dir_path(__FILE__) . 'includes/faktura-funkce.php';
    require_once plugin_dir_path(__FILE__) . 'includes/faktura-db.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin-zaloha.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin-faktura.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin-nastaveni.php';
    require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin-kniha.php';
    require_once plugin_dir_path(__FILE__) . 'includes/licence-check.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-nastaveni-licence.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
    require_once plugin_dir_path(__FILE__) . 'includes/export-pdf.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-zaznamy-edit.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-zaznamy-detail.php';

    add_action('admin_post_ubytovani_zadat_licenci', 'ubytovani_obsluzit_zadost_o_licenci');


   // načítání JavaScriptu a stylů
    function ubytovani_nacteni_skriptu_a_stylu() {
    wp_enqueue_script(
        'ubytovani-script',
        plugin_dir_url(__FILE__) . 'assets/js/script.js',
        array(),
        false,
        true
    );

    wp_localize_script('ubytovani-script', 'ubytovaniTexty', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'rezervaceOdeslana' => get_option('ubytovani_zprava_odeslano', 'Rezervace byla úspěšně odeslána.'),
        'chybaOdeslani' => __('Při odesílání nastala chyba.', 'ubytovani')
    ]);

    wp_enqueue_style(
        'ubytovani-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css'
    );
    $redirect_url = get_option('ubytovani_dekovna_stranka', site_url('/dekujeme/'));

        wp_enqueue_script(
        'ubytovani-kalendar',
        plugin_dir_url(__FILE__) . 'assets/js/kalendar.js',
        array(),
        null,
        true
    );


    wp_enqueue_script(
    'ubytovani-redirect',
    plugin_dir_url(__FILE__) . 'assets/js/redirect.js',
    array('jquery'),
    null,
    true
    );


    wp_localize_script('ubytovani-redirect', 'ubytovani_ajax', [
    'ajaxurl' => admin_url('admin-ajax.php'),
    'redirect_url' => esc_url($redirect_url),
    ]);


        }

    add_action('wp_enqueue_scripts', 'ubytovani_nacteni_skriptu_a_stylu');

    add_action('admin_enqueue_scripts', 'ubytovani_admin_styly');

function ubytovani_admin_styly($hook) {
    // Volitelně omezit na konkrétní admin stránku (např. pouze záznamy)
    // if ($hook !== 'toplevel_page_ubytovani-zaznamy') return;

    wp_enqueue_style(
        'ubytovani-admin-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css')
    );
}


    // ✉️ Oprava jména a adresy odesílatele
    add_filter('wp_mail_from_name', function () {
    return get_option('ubytovani_email_jmeno', get_bloginfo('name'));
    });

    add_filter('wp_mail_from', function () {
    return get_option('ubytovani_email_adresa', get_option('admin_email'));
    });

    // 📩 Odeslání zálohové výzvy
    add_action('admin_post_ubytovani_odeslat_zalohu', 'ubytovani_odeslat_zalohu');

    // 🔁 Plugin Update Checker – verze 5.6// 

     //use YahnisElsts\PluginUpdateChecker\v5p6\PucFactory;

    $updateChecker = \YahnisElsts\PluginUpdateChecker\v5p6\PucFactory::buildUpdateChecker(
    'https://update.zyxik.cz/metadata.json',
    __FILE__,
    'ubytovani'
    );


    // licenční aktivace
    register_activation_hook(__FILE__, 'ubytovani_zaznamenat_aktivaci');

    function ubytovani_zaznamenat_aktivaci() {
    if (!get_option('ubytovani_datum_aktivace')) {
        update_option('ubytovani_datum_aktivace', current_time('timestamp'));
    }
}