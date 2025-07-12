<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'ubytovani_zaregistrovat_nastaveni_ceny');

function ubytovani_zaregistrovat_nastaveni_ceny() {
    register_setting('ubytovani_nastaveni_ceny', 'ubytovani_poplatek_osoba');

    for ($i = 1; $i <= 10; $i++) {
        register_setting('ubytovani_nastaveni_ceny', 'ubytovani_cena_' . $i . '_osoba');
    }

    // Sekce
    add_settings_section(
        'ubytovani_ceny_sekce',
        __('Nastavení cen podle počtu osob', 'ubytovani'),
        '__return_null',
        'ubytovani-ceny'
    );
    // Pole: turistický poplatek
    add_settings_field(
        'ubytovani_poplatek_osoba',
        __('Turistický poplatek za osobu / noc', 'ubytovani'),
        'ubytovani_vykreslit_poplatek_osoba',
        'ubytovani-ceny',
        'ubytovani_ceny_sekce'
    );
    // Pole: ceny za noc
    add_settings_field(
        'ubytovani_ceny_dynamicke',
        __('Ceny za noc podle počtu osob', 'ubytovani'),
        'ubytovani_vykreslit_pole_cen',
        'ubytovani-ceny',
        'ubytovani_ceny_sekce'
    );

}

// 🟢 Funkce pro vykreslení pole pro turistický poplatek
function ubytovani_vykreslit_poplatek_osoba() {
    $value = esc_attr(get_option('ubytovani_poplatek_osoba', 30));
    echo '<input type="number" name="ubytovani_poplatek_osoba" value="' . $value . '" step="1" min="0"> Kč / osoba / noc';
}

// 🟢 Výstup HTML stránky
function ubytovani_stranka_ceny_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Nastavení cen', 'ubytovani') . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('ubytovani_nastaveni_ceny');
    do_settings_sections('ubytovani-ceny');
    submit_button(__('Uložit', 'ubytovani'));
    echo '</form>';
    echo '</div>';
}
// 🟢 Funkce pro vykreslení ceny podle počtu osob
function ubytovani_vykreslit_pole_cen() {
    echo '<p class="description">';
    echo __('Zadejte cenu za noc podle počtu osob (1–10). Pokud některé počty osob nejsou využívány, pole ponechte prázdné.', 'ubytovani');
    echo '</p>';

    echo '<table class="form-table"><tbody>';
    for ($i = 1; $i <= 10; $i++) {
        $option_name = 'ubytovani_cena_' . $i . '_osoba';
        $value = esc_attr(get_option($option_name, ''));
        echo '<tr>';
        echo '<th scope="row">' . sprintf(__('Cena pro %d %s', 'ubytovani'), $i, ($i == 1 ? 'osobu' : 'osob')) . '</th>';
        echo '<td><input type="number" name="' . $option_name . '" value="' . $value . '" step="1" min="0"> Kč / noc</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}