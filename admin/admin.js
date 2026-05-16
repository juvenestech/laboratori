// === Admin Panel JS ===
const API = '../api';

// Navigation
$('.sidebar-nav .nav-item').click(function() {
    $('.sidebar-nav .nav-item').removeClass('active');
    $(this).addClass('active');
    $('.admin-section').removeClass('active');
    $('#sec-' + $(this).data('section')).addClass('active');
    loadSection($(this).data('section'));
});

$('#btnLogout').click(() => {
    // Invalida la sessione PHP lato server, poi redirect
    $.post('../logout')
        .always(() => { window.location.href = '../'; });
});

$('#exportSettimana').on('input', function() {
    $('#btnExport').attr('href', `${API}/export?settimana=${$(this).val()}`);
});

// === LOAD FUNCTIONS ===
function loadSection(name) {
    switch(name) {
        case 'dashboard': loadDashboard(); break;
        case 'edizioni': loadEdizioni(); break;
        case 'categorie': loadCategorie(); break;
        case 'laboratori': loadLaboratori(); break;
        case 'settimane': loadSettimane(); break;
        case 'codici': loadCodici(); break;
    }
}

function loadDashboard() {
    $.get(`${API}/edizioni?active=1`, (data) => {
        if (data && data.length > 0) {
            $('#statEdizione').text(data[0].nome);
        } else {
            $('#statEdizione').text('Nessuna');
        }
    });
    $.get(`${API}/laboratori`, (data) => {
        if (data) {
            $('#statLaboratori').text(data.length);
            renderFillBars(data);
        }
    });
    $.get(`${API}/codici`, (data) => {
        if (data) $('#statCodici').text(data.length);
    });
    $.get(`${API}/scelte`, (data) => {
        if (data) $('#statScelte').text(data.length);
    });
}

function renderFillBars(labs) {
    let html = '';
    labs.forEach(lab => {
        const pct = lab.posti > 0 ? Math.round((lab.prenotazioni / lab.posti) * 100) : 0;
        let cls = '';
        if (pct >= 100) cls = 'full';
        else if (pct >= 75) cls = 'high';
        html += `<div class="fill-bar-row">
            <div class="fill-bar-label" title="${lab.nome}">${lab.nome}</div>
            <div class="fill-bar-track"><div class="fill-bar-fill ${cls}" style="width:${Math.min(pct,100)}%"></div></div>
            <div class="fill-bar-count">${lab.prenotazioni || 0}/${lab.posti}</div>
        </div>`;
    });
    $('#fillBars').html(html || '<p class="text-muted">Nessun laboratorio.</p>');
}

// === EDIZIONI ===
function loadEdizioni() {
    $.get(`${API}/edizioni`, (data) => {
        let html = '';
        (data || []).forEach(e => {
            const badge = e.is_active ? '<span class="badge-active">Attiva</span>' : '<span class="badge-inactive">Inattiva</span>';
            html += `<tr>
                <td>${e.id}</td><td>${e.anno}</td><td>${e.nome}</td><td>${badge}</td>
                <td>
                    ${!e.is_active ? `<button class="btn btn-sm btn-outline-success" onclick="setActiveEdizione(${e.id})">Attiva</button>` : ''}
                    <button class="btn btn-sm btn-outline-primary" onclick="editEdizione(${e.id},'${e.anno}','${e.nome.replace(/'/g,"\\'")}')">Modifica</button>
                </td>
            </tr>`;
        });
        $('#tblEdizioni tbody').html(html);
    });
}

window.setActiveEdizione = function(id) {
    $.post(`${API}/edizioni`, { action: 'set_active', id: id }, () => loadEdizioni());
};

window.editEdizione = function(id, anno, nome) {
    $('#edId').val(id); $('#edAnno').val(anno); $('#edNome').val(nome);
    new bootstrap.Modal(document.getElementById('modalEdizione')).show();
};

$('#btnSaveEdizione').click(() => {
    const data = { anno: $('#edAnno').val(), nome: $('#edNome').val() };
    if ($('#edId').val()) data.id = $('#edId').val();
    $.post(`${API}/edizioni`, data, () => {
        bootstrap.Modal.getInstance(document.getElementById('modalEdizione')).hide();
        $('#edId').val(''); $('#edAnno').val(''); $('#edNome').val('');
        loadEdizioni();
    });
});

// === CATEGORIE ===
function loadCategorie() {
    $.get(`${API}/categorie`, (data) => {
        let html = '';
        (data || []).forEach(c => {
            html += `<tr>
                <td>${c.id}</td><td>${c.nome}</td><td>${c.max_scelte}</td><td>${c.descrizione || ''}</td>
                <td><button class="btn btn-sm btn-outline-primary" onclick="editCategoria(${c.id},'${c.nome.replace(/'/g,"\\'")}',${c.max_scelte},'${(c.descrizione||'').replace(/'/g,"\\'")}')">Modifica</button></td>
            </tr>`;
        });
        $('#tblCategorie tbody').html(html);
    });
}

window.editCategoria = function(id, nome, max, desc) {
    $('#catId').val(id); $('#catNome').val(nome); $('#catMax').val(max); $('#catDesc').val(desc);
    new bootstrap.Modal(document.getElementById('modalCategoria')).show();
};

$('#btnSaveCategoria').click(() => {
    const data = { nome: $('#catNome').val(), max_scelte: $('#catMax').val(), descrizione: $('#catDesc').val() };
    const doSave = () => {
        $.post(`${API}/categorie`, data, () => {
            bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
            $('#catId').val(''); $('#catNome').val(''); $('#catMax').val(5); $('#catDesc').val('');
            loadCategorie();
        }).fail((xhr) => {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nel salvataggio';
            alert(msg);
        });
    };
    if ($('#catId').val()) {
        // Update: niente id_edizione necessario
        data.id = $('#catId').val();
        doSave();
    } else {
        // Create: recupera l'edizione attiva dinamicamente
        $.get(`${API}/edizioni?active=1`, (eds) => {
            if (!eds || !eds.length) {
                alert('Nessuna edizione attiva trovata. Crea o attiva un\'edizione prima di aggiungere categorie.');
                return;
            }
            data.id_edizione = eds[0].id;
            doSave();
        }).fail(() => {
            alert('Impossibile recuperare l\'edizione attiva.');
        });
    }
});

// === LABORATORI ===
function loadLaboratori() {
    // Load categories for dropdown
    $.get(`${API}/categorie`, (cats) => {
        let opts = '<option value="">-- Nessuna --</option>';
        (cats || []).forEach(c => { opts += `<option value="${c.id}">${c.nome}</option>`; });
        $('#labCategoria').html(opts);
    });
    $.get(`${API}/laboratori`, (data) => {
        let html = '';
        (data || []).forEach(l => {
            html += `<tr>
                <td>${l.id}</td><td>${l.nome}</td><td>${l.categoria_nome || '—'}</td>
                <td>${l.posti}</td><td>${l.prenotazioni || 0}</td>
                <td><button class="btn btn-sm btn-outline-primary" onclick="editLab(${l.id})">Modifica</button></td>
            </tr>`;
        });
        $('#tblLaboratori tbody').html(html);
    });
}

window.editLab = function(id) {
    $.get(`${API}/laboratori?id=${id}`, (data) => {
        if (!data || !data[0]) return;
        const l = data[0];
        $('#labId').val(l.id); $('#labNome').val(l.nome); $('#labDesc').val(l.descrizione);
        $('#labGif').val(l.gif); $('#labPosti').val(l.posti); $('#labCategoria').val(l.id_categoria);
        new bootstrap.Modal(document.getElementById('modalLaboratorio')).show();
    });
};

// Preview file immagine prima dell'upload
$(document).on('change', '#labGifFile', function() {
    const file = this.files && this.files[0];
    if (!file) {
        $('#labGifPreview').hide();
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        $('#labGifPreviewImg').attr('src', e.target.result);
        $('#labGifPreview').show();
    };
    reader.readAsDataURL(file);
});

function uploadLabGif() {
    const fileInput = $('#labGifFile')[0];
    if (!fileInput || !fileInput.files || !fileInput.files.length) {
        // Nessun file, ritorna path esistente (può essere vuoto)
        return $.Deferred().resolve($('#labGif').val()).promise();
    }
    const fd = new FormData();
    fd.append('gif', fileInput.files[0]);
    return $.ajax({
        url: `${API}/upload`,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false
    }).then((res) => {
        if (res && res.path) {
            $('#labGif').val(res.path);
            return res.path;
        }
        return $.Deferred().reject({ responseJSON: { error: 'Upload fallito' } });
    });
}

$('#btnSaveLaboratorio').click(() => {
    const $btn = $('#btnSaveLaboratorio');
    $btn.prop('disabled', true).text('Salvataggio...');

    uploadLabGif().then(() => {
        const data = new FormData();
        data.append('nome', $('#labNome').val());
        data.append('descrizione', $('#labDesc').val());
        data.append('gif', $('#labGif').val());
        data.append('posti', $('#labPosti').val());
        data.append('id_categoria', $('#labCategoria').val());
        if ($('#labId').val()) data.append('id', $('#labId').val());

        return $.ajax({
            url: `${API}/laboratori`,
            method: 'POST',
            data: data,
            processData: false,
            contentType: false
        });
    }).done(() => {
        bootstrap.Modal.getInstance(document.getElementById('modalLaboratorio')).hide();
        $('#labId').val('');
        $('#labGifFile').val('');
        $('#labGifPreview').hide();
        loadLaboratori();
    }).fail((xhr) => {
        const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nel salvataggio';
        alert(msg);
    }).always(() => {
        $btn.prop('disabled', false).text('Salva');
    });
});

// === CODICI ===
function loadCodici() {
    $.get(`${API}/codici`, (data) => {
        let html = '';
        (data || []).forEach(c => {
            html += `<tr><td><code>${c.codice}</code></td><td>${c.iscritto}</td><td>${c.id_settimana}</td><td>${c.expired ? '⛔ Sì' : '✅ No'}</td></tr>`;
        });
        $('#tblCodici tbody').html(html);
    });
}

$('#btnGenCodice').click(() => {
    $.post(`${API}/codici`, {
        iscritto: $('#genIscritto').val(),
        settimana: $('#genSettimana').val()
    }, (data) => {
        $('#genResult').html(`<span class="text-success">Codice generato: <code>${data[0]?.codice || 'Errore'}</code></span>`);
        loadCodici();
    }).fail(() => {
        $('#genResult').html('<span class="text-danger">Errore nella generazione</span>');
    });
});

// === SETTIMANE ===
function loadSettimane() {
    // Carica edizioni per il dropdown
    $.get(`${API}/edizioni`, (eds) => {
        let opts = '<option value="">-- Seleziona --</option>';
        (eds || []).forEach(e => {
            opts += `<option value="${e.id}">${e.anno} — ${e.nome}</option>`;
        });
        $('#setEdizione').html(opts);
    });
    // Carica settimane
    $.get(`${API}/settimane`, (data) => {
        let html = '';
        (data || []).forEach(s => {
            const edizione = s.edizione_nome ? `${s.edizione_anno} — ${s.edizione_nome}` : '—';
            html += `<tr>
                <td>${s.id}</td>
                <td>${s.nome}</td>
                <td>${edizione}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editSettimana(${s.id},'${(s.nome||'').replace(/'/g,"\\'")}',${s.id_edizione||0})">Modifica</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSettimana(${s.id})">Elimina</button>
                </td>
            </tr>`;
        });
        $('#tblSettimane tbody').html(html);
    });
}

window.editSettimana = function(id, nome, idEdizione) {
    $('#setId').val(id);
    $('#setNome').val(nome);
    $('#setEdizione').val(idEdizione);
    new bootstrap.Modal(document.getElementById('modalSettimana')).show();
};

window.deleteSettimana = function(id) {
    if (!confirm('Eliminare questa settimana? L\'operazione è possibile solo se non ci sono codici associati.')) return;
    $.ajax({
        url: `${API}/settimane`,
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ id: id })
    }).done((res) => {
        if (res && res.success) {
            loadSettimane();
        } else {
            alert('Eliminazione fallita.');
        }
    }).fail((xhr) => {
        const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Eliminazione fallita.';
        alert(msg);
    });
};

$('#btnSaveSettimana').click(() => {
    const nome = $('#setNome').val().trim();
    const idEdizione = $('#setEdizione').val();
    if (!nome || !idEdizione) {
        alert('Inserisci nome ed edizione.');
        return;
    }
    const data = { nome: nome, id_edizione: idEdizione };
    if ($('#setId').val()) data.id = $('#setId').val();
    $.post(`${API}/settimane`, data, () => {
        bootstrap.Modal.getInstance(document.getElementById('modalSettimana')).hide();
        $('#setId').val(''); $('#setNome').val(''); $('#setEdizione').val('');
        loadSettimane();
    }).fail((xhr) => {
        const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nel salvataggio';
        alert(msg);
    });
});

// === SYNC GESTIONALE ===

var syncIscritti = []; // Iscritti caricati dallo step 2 / arricchiti con codici allo step 3

$('#btnGestLogin').click(function() {
    const user = $('#gestUser').val().trim();
    const pass = $('#gestPass').val();
    if (!user || !pass) {
        $('#gestLoginStatus').html('<span class="text-danger">Inserisci username e password.</span>');
        return;
    }
    const $btn = $(this);
    $btn.prop('disabled', true).text('Connessione...');
    $.post(`${API}/sync?action=login`, { gest_user: user, gest_pass: pass })
        .done(() => {
            $('#gestLoginStatus').html('<span class="text-success">✅ Connesso al gestionale.</span>');
            $('#syncStep2').show();
        })
        .fail((xhr) => {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Login fallito';
            $('#gestLoginStatus').html(`<span class="text-danger">❌ ${msg}</span>`);
        })
        .always(() => { $btn.prop('disabled', false).text('Connetti'); });
});

$('#btnLoadAttivita').click(function() {
    const anno = $('#syncAnno').val();
    const $btn = $(this);
    $btn.prop('disabled', true).text('Caricamento...');
    $('#attivitaList').html('<p class="text-muted">Caricamento attività...</p>');
    $.get(`${API}/sync?action=attivita&anno=${encodeURIComponent(anno)}`)
        .done((data) => {
            // L'API gestionale può rispondere con array o oggetto wrapped
            const attivita = Array.isArray(data) ? data : (data && (data.attivita || data.results || data.data)) || [];
            if (!attivita.length) {
                $('#attivitaList').html('<p class="text-muted">Nessuna attività trovata per questo anno.</p>');
                return;
            }
            let html = '<table class="table table-sm table-hover"><thead><tr><th>ID</th><th>Nome</th><th>Iscritti</th><th></th></tr></thead><tbody>';
            attivita.forEach(a => {
                const nome = a.nome || a.titolo || a.denominazione || a.descrizione || `Attività #${a.id}`;
                const n = a.num_iscritti ?? a.iscritti ?? '—';
                html += `<tr>
                    <td>${a.id ?? ''}</td>
                    <td>${nome}</td>
                    <td>${n}</td>
                    <td><button class="btn btn-sm btn-primary btn-seleziona-attivita" data-id="${a.id}">Seleziona</button></td>
                </tr>`;
            });
            html += '</tbody></table>';
            $('#attivitaList').html(html);
        })
        .fail((xhr) => {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nel caricamento';
            $('#attivitaList').html(`<span class="text-danger">❌ ${msg}</span>`);
        })
        .always(() => { $btn.prop('disabled', false).text('Carica Attività'); });
});

$(document).on('click', '.btn-seleziona-attivita', function() {
    const $btn = $(this);
    const idAttivita = $btn.data('id');
    $('.btn-seleziona-attivita').prop('disabled', true);
    $btn.text('Caricamento...');
    $.get(`${API}/sync?action=iscritti&id_attivita=${encodeURIComponent(idAttivita)}`)
        .done((data) => {
            const iscritti = Array.isArray(data) ? data : (data && (data.iscritti || data.results || data.data)) || [];
            if (!iscritti.length) {
                alert('Nessun iscritto trovato per questa attività.');
                $('.btn-seleziona-attivita').prop('disabled', false);
                $btn.text('Seleziona');
                return;
            }
            // Normalizza ogni iscritto con campo `id` standard
            syncIscritti = iscritti.map(i => ({
                id: i.id || i.id_iscritto || i.id_socio,
                nome: i.nome || '',
                cognome: i.cognome || '',
                email: i.email || ''
            }));
            // Pre-carica template email da example_email.html
            $.get('../example_email.html')
                .done((html) => { $('#emailTemplate').val(html); })
                .fail(() => { /* template non trovato, textarea resta vuota */ });
            $('#syncStep3').show();
            // Reset step 4 se già visibile
            $('#syncStep4').hide();
            $('#emailResults').html('');
            $('html, body').animate({ scrollTop: $('#syncStep3').offset().top - 20 }, 300);
            $('.btn-seleziona-attivita').prop('disabled', false);
            $btn.text('✓ Selezionata');
        })
        .fail((xhr) => {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nel caricamento iscritti';
            alert(msg);
            $('.btn-seleziona-attivita').prop('disabled', false);
            $btn.text('Seleziona');
        });
});

$('#btnGenCodiciSync').click(function() {
    if (!syncIscritti.length) {
        alert('Nessun iscritto caricato. Torna allo step 2 e seleziona un\'attività.');
        return;
    }
    const settimana = $('#syncSettimana').val();
    if (!settimana) {
        alert('Inserisci il numero della settimana.');
        return;
    }
    const $btn = $(this);
    $btn.prop('disabled', true).text('Generazione...');
    $('#syncIscritti').html('<p class="text-muted">Generazione codici in corso...</p>');

    $.post(`${API}/sync?action=genera_codici`, {
        iscritti: JSON.stringify(syncIscritti),
        settimana: settimana
    })
        .done((data) => {
            const risultati = data || [];
            let html = '<table class="table table-sm"><thead><tr><th>ID</th><th>Iscritto</th><th>Codice</th><th>Stato</th></tr></thead><tbody>';
            risultati.forEach(r => {
                const stato = r.codice
                    ? (r.nuovo ? '<span class="badge bg-success">Nuovo</span>' : '<span class="badge bg-secondary">Esistente</span>')
                    : '<span class="badge bg-danger">Errore</span>';
                html += `<tr>
                    <td>${r.id_iscritto}</td>
                    <td>${(r.nome || '') + ' ' + (r.cognome || '')}</td>
                    <td><code>${r.codice || '—'}</code></td>
                    <td>${stato}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            $('#syncIscritti').html(html);
            // Prepara destinatari per step 4 (solo chi ha codice)
            syncIscritti = risultati
                .filter(r => r.codice)
                .map(r => ({
                    id_iscritto: r.id_iscritto,
                    nome: r.nome || '',
                    cognome: r.cognome || '',
                    email: r.email || '',
                    codice: r.codice
                }));
            $('#syncStep4').show();
            $('html, body').animate({ scrollTop: $('#syncStep4').offset().top - 20 }, 300);
        })
        .fail((xhr) => {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nella generazione codici';
            $('#syncIscritti').html(`<span class="text-danger">❌ ${msg}</span>`);
        })
        .always(() => { $btn.prop('disabled', false).text('Genera Codici'); });
});

$('#btnInviaEmail').click(function() {
    const template = $('#emailTemplate').val().trim();
    const oggetto = $('#emailOggetto').val().trim();
    if (!template) { alert('Il template email è vuoto.'); return; }
    if (!oggetto) { alert('Inserisci l\'oggetto dell\'email.'); return; }
    if (!syncIscritti.length) { alert('Nessun destinatario disponibile. Genera prima i codici.'); return; }

    const conferma = confirm(`Stai per inviare ${syncIscritti.length} email. Confermi?`);
    if (!conferma) return;

    const $btn = $(this);
    $btn.prop('disabled', true).text('Invio in corso...');
    $('#emailResults').html('<div class="alert alert-info">📨 Invio in corso, attendere... (l\'operazione può richiedere diversi minuti)</div>');

    $.post(`${API}/sync?action=invia_email`, {
        destinatari: JSON.stringify(syncIscritti),
        template: template,
        oggetto: oggetto
    })
        .done((data) => {
            const risultati = data || [];
            const ok = risultati.filter(r => r.status === 'OK').length;
            const err = risultati.filter(r => r.status !== 'OK').length;
            const cls = err > 0 ? 'warning' : 'success';
            let html = `<div class="alert alert-${cls}">
                ✅ Invio completato: <strong>${ok}</strong> OK, <strong>${err}</strong> errori.
            </div>`;
            html += '<table class="table table-sm"><thead><tr><th>ID</th><th>Nome</th><th>Stato</th></tr></thead><tbody>';
            risultati.forEach(r => {
                const cls2 = r.status === 'OK' ? 'text-success' : 'text-danger';
                html += `<tr><td>${r.id_iscritto}</td><td>${r.nome || ''}</td><td class="${cls2}">${r.status}${r.dettaglio ? ' — ' + r.dettaglio : ''}</td></tr>`;
            });
            html += '</tbody></table>';
            $('#emailResults').html(html);
        })
        .fail((xhr) => {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nell\'invio';
            $('#emailResults').html(`<div class="alert alert-danger">❌ ${msg}</div>`);
        })
        .always(() => { $btn.prop('disabled', false).text('📧 Invia Email a tutti'); });
});

// Initial load
loadDashboard();
