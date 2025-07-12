window.addEventListener("load", function () {
    

    const inputPrijezd = document.querySelector('input[name="datum_prijezdu"]');
    const inputOdjezd = document.querySelector('input[name="datum_odjezdu"]');

    const pocetNoci = document.querySelector('input[name="pocet_noci"]');
    const cena = document.querySelector('input[name="cena"]');
    const pocetOsob = document.getElementById('pocet_osob');
    const firmaCheck = document.getElementById("firma-check");
    const firmaFields = document.getElementById("firma-fields");
    const resetTlacitko = document.getElementById("reset-vyber");
    const hlaseni = document.getElementById("rezervace-hlaseni");
    const hlaseniText = document.getElementById("rezervace-text");
    const hlaseniOk = document.getElementById("rezervace-ok");



    if (!firmaCheck || !firmaFields) {
        console.warn("firmaCheck nebo firmaFields nenalezeny. Přeskakuji firemní logiku.");
        return;
    }

    const wrapper = document.getElementById("ubytovani-form-wrapper");
    const cena1 = parseFloat(wrapper.dataset.cena1);
    const cena2 = parseFloat(wrapper.dataset.cena2);
    const poplatek = parseFloat(wrapper.dataset.poplatek);

    let aktualniMesic = new Date();

    let vyber = {
        od: null,
        do: null
    };

    function formatDatum(d) {
    return d.getFullYear() + '-' +
           String(d.getMonth() + 1).padStart(2, '0') + '-' +
           String(d.getDate()).padStart(2, '0');
}

    function kolidujeSRezervaci(od, do_) {
        const datumOd = new Date(od);
        const datumDo = new Date(do_);

        for (const rez of rezervace) {
            const rezOd = new Date(rez.od);
            const rezDo = new Date(rez.do);

            if (!(datumDo <= rezOd || datumOd >= rezDo)) {
                return true;
            }
        }

        return false;
    }

        if (resetTlacitko) {
        resetTlacitko.addEventListener("click", () => {
        vyber.od = null;
        vyber.do = null;
        inputPrijezd.value = "";
        inputOdjezd.value = "";
        pocetNoci.value = "";
        cena.value = "";
        document.getElementById("zobrazena-cena").textContent = "–";
    
    });
}
    function prepocitat() {
    if (inputPrijezd.value && inputOdjezd.value) {
        const od = new Date(inputPrijezd.value);
        const do_ = new Date(inputOdjezd.value);
        const rozdil = Math.round((do_ - od) / (1000 * 60 * 60 * 24));

        if (rozdil > 0) {
            pocetNoci.value = rozdil;

            const osoby = parseInt(pocetOsob.value);
            const jeFirma = firmaCheck.checked;

            if (!osoby || osoby < 1 || osoby > 10) {
    cena.value = "";
    document.getElementById("zobrazena-cena").textContent = "–";
    return;
}

const cenaKey = 'cena' + osoby;
const cenaZaNoc = parseFloat(wrapper.dataset[cenaKey]);
if (isNaN(cenaZaNoc)) return;
const zaklad = cenaZaNoc * rozdil;
const poplatky = jeFirma ? 0 : osoby * poplatek * rozdil;

const celkovaCena = zaklad + poplatky;
cena.value = celkovaCena;
document.getElementById("zobrazena-cena").textContent = celkovaCena.toLocaleString("cs-CZ") + " Kč";


        } else {
            pocetNoci.value = "";
            cena.value = "";
            document.getElementById("zobrazena-cena").textContent = "–";
        }
    }
}


    pocetOsob.addEventListener("change", prepocitat);


    firmaCheck.addEventListener("change", function () {
        if (this.checked) {
            firmaFields.classList.remove("skryte");
        } else {
            firmaFields.classList.add("skryte");
        }
        prepocitat();
        document.getElementById("firma-hidden").value = this.checked ? "ano" : "";
    });

    if (firmaCheck.checked) {
        firmaFields.classList.remove("skryte");
    } else {
        firmaFields.classList.add("skryte");
    }

    const statSelect = document.getElementById("stat");
    const narozeniBlok = document.getElementById("datum-narozeni-blok");
    const narozeniInput = document.getElementById("datum_narozeni");

    statSelect.addEventListener("change", function () {
        if (statSelect.value === "Česká republika") {
            narozeniBlok.classList.add("skryte");
            narozeniInput.removeAttribute("required");
            narozeniInput.value = "";
        } else {
            narozeniBlok.classList.remove("skryte");
            narozeniInput.setAttribute("required", "required");
        }
    });
});
/// formulář – použito pro výpočty
const formular = document.getElementById("formular-ubytovani");
