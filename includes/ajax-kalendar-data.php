<?php
add_action('wp_ajax_nacti_rezervace', 'ubytovani_nacti_rezervace');
add_action('wp_ajax_nopriv_nacti_rezervace', 'ubytovani_nacti_rezervace');

function ubytovani_nacti_rezervace() {
    if (ob_get_length()) ob_clean(); // ← přidej tento řádek jako první

    if (!isset($_GET['pokoj_id'])) {
        wp_send_json_error(['message' => __('Chybí ID pokoje', 'ubytovani')]);
    }

    $pokoj_id = intval($_GET['pokoj_id']);

    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

    $vysledky = $wpdb->get_results(
       $wpdb->prepare("SELECT datum_prijezdu, datum_odjezdu, stav, pokoj_id FROM $tabulka WHERE pokoj_id = %d", $pokoj_id)
    );

    $data = array_map(function($zaznam) {
        $stav_normalizovany = mb_strtolower(trim($zaznam->stav), 'UTF-8');
        $stav_normalizovany = str_replace(' ', '_', $stav_normalizovany);

        return [
            'od' => $zaznam->datum_prijezdu,
            'do' => $zaznam->datum_odjezdu,
            'stav' => $stav_normalizovany,
            'pokoj_id' => $zaznam->pokoj_id
        ];
    }, $vysledky);

    wp_send_json_success($data);
}
