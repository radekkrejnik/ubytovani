<?php
use Dompdf\Dompdf;
use Dompdf\Options;
// admin-kniha.php

add_action('admin_menu', function () {
   
});

function ubytovani_stranka_kniha_hostu_html() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Nemáte oprávnění.', 'ubytovani'));
    }

    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

    $rok = isset($_GET['rok']) ? intval($_GET['rok']) : date('Y');
    $od = $rok . '-01-01';
    $do = $rok . '-12-31';

    $zaznamy = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $tabulka WHERE datum_prijezdu BETWEEN %s AND %s ORDER BY datum_prijezdu ASC", $od, $do)
    );

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Kniha hostů', 'ubytovani') . '</h1>';

    // Jeden formulář pro výběr roku a zobrazení + tlačítko PDF
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="ubytovani-kniha-hostu">';
    echo '<label for="rok">' . esc_html__('Vyber rok:', 'ubytovani') . '</label> ';
    echo '<select name="rok" id="rok">';
    $soucasny = date('Y');
    for ($r = $soucasny; $r >= $soucasny - 10; $r--) {
        $sel = ($rok == $r) ? 'selected' : '';
        echo "<option value='$r' $sel>$r</option>";
    }
    echo '</select> ';
    submit_button(__('Zobrazit', 'ubytovani'), 'secondary', '', false);
    echo '</form>';

    // Tlačítko PDF – odkaz, který přebírá zvolený rok
    echo '<form method="get" action="' . esc_url(admin_url('admin-post.php')) . '" target="_blank" style="margin-top: 1em;">';
    echo '<input type="hidden" name="action" value="ubytovani_kniha_hostu_pdf">';
    echo '<input type="hidden" name="rok" value="' . esc_attr($rok) . '">';
    submit_button(__('Stáhnout PDF', 'ubytovani'));
    echo '</form>';

    if (!empty($zaznamy)) {
        echo '<table class="widefat striped" style="margin-top: 1.5em;">';
        echo '<thead><tr>
                <th>' . esc_html__('Příjezd', 'ubytovani') . '</th>
                <th>' . esc_html__('Odjezd', 'ubytovani') . '</th>
                <th>' . esc_html__('Jméno', 'ubytovani') . '</th>
                <th>' . esc_html__('Adresa', 'ubytovani') . '</th>
                <th>' . esc_html__('Stát', 'ubytovani') . '</th>
                <th>' . esc_html__('OP', 'ubytovani') . '</th>
                <th>' . esc_html__('Narozen', 'ubytovani') . '</th>
            </tr></thead><tbody>';
        foreach ($zaznamy as $zaznam) {
            echo '<tr>';
            echo '<td>' . esc_html(date_i18n('j.n.Y', strtotime($zaznam->datum_prijezdu))) . '</td>';
            echo '<td>' . esc_html(date_i18n('j.n.Y', strtotime($zaznam->datum_odjezdu))) . '</td>';
            echo '<td>' . esc_html($zaznam->jmeno . ' ' . $zaznam->prijmeni) . '</td>';
            echo '<td>' . esc_html($zaznam->adresa) . '</td>';
            echo '<td>' . esc_html($zaznam->stat) . '</td>';
            echo '<td>' . esc_html($zaznam->cislo_op) . '</td>';
            echo '<td>' . esc_html(date_i18n('j.n.Y', strtotime($zaznam->datum_narozeni))) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>' . esc_html__('Pro vybraný rok nebyly nalezeny žádné záznamy.', 'ubytovani') . '</p>';
    }

    echo '</div>';
}

// Export knihy hostů do PDF
add_action('admin_post_ubytovani_kniha_hostu_pdf', 'ubytovani_kniha_hostu_pdf');
function ubytovani_kniha_hostu_pdf() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Nemáte oprávnění.', 'ubytovani'));
    }

    if (!isset($_GET['rok'])) {
        wp_die(__('Chybí rok exportu.', 'ubytovani'));
    }

    $rok = intval($_GET['rok']);
    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

    $zaznamy = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $tabulka WHERE YEAR(datum_prijezdu) = %d ORDER BY datum_prijezdu ASC",
            $rok
        )
    );

    if (empty($zaznamy)) {
        wp_die(__('Žádné záznamy pro zvolený rok.', 'ubytovani'));
    }

    
    // Načti knihovnu DOMPDF
    require_once plugin_dir_path(__FILE__) . '../libs/dompdf/autoload.inc.php';
   

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'dejavu sans');
    $dompdf = new Dompdf($options);

    ob_start();
    echo '<h1>Kniha hostů – ' . esc_html($rok) . '</h1>';
    echo '<table border="1" cellpadding="4" cellspacing="0" width="100%">';
    echo '<thead><tr>
        <th>Datum příjezdu</th><th>Datum odjezdu</th><th>Jméno</th><th>Příjmení</th><th>Stát</th><th>Datum narození</th><th>Číslo OP</th>
    </tr></thead><tbody>';

    foreach ($zaznamy as $z) {
        echo '<tr>';
        echo '<td>' . date('d.m.Y', strtotime($z->datum_prijezdu)) . '</td>';
        echo '<td>' . date('d.m.Y', strtotime($z->datum_odjezdu)) . '</td>';
        echo '<td>' . esc_html($z->jmeno) . '</td>';
        echo '<td>' . esc_html($z->prijmeni) . '</td>';
        echo '<td>' . esc_html($z->stat) . '</td>';
        echo '<td>' . date('d.m.Y', strtotime($z->datum_narozeni)) . '</td>';
        echo '<td>' . esc_html($z->cislo_op) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    $html = ob_get_clean();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('kniha_hostu_' . $rok . '.pdf', ['Attachment' => true]);
    exit;
}
