<?php
// Blokace přímého přístupu
defined('ABSPATH') || exit;

function ubytovani_zkontroluj_pristup_k_pokoji($id_pokoje) {
    $stav = ubytovani_ziskej_stav_licence();

    if ($stav === 'aktivni') {
        return true;
    }

    return ($id_pokoje == 1); // Povolit pouze pokoj č. 1
}

// Funkce pro výstup jednoho kalendáře
function ubytovani_zobrazit_kalendar($atts) {
    $stav = function_exists('ubytovani_ziskej_stav_licence') ? ubytovani_ziskej_stav_licence() : 'zkusebni';

    $atts = shortcode_atts(['pokoj' => 1], $atts);
    $pokoj_id = intval($atts['pokoj']);

    if ($stav !== 'aktivni' && $pokoj_id > 1) {
        return '<p style="color:red;">' . __('Tento kalendář je dostupný pouze v plné verzi pluginu. Aktuálně lze používat pouze pokoj č. 1.', 'ubytovani') . '</p>';
    }

    $nastaveni = get_option('ubytovani_pokoje_nastaveni', []);
    $max_osob = isset($nastaveni[$pokoj_id]) ? intval($nastaveni[$pokoj_id]) : 2;

    ob_start();
echo '<div id="ubytovani-kalendar-' . esc_attr($pokoj_id) . '" class="ubytovani-kalendar" data-pokoj="' . esc_attr($pokoj_id) . '" data-max-osob="' . esc_attr($max_osob) . '">';
echo '<div class="kalendar-nav-wrapper">';
echo '<button class="kalendar-prev">&#8592;</button>';
echo '<button class="kalendar-next">&#8594;</button>';
echo '</div>';
echo '<div class="kalendar-mesice"></div>';
echo '</div>';

echo '<div class="kalendar-legenda">';
echo '<strong>' . __('Legenda:', 'ubytovani') . '</strong>';

echo '<span class="legenda-polozka legenda-vyber"><span class="legenda-barva"></span>' . __('vybrané termíny', 'ubytovani') . '</span>';
echo '<span class="legenda-polozka legenda-ceka"><span class="legenda-barva"></span>' . __('čeká na schválení', 'ubytovani') . '</span>';
echo '<span class="legenda-polozka legenda-schvaleno"><span class="legenda-barva"></span>' . __('potvrzená rezervace', 'ubytovani') . '</span>';
echo '<span class="legenda-polozka legenda-blokovano"><span class="legenda-barva"></span>' . __('uzavřený termín', 'ubytovani') . '</span>';

echo '</div>';


return ob_get_clean();
}

// Funkce pro zobrazení všech kalendářů podle nastavení
function ubytovani_zobrazit_vsechny_kalendare() {
    $stav = function_exists('ubytovani_ziskej_stav_licence') ? ubytovani_ziskej_stav_licence() : 'zkusebni';
    $nastaveni = get_option('ubytovani_pokoje_nastaveni', []);

    if (empty($nastaveni)) return '';

    $vystup = '';
    foreach ($nastaveni as $pokoj_id => $max_osob) {
        if ($stav !== 'aktivni' && $pokoj_id > 1) continue;

        $vystup .= '<h3>' . sprintf(__('Pokoj %d', 'ubytovani'), $pokoj_id) . '</h3>';
        $vystup .= do_shortcode('[ubytovani_kalendar pokoj="' . esc_attr($pokoj_id) . '"]');
        $vystup .= do_shortcode('[ubytovani_formular pokoj="' . esc_attr($pokoj_id) . '"]');
    }
    return $vystup;
}


// Registrace shortcodů
function ubytovani_registruj_shortcody() {
    add_shortcode('ubytovani_kalendar', 'ubytovani_zobrazit_kalendar');
    add_shortcode('ubytovani_formular', 'ubytovani_zobrazit_formular');
    add_shortcode('ubytovani_vse', 'ubytovani_zobrazit_vsechny_kalendare');
}

add_action('init', 'ubytovani_registruj_shortcody');

function ubytovani_licence_neplatna_zprava() {
    return '<div style="border:1px solid #cc0000; padding:15px; background:#ffe6e6;">' .
           __('Tento obsah je dostupný pouze s platnou licencí pluginu Ubytování.', 'ubytovani') .
           '</div>';
}
