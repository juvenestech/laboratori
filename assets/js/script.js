const params = new URLSearchParams(window.location.search)
const codice = params.get('codice')

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
})
$.get("api/scelte?codice=" + codice, (scelte) =>{
    scelte.forEach(scelta => {
        $('.laboratorio[lab="' + scelta.id_laboratorio + '"]')
            .click()
            .addClass('choosen')
            .append(`
                <div class="col-12 choosenMessage">
                    Hai gi&agrave; scelto questo laboratorio.
                </div>`
            ); 
    });
});


async function inviaScelte() {
    var scelte = $(".laboratorio > input:checked");
    await Promise.all(scelte.map(async (i, e) => {
        console.log($(e).parent().attr('lab'))
        var data = new FormData();
        data.append('codice', codice);
        data.append('id_laboratorio', $(e).parent().attr('lab'));

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