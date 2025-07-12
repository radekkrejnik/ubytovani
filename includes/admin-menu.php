<?php
// includes/admin-menu.php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {

    // Hlavní stránka + podmenu
    add_menu_page(
        __('Rezervace ubytování', 'ubytovani'),
        __('Rezervace ubytování', 'ubytovani'),
        'manage_options',
        'ubytovani-zaznamy',
        'ubytovani_vypis_zaznamu',
        'dashicons-admin-home',
        6
    );

    add_submenu_page(
        'ubytovani-zaznamy',
        __('Obecné nastavení', 'ubytovani'),
        __('Obecné', 'ubytovani'),
        'manage_options',
        'ubytovani-obecne',
        'ubytovani_stranka_obecne_html'
    );

    add_submenu_page(
        'ubytovani-zaznamy',
        __('Pokoje', 'ubytovani'),
        __('Pokoje', 'ubytovani'),
        'manage_options',
        'ubytovani-pokoje',
        'ubytovani_stranka_pokoje_html'
    );

    add_submenu_page(
    'ubytovani-zaznamy',
    __('Blokování termínů', 'ubytovani'),
    __('Blokace', 'ubytovani'),
    'manage_options',
    'ubytovani-blokace',
    'ubytovani_blokace_render_page'
    );

    add_submenu_page(
        'ubytovani-zaznamy',
        __('Ceny', 'ubytovani'),
        __('Ceny', 'ubytovani'),
        'manage_options',
        'ubytovani-ceny',
        'ubytovani_stranka_ceny_html'
    );

    add_submenu_page(
        'ubytovani-zaznamy',
        __('Fakturace', 'ubytovani'),
        __('Fakturace', 'ubytovani'),
        'manage_options',
        'ubytovani-fakturace',
        'ubytovani_stranka_fakturace_html'
    );

    add_submenu_page(
        'ubytovani-zaznamy',
        __('Export PDF', 'ubytovani'),
        __('Export', 'ubytovani'),
        'manage_options',
        'ubytovani-export',
        'ubytovani_export_pdf_formular'
    );

     add_submenu_page(
        'ubytovani-zaznamy',
        __('Licence', 'ubytovani'),
        __('Licence', 'ubytovani'),
        'manage_options',
        'ubytovani-licence',
        'ubytovani_stranka_licence_html'
    );
    // Skryté podstránky (nejsou v menu, ale potřebné pro přímé odkazy)
    add_submenu_page(
        null,
        __('Detail rezervace', 'ubytovani'),
        __('Detail rezervace', 'ubytovani'),
        'manage_options',
        'ubytovani-detail',
        'ubytovani_stranka_detail'
    );

    add_submenu_page(
        null,
        __('Úprava rezervace', 'ubytovani'),
        __('Úprava rezervace', 'ubytovani'),
        'manage_options',
        'ubytovani-edit',
        'ubytovani_stranka_editace'
    );

    add_submenu_page(
        null,
        __('Faktura', 'ubytovani'),
        __('Faktura', 'ubytovani'),
        'manage_options',
        'ubytovani-faktura',
        'ubytovani_formular_faktury'
    );

    add_submenu_page(
        null,
        __('Zálohová platba', 'ubytovani'),
        __('Zálohová platba', 'ubytovani'),
        'manage_options',
        'ubytovani-zaloha',
        'ubytovani_formular_zalohy'
    );
});
