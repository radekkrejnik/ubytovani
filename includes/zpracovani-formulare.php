<?php
add_action('admin_post_nopriv_ubytovani_odeslat', 'ubytovani_zpracuj_formular');
add_action('admin_post_ubytovani_odeslat', 'ubytovani_zpracuj_formular');

function ubytovani_zpracuj_formular() {
    if (!isset($_POST['email']) || !isset($_POST['datum_prijezdu'])) {
        wp_die(esc_html__('Neplatný požadavek.', 'ubytovani'));
    }

    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

    $wpdb->insert($tabulka, array(
        'datum_prijezdu'   => sanitize_text_field($_POST['datum_prijezdu']),
        'datum_odjezdu'    => sanitize_text_field($_POST['datum_odjezdu']),
        'pocet_noci'       => intval($_POST['pocet_noci']),
        'jmeno'            => sanitize_text_field($_POST['jmeno']),
        'prijmeni'         => sanitize_text_field($_POST['prijmeni']),
        'firma'            => sanitize_text_field($_POST['firma']),
        'ico'              => sanitize_text_field($_POST['ico']),
        'dic'              => sanitize_text_field($_POST['dic']),
        'adresa'           => sanitize_text_field($_POST['adresa']),
        'stat'             => sanitize_text_field($_POST['stat']),
        'telefon'          => sanitize_text_field($_POST['telefon']),
        'email'            => sanitize_email($_POST['email']),
        'cislo_op'         => sanitize_text_field($_POST['cislo_op']),
        'datum_narozeni'   => sanitize_text_field($_POST['datum_narozeni']),
        'cena'             => floatval($_POST['cena']),
        'poznamka'         => sanitize_textarea_field($_POST['poznamka']),
        'stav'             => __('Čeká na schválení', 'ubytovani'),
    ));

    $redirect_url = get_option('ubytovani_dekovna_stranka');

if (!empty($redirect_url)) {
    $redirect_url = add_query_arg([
        'jmeno' => sanitize_text_field($_POST['jmeno']),
        'prijmeni' => sanitize_text_field($_POST['prijmeni']),
        'email' => sanitize_email($_POST['email']),
        'telefon' => sanitize_text_field($_POST['telefon']),
        'datum_odjezdu' => sanitize_text_field($_POST['datum_odjezdu']),
        'datum_prijezdu' => sanitize_text_field($_POST['datum_prijezdu']),
        'pocet_osob' => intval($_POST['pocet_osob']),
    ], $redirect_url);

    wp_redirect($redirect_url);
    exit;
} else {
    // Pokud není URL nastaveno, jen zpět na domovskou stránku nebo potvrzení
    wp_redirect(home_url());
    exit;
}

}