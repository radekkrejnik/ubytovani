<?php
add_action('admin_menu', function () {
    
});

function ubytovani_upravit_zaznam() {
    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if (isset($_POST['ubytovani_ulozit'])) {
        $wpdb->update($tabulka, array(
            'datum_prijezdu' => sanitize_text_field($_POST['datum_prijezdu']),
            'datum_odjezdu' => sanitize_text_field($_POST['datum_odjezdu']),
            'pocet_noci' => intval($_POST['pocet_noci']),
            'jmeno' => sanitize_text_field($_POST['jmeno']),
            'prijmeni' => sanitize_text_field($_POST['prijmeni']),
            'firma' => sanitize_text_field($_POST['firma']),
            'ico' => sanitize_text_field($_POST['ico']),
            'dic' => sanitize_text_field($_POST['dic']),
            'adresa' => sanitize_text_field($_POST['adresa']),
            'stat' => sanitize_text_field($_POST['stat']),
            'telefon' => sanitize_text_field($_POST['telefon']),
            'email' => sanitize_email($_POST['email']),
            'cislo_op' => sanitize_text_field($_POST['cislo_op']),
            'datum_narozeni' => sanitize_text_field($_POST['datum_narozeni']),
            'cena' => floatval($_POST['cena']),
            'stav' => sanitize_text_field($_POST['stav']),
            'poznamka' => sanitize_textarea_field($_POST['poznamka']),
        ), array('id' => $id));

        echo '<div class="updated"><p>' . esc_html__('Záznam byl úspěšně upraven.', 'ubytovani') . '</p></div>';
    }

    $zaznam = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));

    if (!$zaznam) {
        echo '<div class="wrap"><h2>' . esc_html__('Chyba', 'ubytovani') . '</h2><p>' . esc_html__('Záznam nebyl nalezen.', 'ubytovani') . '</p></div>';
        return;
    }

    $preklady = [
        'datum_prijezdu' => __('Datum příjezdu', 'ubytovani'),
        'datum_odjezdu' => __('Datum odjezdu', 'ubytovani'),
        'pocet_noci' => __('Počet nocí', 'ubytovani'),
        'jmeno' => __('Jméno', 'ubytovani'),
        'prijmeni' => __('Příjmení', 'ubytovani'),
        'firma' => __('Firma', 'ubytovani'),
        'ico' => __('IČO', 'ubytovani'),
        'dic' => __('DIČ', 'ubytovani'),
        'adresa' => __('Adresa', 'ubytovani'),
        'stat' => __('Stát', 'ubytovani'),
        'telefon' => __('Telefon', 'ubytovani'),
        'email' => __('E-mail', 'ubytovani'),
        'cislo_op' => __('Číslo OP', 'ubytovani'),
        'datum_narozeni' => __('Datum narození', 'ubytovani'),
        'cena' => __('Cena (Kč)', 'ubytovani'),
        'poznamka' => __('Poznámka', 'ubytovani'),
    ];

    echo '<div class="wrap"><h1>' . esc_html__('Upravit záznam', 'ubytovani') . '</h1>';
    echo '<form method="post">';
    echo '<table class="form-table">';

    foreach ($zaznam as $klic => $hodnota) {
        if ($klic === 'id' || $klic === 'stav') continue;

        $label = isset($preklady[$klic]) ? $preklady[$klic] : esc_html(ucfirst(str_replace('_', ' ', $klic)));
        echo '<tr><th><label for="' . esc_attr($klic) . '">' . esc_html($label) . '</label></th>';
        echo '<td><input type="text" name="' . esc_attr($klic) . '" id="' . esc_attr($klic) . '" value="' . esc_attr($hodnota) . '" class="regular-text"></td></tr>';
    }

    echo '<tr><th><label for="stav">' . esc_html__('Stav rezervace:', 'ubytovani') . '</label></th><td>';
    echo '<select name="stav" id="stav">';
    echo '<option value="Čeká na schválení"' . selected($zaznam->stav, 'Čeká na schválení', false) . '>' . esc_html__('Čeká na schválení', 'ubytovani') . '</option>';
    echo '<option value="Schváleno"' . selected($zaznam->stav, 'Schváleno', false) . '>' . esc_html__('Schváleno', 'ubytovani') . '</option>';
    echo '</select>';
    echo '</td></tr>';

    echo '</table>';
    echo '<p><input type="submit" name="ubytovani_ulozit" class="button-primary" value="' . esc_attr__(esc_attr__( 'Uložit změny', 'ubytovani' ), 'ubytovani') . '">';
    echo ' <a href="' . esc_url(admin_url('admin.php?page=ubytovani-zaznamy')) . '" class="button">' . esc_html__('Zpět', 'ubytovani') . '</a></p>';
    echo '</form>';
    echo '</div>';
}
