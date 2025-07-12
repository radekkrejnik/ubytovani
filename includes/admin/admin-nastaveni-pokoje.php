<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'ubytovani_zaregistrovat_nastaveni_pokoje');

function ubytovani_zaregistrovat_nastaveni_pokoje() {
    register_setting('ubytovani_nastaveni_pokoje', 'ubytovani_pokoje_nastaveni');

    add_settings_section(
        'ubytovani_pokoje_sekce',
        __('Nastavení pokojů', 'ubytovani'),
        '__return_null',
        'ubytovani-pokoje'
    );

    add_settings_field(
        'ubytovani_pokoje_nastaveni',
        __('Počet osob na pokoj', 'ubytovani'),
        'ubytovani_vykreslit_nastaveni_pokoju',
        'ubytovani-pokoje',
        'ubytovani_pokoje_sekce'
    );
}

function ubytovani_vykreslit_nastaveni_pokoju() {
    $pocet_pokoju = intval(get_option('ubytovani_pocet_pokoju', 1));
    $stav = function_exists('ubytovani_ziskej_stav_licence') ? ubytovani_ziskej_stav_licence() : 'zkusebni';

    if ($stav !== 'aktivni') {
        $pocet_pokoju = 1;
    }

    $data = get_option('ubytovani_pokoje_nastaveni', []);

    echo '<table class="widefat fixed striped">';
    echo '<thead><tr>';
    echo '<th>' . __('Pokoj', 'ubytovani') . '</th>';
    echo '<th>' . __('Shortcode kalendáře', 'ubytovani') . '</th>';
    echo '<th>' . __('Shortcode formuláře', 'ubytovani') . '</th>';
    echo '<th>' . __('Počet osob', 'ubytovani') . '</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    for ($i = 1; $i <= $pocet_pokoju; $i++) {
        $osoby = isset($data[$i]) ? intval($data[$i]) : 2;
        echo '<tr>';
        echo '<td><strong>' . __('Pokoj', 'ubytovani') . ' ' . $i . '</strong></td>';
        echo '<td><code>[ubytovani_kalendar pokoj="' . $i . '"]</code></td>';
        echo '<td><code>[ubytovani_formular pokoj="' . $i . '"]</code></td>';
        echo '<td>';
        echo '<select name="ubytovani_pokoje_nastaveni[' . $i . ']">';
        for ($j = 1; $j <= 10; $j++) {
            $selected = selected($osoby, $j, false);
            echo "<option value='$j' $selected>$j</option>";
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    echo '<div style="text-align: center; margin-top: 20px;">';
    echo '<p class="description">';
    echo __('Pro zobrazení všech pokojů na jedné stránce (včetně kalendářů a formulářů) použijte tento shortcode:', 'ubytovani');
    echo ' <code>[ubytovani_vse]</code>';
    echo '</p>';
    echo '</div>';
}

function ubytovani_stranka_pokoje_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Nastavení pokojů', 'ubytovani') . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('ubytovani_nastaveni_pokoje');
    do_settings_sections('ubytovani-pokoje');
    submit_button(__('Uložit', 'ubytovani'));
    echo '</form>';
    echo '</div>';
}
