const params = new URLSearchParams(window.location.search)
const codice = params.get('codice')

var selezionate = 0;

$('.laboratorio').click(function () {
    if($(this).is('.choosen')) {
        return;
    }
    let check = $(this).find('input[type="checkbox"]');
    if (check.prop('checked')) {
        $(this).removeClass('selected');
    } else {
        $(this).addClass('selected');
    }
    check.prop('checked', !check.prop('checked'));
    selezionate = $('.laboratorio input[type="checkbox" checked]').length;
    console.log(selezionate)
})
$.get("api/scelte?codice=" + codice, (scelte) =>{
    scelte.forEach(scelta => {
        $('.laboratorio[lab="' + scelta.attivita + '"]')
            .click()
            .addClass('choosen')
            .append(`
                <div class="col-12 choosenMessage">
                    Hai gi&agrave; scelto questa attivit&agrave;.
                </div>`
            ); 
    });
});


async function inviaScelte() {
    var scelte = $(".laboratorio > input:checked");
    await Promise.all(scelte.map(async (i, e) => {
        console.log($(e).parent().attr('attivita'))
        var data = new FormData();
        data.append('codice', codice);
        data.append('attivita', $(e).parent().attr('attivita'));

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
        })

    }));
    if(scelte.length > 0) window.location.href = "?done"
}

function inviaCodice() {
    window.location.href = "?codice=" + $("#codice").val();
}