<?php
require_once('/data/www/24798/zyxik_cz/byt37/wp-load.php');
require_once plugin_dir_path(__FILE__) . 'libs/dompdf/autoload.inc.php';

if (!is_user_logged_in()) {
    die(esc_html__('Uživatel není přihlášen.', 'ubytovani'));
}

echo esc_html__('WordPress je načten.', 'ubytovani') . '<br>';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!current_user_can('manage_options')) {
    wp_die(esc_html__('Neautorizovaný přístup.', 'ubytovani'));
}

if (!isset($_GET['rok'])) {
    wp_die(esc_html__('Není zadán rok.', 'ubytovani'));
}

$rok = intval($_GET['rok']);
global $wpdb;
$tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

$zaznamy = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $tabulka WHERE YEAR(datum_prijezdu) = %d ORDER BY datum_prijezdu ASC",
    $rok
));

$html = sprintf('<h2 style="text-align: center;">%s</h2>', sprintf( /* translators: %d: Rok */ __('Byt číslo 37 – kniha hostů (%d)', 'ubytovani'), $rok ));
$html .= '<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        table-layout: fixed;
    }
    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
        vertical-align: top;
        word-break: break-word;
    }
    th {
        background-color: #f0f0f0;
    }
</style>';

if (empty($zaznamy)) {
    $html .= '<p>' . __('Žádné záznamy pro tento rok.', 'ubytovani') . '</p>';
} else {
    $html .= '<table><thead><tr>
        <th>#</th>
        <th>' . __('Datum příjezdu', 'ubytovani') . '</th>
        <th>' . __('Datum odjezdu', 'ubytovani') . '</th>
        <th>' . __('Počet nocí', 'ubytovani') . '</th>
        <th>' . __('Jméno', 'ubytovani') . '</th>
        <th>' . __('Příjmení', 'ubytovani') . '</th>
        <th>' . __('Název firmy', 'ubytovani') . '</th>
        <th>' . __('IČO', 'ubytovani') . '</th>
        <th>' . __('DIČ', 'ubytovani') . '</th>
        <th>' . __('Adresa', 'ubytovani') . '</th>
        <th>' . __('Stát', 'ubytovani') . '</th>
        <th>' . __('Telefon', 'ubytovani') . '</th>
        <th>' . __('Email', 'ubytovani') . '</th>
        <th>' . __('Číslo OP', 'ubytovani') . '</th>
        <th>' . __('Datum narození', 'ubytovani') . '</th>
        <th>' . __('Poznámka', 'ubytovani') . '</th>
    </tr></thead><tbody>';

    $poradi = 1;
    foreach ($zaznamy as $z) {
        $html .= '<tr>';
        $html .= '<td>' . $poradi++ . '</td>';
        $html .= '<td>' . date('d.m.Y', strtotime($z->datum_prijezdu)) . '</td>';
        $html .= '<td>' . date('d.m.Y', strtotime($z->datum_odjezdu)) . '</td>';
        $html .= '<td>' . intval($z->pocet_noci) . '</td>';
        $html .= '<td>' . esc_html($z->jmeno) . '</td>';
        $html .= '<td>' . esc_html($z->prijmeni) . '</td>';
        $html .= '<td>' . esc_html($z->firma) . '</td>';
        $html .= '<td>' . esc_html($z->ico) . '</td>';
        $html .= '<td>' . esc_html($z->dic) . '</td>';
        $html .= '<td>' . esc_html($z->adresa) . '</td>';
        $html .= '<td>' . esc_html($z->stat) . '</td>';
        $html .= '<td>' . esc_html($z->telefon) . '</td>';
        $html .= '<td>' . esc_html($z->email) . '</td>';
        $html .= '<td>' . esc_html($z->cislo_op) . '</td>';
        $html .= '<td>' . date('d.m.Y', strtotime($z->datum_narozeni)) . '</td>';
        $html .= '<td>' . esc_html($z->poznamka) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
}

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

ob_end_clean();
$dompdf->stream('ubytovani_' . $rok . '.pdf', ['Attachment' => 1]);
exit;
