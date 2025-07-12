<?php
class QRPlatba {
    public static function vygeneruj($ucet, $castka, $vs) {
        $data = 'SPD*1.0*ACC:CZ' . str_replace(' ', '', $ucet) . '*AM:' . number_format($castka, 2, '.', '') . '*CC:CZK*X-VS:' . $vs;
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($data);
        return $url;
    }
}
