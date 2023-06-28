const params = new URLSearchParams(window.location.search)
const codice = params.get('codice')

var selezionate = 0;
const SELEZIONABILI = 4;

var modalErrore = new bootstrap.Modal(document.getElementById('modalErrore'), {})

$('.laboratorio').click(function () {
    if($(this).is('.choosen') || $(this).is('.completo')) {
        return;
    }
    let check = $(this).find('input[type="checkbox"]');
    if (check.prop('checked')) {
        $(this).removeClass('selected');
    } else if (selezionate < SELEZIONABILI) {
        $(this).addClass('selected');
    } else {
        $('#testoErrore').html(`Puoi scegliere al massimo ${SELEZIONABILI} attivit&agrave;!`);
        modalErrore.show();
        return;
    }
    check.prop('checked', !check.prop('checked'));
    selezionate = $('.laboratorio input:checkbox:checked').length
})
$.get("api/scelte?codice=" + codice, (scelte) =>{
    scelte.forEach(scelta => {
        console.log(scelta);
        $('.laboratorio[lab="' + scelta.id_laboratorio + '"]')
            .click()
            .addClass('choosen')
            .append(`
                <div class="col-12 choosenMessage">
                    Hai gi&agrave; scelto questa attivit&agrave;.
                </div>`
            ); 
    });
}).then(() => {
    $.get("api/laboratori?codice=" + codice, (labotatori) =>{
        labotatori.forEach(lab => {
            if(lab.posti - lab.prenotazioni <= 0)
                $('.laboratorio[lab="' + lab.id + '"]')
                    .addClass('completo')
                    .append(`
                        <div class="col-12 choosenMessage">
                            Attivit&agrave; al completo per questa settimana.
                        </div>`
                    ); 
        });
    });
});


async function inviaScelte() {
    var scelte = $(".laboratorio > input:checked");
    if (scelte.length < SELEZIONABILI) {
        $('#testoErrore').html(`Devi scegliere essattamente ${SELEZIONABILI} attivit&agrave;!`);
        modalErrore.show();
        return;
    }  
    await Promise.all(scelte.map(async (i, e) => {
        console.log($(e).parent().attr('lab'))
        var data = new FormData();
        data.append('codice', codice);
        data.append('laboratorio', $(e).parent().attr('lab'));

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
    if(scelte.length > 0) window.location.href = "?done=" + codice;
}

function inviaCodice() {
    window.location.href = "?codice=" + $("#codice").val();
}