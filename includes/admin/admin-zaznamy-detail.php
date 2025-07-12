<?php
// includes/admin/admin-zaznamy-detail.php – upravená verze (přehlednější vzhled)

defined('ABSPATH') || exit;

add_action(
    'admin_menu',
    function () {
        // Tuto stránku přidává hlavní registrující funkce ubytovani, takže zde není třeba nic.
    }
);

/**
 * Vykreslení jednoho řádku detailu (label + hodnota)
 */
if (!function_exists('ubytovani_vykresli_radek')) {
    function ubytovani_vykresli_radek(string $label, string $value): void
    {
        if ($value === '') {
            return; // prázdné hodnoty neukazujeme
        }
        echo '<tr class="ub-row">';
        echo '<th class="ub-label">' . esc_html($label) . '</th>';
        echo '<td class="ub-value">' . esc_html($value) . '</td>';
        echo '</tr>';
    }
}

/**
 * Stránka detailu rezervace
 */
function ubytovani_stranka_detail(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Nemáte oprávnění.', 'ubytovani'));
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        echo '<div class="wrap"><h2 class="notice notice-error">' . esc_html__('Chybí ID záznamu.', 'ubytovani') . '</h2></div>';
        return;
    }

    global $wpdb;
    $tabulka  = $wpdb->prefix . 'ubytovani_zaznamy';
    $zaznam   = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabulka WHERE id = %d", $id));

    if (!$zaznam) {
        echo '<div class="wrap"><h2 class="notice notice-error">' . esc_html__('Záznam nenalezen.', 'ubytovani') . '</h2></div>';
        return;
    }

    // Překlady
    $labels = [
        'jmeno'            => __('Jméno', 'ubytovani'),
        'prijmeni'         => __('Příjmení', 'ubytovani'),
        'email'            => __('E‑mail', 'ubytovani'),
        'telefon'          => __('Telefon', 'ubytovani'),
        'adresa'           => __('Adresa', 'ubytovani'),
        'stat'             => __('Stát', 'ubytovani'),
        'cislo_op'         => __('Číslo OP', 'ubytovani'),
        'datum_narozeni'   => __('Datum narození', 'ubytovani'),
        'firma'            => __('Firma', 'ubytovani'),
        'ico'              => __('IČO', 'ubytovani'),
        'dic'              => __('DIČ', 'ubytovani'),
        'datum_prijezdu'   => __('Datum příjezdu', 'ubytovani'),
        'datum_odjezdu'    => __('Datum odjezdu', 'ubytovani'),
        'pocet_noci'       => __('Počet nocí', 'ubytovani'),
        'pocet_osob'       => __('Počet osob', 'ubytovani'),
        'cena'             => __('Cena', 'ubytovani'),
        'stav'             => __('Stav rezervace', 'ubytovani'),
        'poznamka'         => __('Poznámka', 'ubytovani'),
    ];

    // Formátování
    $zaznam->datum_prijezdu = date_i18n('j. n. Y', strtotime($zaznam->datum_prijezdu));
    $zaznam->datum_odjezdu  = date_i18n('j. n. Y', strtotime($zaznam->datum_odjezdu));
    if ($zaznam->datum_narozeni) {
        $zaznam->datum_narozeni = date_i18n('j. n. Y', strtotime($zaznam->datum_narozeni));
    }

    // Hlavní výstup
    echo '<div class="wrap">';
    echo '<h1>' . sprintf(__('Detail rezervace č. %d', 'ubytovani'), $id) . '</h1>';

    echo '<p><a href="' . esc_url(admin_url('admin.php?page=ubytovani-zaznamy')) . '" class="button">&larr; ' . esc_html__('Zpět na přehled', 'ubytovani') . '</a></p>';

    // Inline styl – jednoduchý, aby šel hned použít.
    echo '<style>
        .ub-box         { background:#fff;border:1px solid #ccd0d4;border-radius:6px;margin-bottom:25px;}
        .ub-box h2      { margin:0;padding:10px 15px;border-bottom:1px solid #f0f0f1;font-size:16px; }
        .ub-table       { width:100%;border-collapse:collapse; }
        .ub-table tr:nth-child(odd){background:#f9f9f9;}
        .ub-label       { width:30%;padding:8px 15px;font-weight:600;vertical-align:top; }
        .ub-value       { padding:8px 15px; }
        .ub-row-pre     { padding:8px 15px;white-space:pre-wrap; }
    </style>';

    // === Osobní údaje ===
    echo '<div class="ub-box">';
    echo '<h2>' . esc_html__('Osobní údaje', 'ubytovani') . '</h2>';
    echo '<table class="ub-table">';
    foreach (['jmeno','prijmeni','email','telefon','adresa','stat','cislo_op','datum_narozeni'] as $k) {
        ubytovani_vykresli_radek($labels[$k], $zaznam->$k);
    }
    echo '</table></div>';

    // === Fakturační údaje (pokud existují) ===
    if ($zaznam->firma || $zaznam->ico || $zaznam->dic) {
        echo '<div class="ub-box">';
        echo '<h2>' . esc_html__('Fakturační údaje', 'ubytovani') . '</h2>';
        echo '<table class="ub-table">';
        foreach (['firma','ico','dic'] as $k) {
            ubytovani_vykresli_radek($labels[$k], $zaznam->$k);
        }
        echo '</table></div>';
    }

    // === Rezervace ===
    echo '<div class="ub-box">';
    echo '<h2>' . esc_html__('Rezervace', 'ubytovani') . '</h2>';
    echo '<table class="ub-table">';
    foreach (['datum_prijezdu','datum_odjezdu','pocet_noci','pocet_osob','cena','stav'] as $k) {
        $val = $zaznam->$k;
        if ($k === 'cena') {
            $val = number_format(floatval($val), 2, ',', ' ') . ' Kč';
        }
        ubytovani_vykresli_radek($labels[$k], $val);
    }
    echo '</table></div>';

    // === Poznámka ===
    if (!empty($zaznam->poznamka)) {
        echo '<div class="ub-box">';
        echo '<h2>' . esc_html__('Poznámka', 'ubytovani') . '</h2>';
        echo '<div class="ub-row-pre">' . esc_html($zaznam->poznamka) . '</div>';
        echo '</div>';
    }

    echo '</div>'; // wrap
}
?>