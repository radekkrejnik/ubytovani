<?php
function ubytovani_vytvorit_tabulku() {
       global $wpdb;

    $tabulka = $wpdb->prefix . 'ubytovani_zaznamy';
    $charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql = "CREATE TABLE {$tabulka} (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        pokoj_id INT DEFAULT 1,
        datum_prijezdu DATE NOT NULL,
        datum_odjezdu DATE NOT NULL,
        pocet_noci INT NOT NULL,
        jmeno VARCHAR(100) NOT NULL,
        prijmeni VARCHAR(100) NOT NULL,
        firma VARCHAR(100) DEFAULT NULL,
        ico VARCHAR(20) DEFAULT NULL,
        dic VARCHAR(20) DEFAULT NULL,
        adresa TEXT NOT NULL,
        stat VARCHAR(50) NOT NULL,
        telefon VARCHAR(30) NOT NULL,
        email VARCHAR(100) NOT NULL,
        cislo_op VARCHAR(50) NOT NULL,
        datum_narozeni DATE NOT NULL,
        cena DECIMAL(10,2) NOT NULL,
        poplatek DECIMAL(10,2) DEFAULT 0,
        poznamka TEXT DEFAULT NULL,
        stav VARCHAR(50) DEFAULT 'Čeká na schválení',
        pocet_osob INT NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    dbDelta($sql);

    // Kontrola existence sloupce pokoj_id (pro jistotu i u starší instalace)
    $sloupce = $wpdb->get_results("SHOW COLUMNS FROM {$tabulka} LIKE 'pokoj_id'");
    if (empty($sloupce)) {
        $wpdb->query("ALTER TABLE {$tabulka} ADD COLUMN pokoj_id INT DEFAULT 1");
    }
    }
