jQuery(document).ready(function($) {
    window.vybranyDatumOd = null;
window.vybranyDatumDo = null;

    window.vykresliKalendar = function(kalendarEl, rezervace) {
    if (!kalendarEl || !kalendarEl.querySelector(".kalendar-mesice")) {
        console.warn("Chybí prvky kalendáře, funkce vykresliKalendar nebude spuštěna.");
        return;
    }

    const pokojId = kalendarEl.dataset.pokoj;
    const kalendarMesice = kalendarEl.querySelector(".kalendar-mesice");
    const prevButton = kalendarEl.querySelector(".kalendar-prev");
    const nextButton = kalendarEl.querySelector(".kalendar-next");

    let aktualniMesic = new Date();

    function formatDatum(d) {
        return d.getFullYear() + '-' +
            String(d.getMonth() + 1).padStart(2, '0') + '-' +
            String(d.getDate()).padStart(2, '0');
    }

    function vytvorMesic(datum) {
        const mesic = datum.getMonth();
        const rok = datum.getFullYear();
        const dnyVmesici = new Date(rok, mesic + 1, 0).getDate();
        const prvniDen = new Date(rok, mesic, 1).getDay();
        const jmenoMesice = datum.toLocaleString("cs-CZ", { month: "long", year: "numeric" });

        const mesicDiv = document.createElement("div");
        mesicDiv.classList.add("kalendar-mesic");

        const nadpis = document.createElement("div");
        nadpis.classList.add("mesic-nadpis");
        nadpis.textContent = jmenoMesice;

        const tabulka = document.createElement("table");
        tabulka.innerHTML = `
            <thead>
                <tr><th>Po</th><th>Út</th><th>St</th><th>Čt</th><th>Pá</th><th>So</th><th>Ne</th></tr>
            </thead>
        `;

        const tbody = document.createElement("tbody");
        let radek = document.createElement("tr");
        let denTydne = (prvniDen + 6) % 7;
        for (let i = 0; i < denTydne; i++) {
            radek.appendChild(document.createElement("td"));
        }

        for (let d = 1; d <= dnyVmesici; d++) {
            if (radek.children.length === 7) {
                tbody.appendChild(radek);
                radek = document.createElement("tr");
            }

            const td = document.createElement("td");
            td.textContent = d;
            const datumBtn = new Date(rok, mesic, d);
            const datumStr = formatDatum(datumBtn);
            td.dataset.datum = datumStr;

            td.classList.add("kalendar-den");


            rezervace.forEach(rez => {
    if (parseInt(rez.pokoj_id) !== parseInt(pokojId)) return;
        if (rez.email === 'blokace@interni') {
        if (datumStr >= rez.od && datumStr <= rez.do) {
            td.classList.add('rez-cela-blokace');
        }
        return;
    }

    let stav = rez.stav.toLowerCase().trim();
    if (stav === 'schváleno') stav = 'schvaleno';
    if (stav === 'čeká_na_schválení') stav = 'ceka';
    if (stav === 'blokováno') stav = 'blokovano';



    const zacatek = rez.od;
    const konec = rez.do;

    if (datumStr === zacatek && datumStr === konec) {
        td.classList.add(`rez-leva-${stav}`, `rez-prava-${stav}`);
    } else if (datumStr === zacatek) {
        td.classList.add(`rez-prava-${stav}`);
    } else if (datumStr === konec) {
        td.classList.add(`rez-leva-${stav}`);
    } else if (datumStr > zacatek && datumStr < konec) {
        td.classList.add(`rez-cela-${stav}`);
    }
});


// vykreslování zelených polí
            if (window.vybranyDatumOd && window.vybranyDatumDo) {
    const od = window.vybranyDatumOd;
    const doo = window.vybranyDatumDo;

    if (datumStr === od && datumStr === doo) {
        td.classList.add("rez-leva-vyber", "rez-prava-vyber");
    } else if (datumStr === od) {
        td.classList.add("rez-prava-vyber");
    } else if (datumStr === doo) {
        td.classList.add("rez-leva-vyber");
    } else if (datumStr > od && datumStr < doo) {
        td.classList.add("rez-cela-vyber");
    }
}
// vykreslená prvního pole výběru zeleně
else if (window.vybranyDatumOd && !window.vybranyDatumDo) {
    if (datumStr === window.vybranyDatumOd) {
        td.classList.add("rez-prava-vyber");
    }
}


            const dnes = new Date();
            dnes.setHours(0, 0, 0, 0);
            const zitra = new Date(dnes);
            zitra.setDate(dnes.getDate() + 1);

            if (datumBtn < zitra) {
                td.classList.add("skryte-datum");
                td.style.pointerEvents = "none";
                td.style.opacity = "0.3";
            }

           td.addEventListener("click", function () {
    // Zákaz výběru dnů uvnitř existujících rezervací
    if (
    td.classList.contains('rez-cela-schvaleno') ||
    td.classList.contains('rez-cela-ceka') ||
    td.classList.contains('rez-cela-blokovano')
) {
    return;
}


    if (!window.vybranyDatumOd || (window.vybranyDatumOd && window.vybranyDatumDo)) {
        window.vybranyDatumOd = datumStr;
        window.vybranyDatumDo = null;

        // funkce detekce bez kolize termínů

  } else if (datumStr >= window.vybranyDatumOd) {

    if (datumStr === window.vybranyDatumOd) {
    alert("Nelze zvolit stejný den pro příjezd i odjezd.");
    return;
}

    const od = new Date(window.vybranyDatumOd);
    const doo = new Date(datumStr);
    const rozdil = Math.round((doo - od) / (1000 * 60 * 60 * 24));

    let koliduje = false;

    for (let i = 0; i <= rozdil; i++) {
    const d = new Date(od);
    d.setDate(d.getDate() + i);
    const denStr = formatDatum(d);
    const tdDen = kalendarEl.querySelector(`td[data-datum="${denStr}"]`);

    if (tdDen && (
        tdDen.classList.contains('rez-cela-schvaleno') ||
        tdDen.classList.contains('rez-cela-ceka') ||
        tdDen.classList.contains('rez-cela-blokovano') ||
        (i !== 0 && tdDen.classList.contains('rez-leva-schvaleno')) ||
        (i !== rozdil && tdDen.classList.contains('rez-prava-schvaleno')) ||
        (i !== 0 && tdDen.classList.contains('rez-leva-ceka')) ||
        (i !== rozdil && tdDen.classList.contains('rez-prava-ceka')) ||
        (i !== 0 && tdDen.classList.contains('rez-leva-blokovano')) ||
        (i !== rozdil && tdDen.classList.contains('rez-prava-blokovano'))
    )) {
        koliduje = true;
        break;
    }
}


    // Zákaz jednodenní kolize: dvě půlky vedle sebe
    if (rozdil === 1) {
        const den1 = kalendarEl.querySelector(`td[data-datum="${window.vybranyDatumOd}"]`);
        const den2 = kalendarEl.querySelector(`td[data-datum="${datumStr}"]`);
        if (
            den1 && den2 && (
                (den1.classList.contains('rez-prava-schvaleno') && den2.classList.contains('rez-leva-schvaleno')) ||
                (den1.classList.contains('rez-prava-ceka') && den2.classList.contains('rez-leva-ceka'))
            )
        ) {
            koliduje = true;
        }
    }

    if (koliduje) {
        alert("Vybraný rozsah prochází jinou rezervací.");
        return;
    }

    window.vybranyDatumDo = datumStr;
}




// zde je konec funkce kontroly kolize datumů

    vykresli(); // překreslí kalendář

    // ✅ přenos do formuláře se stejným pokoj_id
    const wrapper = document.querySelector(`#ubytovani-form-wrapper[data-pokoj="${pokojId}"]`);
    if (wrapper) {
        const inputPrijezd = wrapper.querySelector('input[name="datum_prijezdu"]');
const inputOdjezd = wrapper.querySelector('input[name="datum_odjezdu"]');
const inputNoci = wrapper.querySelector('input[name="pocet_noci"]');

if (inputPrijezd && inputOdjezd) {
    inputPrijezd.value = window.vybranyDatumOd || '';
    inputOdjezd.value = window.vybranyDatumDo || '';

    if (window.vybranyDatumOd && window.vybranyDatumDo && inputNoci) {
        const d1 = new Date(window.vybranyDatumOd);
        const d2 = new Date(window.vybranyDatumDo);
        const rozdil = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
        inputNoci.value = rozdil > 0 ? rozdil : '';
    }
}

    }
});

            radek.appendChild(td);
        }

        if (radek.children.length > 0) {
            while (radek.children.length < 7) {
                radek.appendChild(document.createElement("td"));
            }
            tbody.appendChild(radek);
        }

        tabulka.appendChild(tbody);
        mesicDiv.appendChild(nadpis);
        mesicDiv.appendChild(tabulka);
        return mesicDiv;
    }

    function vykresli() {
        kalendarMesice.innerHTML = "";
        for (let i = 0; i < 3; i++) {
            const mesic = new Date(aktualniMesic.getFullYear(), aktualniMesic.getMonth() + i, 1);
            const mesicElement = vytvorMesic(mesic);
            kalendarMesice.appendChild(mesicElement);
        }
    }

    prevButton?.addEventListener("click", () => {
        aktualniMesic.setMonth(aktualniMesic.getMonth() - 1);
        vykresli();
    });

    nextButton?.addEventListener("click", () => {
        aktualniMesic.setMonth(aktualniMesic.getMonth() + 1);
        vykresli();
    });

       vykresli();
};
});