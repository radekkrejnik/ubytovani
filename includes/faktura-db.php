<?php
function ubytovani_vystav_cislo_faktury($rezervace_id) {
    global $wpdb;
    $tabulka = $wpdb->prefix . 'ubytovani_faktury';

    // Zkontroluj, jestli už existuje faktura pro tuto rezervaci
    $existujici = $wpdb->get_var($wpdb->prepare("
        SELECT cislo_faktury FROM $tabulka WHERE rezervace_id = %d
    ", $rezervace_id));

    if ($existujici) {
        return $existujici;
    }

    // Vygeneruj nové číslo
    $rok = date('Y');
    $posledni = $wpdb->get_var($wpdb->prepare("
        SELECT MAX(CAST(SUBSTRING_INDEX(cislo_faktury, '_', 1) AS UNSIGNED)) 
        FROM $tabulka WHERE cislo_faktury LIKE %s
    ", '%' . $rok));

    $cislo = intval($posledni) + 1;
    $cislo_faktury = 'UB' . $cislo . '_' . $rok;

    // Ulož do tabulky
    $wpdb->insert($tabulka, [
        'rezervace_id' => $rezervace_id,
        'cislo_faktury' => $cislo_faktury
    ]);

    return $cislo_faktury;
}
