jQuery(document).ready(function($) {
    $('.ubytovani-kalendar').each(function() {
        const wrapper = $(this);
        const kalendarEl = wrapper.find('.kalendar-mesice');
        const pokojId = wrapper.data('pokoj');

        $.ajax({
            url: ubytovani_ajax.ajaxurl,
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'nacti_rezervace',
                pokoj_id: pokojId
            },
            success: function(response) {
    if (response.success) {
        const domWrapper = wrapper[0];
        if (
    typeof window.vykresliKalendar === 'function' &&
    domWrapper &&
    domWrapper.querySelector('.kalendar-mesice')
) {
    console.log('Rezervace pro pokoj ' + pokojId + ':', response.data);
    vykresliKalendar(domWrapper, response.data);
} else {
    console.warn('vykresliKalendar nebo wrapper nebo .kalendar-mesice chybí');
}

    }
},

            error: function(xhr, status, error) {
                console.error('AJAX chyba:', status, error);
            }
        });
    });
});

jQuery(document).ready(function($) {
    $(".reset-vyber").on("click", function() {
        const pokojId = $(this).data("pokoj");
        const wrapper = $(`#ubytovani-form-wrapper[data-pokoj="${pokojId}"]`);

        if (!wrapper.length) return;

        window.vybranyDatumOd = null;
        window.vybranyDatumDo = null;

        wrapper.find('input[name="datum_prijezdu"]').val('');
        wrapper.find('input[name="datum_odjezdu"]').val('');
        wrapper.find('input[name="pocet_noci"]').val('');
        wrapper.find('select[name="pocet_osob"]').val('');
        wrapper.find('input[name="cena"]').val('');
        wrapper.find('#zobrazena-cena').text('–');




        const kalendarEl = $(`.ubytovani-kalendar[data-pokoj="${pokojId}"]`)[0];
        if (typeof window.vykresliKalendar === "function" && kalendarEl) {
            $.ajax({
                url: ubytovani_ajax.ajaxurl,
                method: 'GET',
                dataType: 'json',
                data: {
                    action: 'nacti_rezervace',
                    pokoj_id: pokojId
                },
                success: function(response) {
                    if (response.success) {
                        window.vykresliKalendar(kalendarEl, response.data);
                    }
                }
            });
        }
    });
});

jQuery(document).ready(function($) {
    $('form#formular-ubytovani').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const data = form.serialize();
        const wrapper = form.closest('[data-pokoj]');
        const pokojId = wrapper.data('pokoj');

        $.ajax({
            url: ubytovani_ajax.ajaxurl,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    alert("Chyba při ukládání rezervace.");
                }
            },
            error: function() {
                alert("Nepodařilo se odeslat rezervaci.");
            }
        });
    });
});

jQuery(document).ready(function($) {
    $('#firma-check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#firma-fields').removeClass('skryte');
            $('#firma-hidden').val('1');
        } else {
            $('#firma-fields').addClass('skryte');
            $('#firma-hidden').val('');
        }
    });
});

// zobrazí o zaškrtnudí pole Název firmy, IČO, DIČ
jQuery(document).ready(function($) {
    $('[id^="ubytovani-form-wrapper"]').each(function() {
        const wrapper = $(this);
        const firmaCheck = wrapper.find('#firma-check');
        const firmaFields = wrapper.find('#firma-fields');
        const firmaHidden = wrapper.find('#firma-hidden');

        firmaCheck.on('change', function() {
            if ($(this).is(':checked')) {
                firmaFields.removeClass('skryte');
                firmaHidden.val('1');
            } else {
                firmaFields.addClass('skryte');
                firmaHidden.val('');
            }
        });
    });
});

// zobrazená pole datum narození
jQuery(document).ready(function($) {
    $('form#formular-ubytovani select[name="stat"]').on('change', function() {
        const stat = $(this).val();
        const wrapper = $(this).closest('[data-pokoj]');
        const blok = wrapper.find('#datum-narozeni-blok');

        if (stat && stat !== 'Česká republika') {
            blok.removeClass('skryte');
        } else {
            blok.addClass('skryte');
            blok.find('input').val('');
        }
    });
});

// výpočet cen v formuláři
jQuery(document).ready(function($) {
    $('[id^="ubytovani-form-wrapper"]').each(function() {
        const wrapper = $(this);
        const osobaSelect = wrapper.find('select[name="pocet_osob"]');
        const nocInput = wrapper.find('input[name="pocet_noci"]');
        const cenaInput = wrapper.find('input[name="cena"]');
        const cenaZobrazena = wrapper.find('#zobrazena-cena');
        const firmaCheck = wrapper.find('#firma-check');
        const cenaData = wrapper.data();
        const poplatek = parseFloat(wrapper.data('poplatek')) || 0;

        function prepocitatCenu() {
            const osoby = parseInt(osobaSelect.val());
            const noci = parseInt(nocInput.val());
            const jeFirma = firmaCheck.is(':checked');

            if (!osoby || !noci) {
                cenaInput.val('');
                cenaZobrazena.text('–');
                return;
            }

            const cenaZaOsoby = parseFloat(cenaData['cena' + osoby]) || 0;
            let celkovaCena = cenaZaOsoby * noci;

            if (!jeFirma) {
                celkovaCena += osoby * noci * poplatek;
            }

            cenaInput.val(celkovaCena);
            cenaZobrazena.text(celkovaCena.toLocaleString('cs-CZ'));
        }

        osobaSelect.on('change', prepocitatCenu);
        nocInput.on('change', prepocitatCenu);
        firmaCheck.on('change', prepocitatCenu);
    });
});
