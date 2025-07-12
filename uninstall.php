<?php
/**
 * Uninstall script for plugin "Ubytování"
 */

defined('WP_UNINSTALL_PLUGIN') or die('Nepovolený přístup.');

// Volitelné: Odstranit všechna data z databáze
global $wpdb;
$tabulka_rezervace = $wpdb->prefix . 'ubytovani_zaznamy';
$tabulka_faktury   = $wpdb->prefix . 'ubytovani_faktury';

// ❗ POZOR: Toto trvale smaže všechna data pluginu – zapněte pouze, pokud si to uživatel výslovně přeje
$odstranit_data = get_option('ubytovani_smazat_data_pri_odinstalaci', false);

if ($odstranit_data) {
    $wpdb->query("DROP TABLE IF EXISTS $tabulka_rezervace");
    $wpdb->query("DROP TABLE IF EXISTS $tabulka_faktury");

    // Odstranit i nastavení
    delete_option('ubytovani_email_jmeno');
    delete_option('ubytovani_email_adresa');
    delete_option('ubytovani_cena_osoba_1');
    delete_option('ubytovani_cena_osoba_2');
    delete_option('ubytovani_cena_osoba_3');
    delete_option('ubytovani_cena_osoba_4');
    delete_option('ubytovani_cena_osoba_5');
    delete_option('ubytovani_cena_osoba_6');
    delete_option('ubytovani_cena_osoba_7');
    delete_option('ubytovani_cena_osoba_8');
    delete_option('ubytovani_cena_osoba_9');
    delete_option('ubytovani_cena_osoba_10');
    delete_option('ubytovani_turisticky_poplatek');
    delete_option('ubytovani_ic');
    delete_option('ubytovani_dic');
    delete_option('ubytovani_adresa');
    delete_option('ubytovani_bankovni_ucet');
    delete_option('ubytovani_platce_dph');
    delete_option('ubytovani_prefix_faktury');
    delete_option('ubytovani_posledni_cislo');
}
delete_option('ubytovani_smazat_data_pri_odinstalaci');
