<?php
// hlavní soubor pro nastavení administrace
if (!defined('ABSPATH')) exit;

// Načtení jednotlivých částí nastavení (každá obsahuje svoji funkci a formulář)
require_once __DIR__ . '/admin/admin-nastaveni-obecne.php';
require_once __DIR__ . '/admin/admin-nastaveni-licence.php';
require_once __DIR__ . '/admin/admin-nastaveni-pokoje.php';
require_once __DIR__ . '/admin/admin-nastaveni-ceny.php';
require_once __DIR__ . '/admin/admin-nastaveni-fakturace.php';


