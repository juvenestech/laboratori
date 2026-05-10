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
    // Clear session by making a request, then redirect
    window.location.href = '../'; 
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
    if ($('#catId').val()) data.id = $('#catId').val();
    else data.id_edizione = 1; // Default, to be selected dynamically later
    $.post(`${API}/categorie`, data, () => {
        bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
        $('#catId').val(''); $('#catNome').val(''); $('#catMax').val(5); $('#catDesc').val('');
        loadCategorie();
    });
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

$('#btnSaveLaboratorio').click(() => {
    const data = new FormData();
    data.append('nome', $('#labNome').val());
    data.append('descrizione', $('#labDesc').val());
    data.append('gif', $('#labGif').val());
    data.append('posti', $('#labPosti').val());
    data.append('id_categoria', $('#labCategoria').val());
    if ($('#labId').val()) data.append('id', $('#labId').val());
    // For now, use the existing laboratori endpoint or a new admin one
    // This would need a POST handler in api/laboratori.php
    $.ajax({ url: `${API}/laboratori`, method: 'POST', data: data, processData: false, contentType: false,
        success: () => {
            bootstrap.Modal.getInstance(document.getElementById('modalLaboratorio')).hide();
            $('#labId').val('');
            loadLaboratori();
        }
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

// Initial load
loadDashboard();
