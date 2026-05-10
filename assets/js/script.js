const params = new URLSearchParams(window.location.search)
const codice = params.get('codice')

var ordineScelte = []; // Traccia l'ordine di preferenza (§5)
var modalErrore = new bootstrap.Modal(document.getElementById('modalErrore'), {})

function getMaxScelte() {
    // Legge il max_scelte dalla prima card (tutte le card della stessa categoria hanno lo stesso valore)
    const first = $('.laboratorio').first();
    return parseInt(first.attr('data-max-scelte')) || 5;
}

function updateCounter() {
    const count = ordineScelte.length;
    const max = getMaxScelte();
    $('#counterText').html(`Hai selezionato <strong>${count}</strong> su <strong>${max}</strong> laboratori`);
    if (count >= max) {
        $('#selectionCounter').addClass('complete');
    } else {
        $('#selectionCounter').removeClass('complete');
    }
}

function updateBadges() {
    // Rimuove tutti i badge
    $('.laboratorio .lab-badge').text('');
    // Assegna i numeri nell'ordine di selezione
    ordineScelte.forEach((labId, index) => {
        $(`.laboratorio[data-lab-id="${labId}"] .lab-badge`).text(index + 1);
    });
}

$('.laboratorio').click(function () {
    if($(this).is('.choosen') || $(this).is('.completo')) {
        return;
    }
    const labId = $(this).attr('data-lab-id');
    let check = $(this).find('input[type="checkbox"]');
    const SELEZIONABILI = getMaxScelte();

    if (check.prop('checked')) {
        // Deseleziona
        $(this).removeClass('selected');
        check.prop('checked', false);
        ordineScelte = ordineScelte.filter(id => id !== labId);
    } else if (ordineScelte.length < SELEZIONABILI) {
        // Seleziona
        $(this).addClass('selected');
        check.prop('checked', true);
        ordineScelte.push(labId);
    } else {
        $('#testoErrore').html(`Puoi scegliere al massimo ${SELEZIONABILI} attivit&agrave;!`);
        modalErrore.show();
        return;
    }

    updateBadges();
    updateCounter();
})

// Carica scelte già fatte
$.get("api/scelte?codice=" + encodeURIComponent(codice), (scelte) =>{
    scelte.forEach(scelta => {
        const card = $(`.laboratorio[data-lab-id="${scelta.id_laboratorio}"]`);
        card.find('input[type="checkbox"]').prop('checked', true);
        card.addClass('selected choosen')
            .append(`<div class="col-12 choosenMessage">Hai gi&agrave; scelto questa attivit&agrave;.</div>`);
        ordineScelte.push(String(scelta.id_laboratorio));
    });
    updateBadges();
    updateCounter();
}).then(() => {
    // Controlla posti disponibili e mostra "ESAURITO" (§5)
    $.get("api/laboratori?codice=" + encodeURIComponent(codice), (labotatori) =>{
        labotatori.forEach(lab => {
            if(lab.posti - lab.prenotazioni <= 0) {
                const card = $(`.laboratorio[data-lab-id="${lab.id}"]`);
                if (!card.is('.choosen')) {
                    card.addClass('completo')
                        .append(`<div class="esaurito-label">ESAURITO</div>`);
                }
            }
        });
    });
});


async function inviaScelte() {
    const SELEZIONABILI = getMaxScelte();
    if (ordineScelte.length < SELEZIONABILI) {
        $('#testoErrore').html(`Devi scegliere esattamente ${SELEZIONABILI} attivit&agrave;!`);
        modalErrore.show();
        return;
    }

    // Invia le scelte nell'ordine di preferenza
    const chosenCards = ordineScelte.filter(id => {
        return !$(`.laboratorio[data-lab-id="${id}"]`).is('.choosen');
    });

    await Promise.all(chosenCards.map(async (labId, index) => {
        var data = new FormData();
        data.append('codice', codice);
        data.append('laboratorio', labId);
        data.append('ordine', index + 1);

        return $.ajax({
            "url": "api/scelte",
            "method": "POST",
            "timeout": 0,
            "processData": false,
            "mimeType": "multipart/form-data",
            "contentType": false,
            "data": data
        }).done(function (response) {
            console.log(response);
        }).catch(function (error) {
            console.log(error);
        });
    }));
    if(chosenCards.length > 0) window.location.href = "?done=" + encodeURIComponent(codice);
}

function inviaCodice() {
    window.location.href = "?codice=" + encodeURIComponent($("#codice").val());
}