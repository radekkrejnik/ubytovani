<?php
require_once plugin_dir_path(__FILE__) . '../libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Akce pro stažení PDF faktury
add_action('admin_post_ubytovani_stazeni_faktury', 'ubytovani_stazeni_faktury');

function ubytovani_stazeni_faktury() {
    // Kontrola oprávnění
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Neautorizovaný přístup.', 'ubytovani'));
    }

    // Kontrola a načtení ID
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        wp_die(esc_html__('Neplatné nebo chybějící ID rezervace.', 'ubytovani'));
    }

    // Načtení knihoven
    require_once plugin_dir_path(__FILE__) . 'faktura-funkce.php';

    // Načtení rezervace
    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';
    $rezervace = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));

    if (!$rezervace) {
        wp_die(esc_html__('Rezervace nebyla nalezena.', 'ubytovani'));
    }

    // Vytvoření HTML faktury
    $html = ubytovani_vytvor_fakturu_html($rezervace, $id);

    // Inicializace DomPDF
    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Vyčištění výstupu a stažení PDF
    if (ob_get_length()) ob_end_clean();
    $dompdf->stream('faktura_rezervace_' . $id . '.pdf', ['Attachment' => true]);
    exit;
}
// Akce pro export knihy hostů (PDF)
add_action('admin_post_ubytovani_kniha_hostu', 'ubytovani_exportovat_knihu_hostu_pdf');

function ubytovani_exportovat_knihu_hostu_pdf() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Neautorizovaný přístup.', 'ubytovani'));
    }

    if (!isset($_GET['rok'])) {
        wp_die(esc_html__('Není zadán rok.', 'ubytovani'));
    }


    global $wpdb;
    $rok = intval($_GET['rok']);
    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';

    $zaznamy = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $tabulka WHERE YEAR(datum_prijezdu) = %d ORDER BY datum_prijezdu ASC",
        $rok
    ));

    $html = '<h2 style="text-align: center;">' . __('Byt číslo 37 – kniha hostů', 'ubytovani') . ' (' . $rok . ')</h2>';
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

    if (ob_get_length()) ob_end_clean();
    $dompdf->stream('ubytovani_' . $rok . '.pdf', ['Attachment' => 1]);
    exit;
}
