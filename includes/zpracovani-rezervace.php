<?php
add_action('wp_ajax_nova_rezervace', 'ubytovani_ulozit_rezervaci');
add_action('wp_ajax_nopriv_nova_rezervace', 'ubytovani_ulozit_rezervaci');

function ubytovani_ulozit_rezervaci() {
    // celý obsah původní funkce zde beze změny,
    // včetně doplnění kroku 3 s redirect URL
}
