<?php
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

function ubytovani_vygeneruj_qr_kod($cena, $cislo_faktury) {
    $cislo_uctu = get_option('ubytovani_firma_ucet');
    if (!$cislo_uctu || !$cena || !$cislo_faktury) {
        return '';
    }

    preg_match_all('/\d+/', $cislo_faktury, $matches);
    $vs = implode('', $matches[0]);

    if (!ctype_digit($vs)) {
        $vs = '';
    }

    $zprava = __('Platba za ubytování', 'ubytovani');
    $qr_data = "SPD*1.0*ACC:$cislo_uctu*AM:$cena*CC:CZK";

    if ($vs !== '') {
        $qr_data .= "*X-VS:$vs";
    }

    $qr_data .= "*MSG:$zprava";

    try {
        $qrCode = new QrCode($qr_data);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        return $result->getDataUri();
    } catch (Exception $e) {
        return '';
    }
}

function ubytovani_vytvor_fakturu_html($rezervace, $cislo_faktury, $zprava = null, $castka = null, $qr = '', $typ = 'faktura') {
    $is_zalohova = strpos($cislo_faktury, 'ZAL') === 0;

    if ($is_zalohova && $castka !== null) {
        $cena_celkem = $castka;
    }

    $nocí = (int) $rezervace->pocet_noci;
    $osob = (int) $rezervace->pocet_osob;
    $je_firma = !empty($rezervace->firma);

    $cena_1_osoba = (float) get_option('ubytovani_cena_1_osoba', 800);
    $cena_2_osoby = (float) get_option('ubytovani_cena_2_osoby', 1200);
    $poplatek_osoba = (float) get_option('ubytovani_poplatek_osoba', 30);

    if ($osob === 1) {
        $cena_ubytovani = $nocí * $cena_1_osoba;
    } else {
        $cena_ubytovani = $nocí * $cena_2_osoby;
    }

    $cena_poplatek = $je_firma ? 0 : ($nocí * $osob * $poplatek_osoba);
    $cena_celkem = $rezervace->cena;

    if ($is_zalohova && $castka !== null) {
        $cena_celkem = $castka;
    }

    if ($zprava === null) {
        $zprava = __('Platba za ubytování', 'ubytovani');
    }

    header('Content-Type: text/html; charset=utf-8');

    $firma = [
        'nazev'   => get_option('ubytovani_firma_nazev'),
        'adresa'  => get_option('ubytovani_firma_adresa'),
        'ico'     => get_option('ubytovani_firma_ico'),
        'dic'     => get_option('ubytovani_firma_dic'),
        'ucet'    => get_option('ubytovani_firma_ucet'),
        'dph'     => get_option('ubytovani_firma_dph'),
    ];

    $datum_od = date('d.m.Y', strtotime($rezervace->datum_prijezdu));
    $datum_do = date('d.m.Y', strtotime($rezervace->datum_odjezdu));
    $qr_img = ubytovani_vygeneruj_qr_kod($cena_celkem, $cislo_faktury);
    preg_match_all('/\d+/', $cislo_faktury, $matches);
    $variabilni_symbol = implode('', $matches[0]);

    ob_start();
    ?>
    <div style="font-family: DejaVu Sans, Arial, sans-serif; max-width: 700px; margin: 0 auto; padding: 20px; border:1px solid #ccc;">
        <h2 style="text-align:left; font-size: 18px; margin-top: 0;">
            <?php echo $is_zalohova ? esc_html__('Zálohová faktura č.', 'ubytovani') : esc_html__('Faktura č.', 'ubytovani'); ?>
            <?php echo esc_html($cislo_faktury); ?>
        </h2>

        <table style="width:100%; margin-top:30px; margin-bottom:20px;">
            <tr>
                <td style="width:50%; vertical-align: top;">
                    <strong><?php _e('Dodavatel:', 'ubytovani'); ?></strong><br>
                    <?php echo esc_html($firma['nazev']); ?><br>
                    <?php echo esc_html($firma['adresa']); ?><br>
                    <?php _e('IČO:', 'ubytovani'); ?> <?php echo esc_html($firma['ico']); ?><br>
                    <?php
                    if ($firma['dph'] === '1' && $firma['dic']) {
                        echo __('DIČ:', 'ubytovani') . ' ' . esc_html($firma['dic']);
                    } else {
                        echo esc_html__('Nejsme plátci DPH', 'ubytovani');
                    }
                    ?><br>
                    <?php
                    $ucet_cz = get_option('ubytovani_firma_ucet_cz');
                    if ($ucet_cz) {
                        echo esc_html__('Číslo účtu:', 'ubytovani') . ' ' . esc_html($ucet_cz);
                    } else {
                        echo esc_html__('Číslo účtu (IBAN):', 'ubytovani') . ' ' . esc_html($firma['ucet']);
                    }
                    ?>
                    <br>
                    <?php _e('Variabilní symbol:', 'ubytovani'); ?> <?php echo esc_html($variabilni_symbol); ?>
                </td>
                <td style="width:50%; vertical-align: top;">
                    <strong><?php _e('Odběratel:', 'ubytovani'); ?></strong><br>
                    <?php echo esc_html($rezervace->jmeno . ' ' . $rezervace->prijmeni); ?><br>
                    <?php if (!empty($rezervace->firma)) echo esc_html($rezervace->firma) . '<br>'; ?>
                    <?php echo esc_html($rezervace->adresa); ?><br>
                    <?php
                        $psc = isset($rezervace->psc) ? $rezervace->psc : '';
                        $mesto = isset($rezervace->mesto) ? $rezervace->mesto : '';
                        echo esc_html(trim($psc . ' ' . $mesto));
                    ?><br>
                    <?php echo esc_html($rezervace->stat); ?><br>
                    <?php _e('E-mail:', 'ubytovani'); ?> <?php echo esc_html($rezervace->email); ?><br>
                    <?php _e('Telefon:', 'ubytovani'); ?> <?php echo esc_html($rezervace->telefon); ?>
                </td>
            </tr>
        </table>

        <?php if (!$is_zalohova): ?>
        <h2 style="border-bottom:1px solid #000; padding-bottom:5px;"><?php _e('Detail rezervace', 'ubytovani'); ?></h2>
        <table style="width:100%; border-collapse:collapse; margin-top:10px;">
            <tr>
                <td style="padding:8px; border:1px solid #ccc;"><?php _e('Termín pobytu', 'ubytovani'); ?></td>
                <td style="padding:8px; border:1px solid #ccc;"><?php echo "$datum_od – $datum_do"; ?></td>
            </tr>
            <tr>
                <td style="padding:8px; border:1px solid #ccc;"><?php _e('Počet osob', 'ubytovani'); ?></td>
                <td style="padding:8px; border:1px solid #ccc;"><?php echo esc_html($rezervace->pocet_osob); ?></td>
            </tr>
            <tr>
                <td style="padding:8px; border:1px solid #ccc;"><?php _e('Počet nocí', 'ubytovani'); ?></td>
                <td style="padding:8px; border:1px solid #ccc;"><?php echo esc_html($rezervace->pocet_noci); ?></td>
            </tr>
            <tr>
                <td style="padding:8px; border:1px solid #ccc;"><?php _e('Cena za ubytování', 'ubytovani'); ?></td>
                <td style="padding:8px; border:1px solid #ccc;"><?php echo number_format($cena_ubytovani, 2, ',', ' ') . ' Kč'; ?></td>
            </tr>
            <tr>
                <td style="padding:8px; border:1px solid #ccc;"><?php _e('Turistický poplatek', 'ubytovani'); ?><?php if ($je_firma) echo ' (' . esc_html__('neúčtován firmám', 'ubytovani') . ')'; ?></td>
                <td style="padding:8px; border:1px solid #ccc;"><?php echo number_format($cena_poplatek, 2, ',', ' ') . ' Kč'; ?></td>
            </tr>
            <tr>
                <td style="padding:8px; border:1px solid #ccc;"><strong><?php _e('Celková cena', 'ubytovani'); ?></strong></td>
                <td style="padding:8px; border:1px solid #ccc;"><strong><?php echo number_format($cena_celkem, 2, ',', ' ') . ' Kč'; ?></strong></td>
            </tr>
        </table>
        <?php else: ?>
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <tr>
                <td style="padding:10px; border:1px solid #ccc;"><?php _e('Záloha k úhradě', 'ubytovani'); ?></td>
                <td style="padding:10px; border:1px solid #ccc;">
                    <strong><?php echo number_format($cena_celkem, 2, ',', ' ') . ' Kč'; ?></strong>
                </td>
            </tr>
        </table>
        <?php endif; ?>

        <h2 style="margin-top:30px;"><?php _e('QR platba', 'ubytovani'); ?></h2>
        <table style="width:100%; margin-top:10px;">
            <tr>
                <td style="width:130px; text-align:left;">
                    <?php if ($qr_img): ?>
                        <img src="<?php echo esc_attr($qr_img); ?>" alt="<?php esc_attr_e('QR kód pro platbu', 'ubytovani'); ?>" width="120" height="120">
                    <?php else: ?>
                        <p style="color: red;"><?php _e('QR kód se nepodařilo vygenerovat.', 'ubytovani'); ?></p>
                    <?php endif; ?>
                </td>
                <td style="vertical-align:top; padding-left:10px;">
                    <p><?php _e('Zpráva pro příjemce:', 'ubytovani'); ?></p>
                    <p><strong><?php echo esc_html($zprava); ?></strong></p>
                </td>
            </tr>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
