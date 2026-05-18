// === Frontend wizard a step per categoria (Fix 4b) ===
const params = new URLSearchParams(window.location.search);
const codice = params.get('codice');

// Stato globale
var currentStep = 0;
var totalSteps = 0;
var ordineScelte = {}; // { categoriaId: [labId, ...] }
var modalErrore = document.getElementById('modalErrore') ? new bootstrap.Modal(document.getElementById('modalErrore'), {}) : null;

// === Toast Notifications (Redesigned) ===
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.textContent = message;

    container.appendChild(toast);

    if (duration > 0) {
        setTimeout(() => {
            toast.classList.add('remove');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    return toast;
}

function hideToasts() {
    document.querySelectorAll('.toast').forEach(t => {
        t.classList.add('remove');
        setTimeout(() => t.remove(), 300);
    });
}

// === Helpers ===
function getCurrentStepEl() {
    return $(`.categoria-step[data-step="${currentStep}"]`);
}

function getCurrentCategoriaId() {
    return getCurrentStepEl().attr('data-categoria-id') || '0';
}

function getMaxScelte() {
    return parseInt(getCurrentStepEl().attr('data-max-scelte')) || 5;
}

function getCurrentOrdine() {
    const catId = getCurrentCategoriaId();
    if (!ordineScelte[catId]) ordineScelte[catId] = [];
    return ordineScelte[catId];
}

function updateCounter() {
    const count = getCurrentOrdine().length;
    const max = getMaxScelte();
    $('#counterText').html(`Hai selezionato <strong>${count}</strong> su <strong>${max}</strong> laboratori`);
    $('#maxCount').text(max);
    if (count >= max) {
        $('#selectionCounter').addClass('complete');
    } else {
        $('#selectionCounter').removeClass('complete');
    }
}

function updateBadges() {
    // Per ogni categoria, aggiorna i badge in base al proprio ordineScelte
    Object.keys(ordineScelte).forEach(catId => {
        const $step = $(`.categoria-step[data-categoria-id="${catId}"]`);
        $step.find('.laboratorio .lab-badge').text('');
        ordineScelte[catId].forEach((labId, index) => {
            $step.find(`.laboratorio[data-lab-id="${labId}"] .lab-badge`).text(index + 1);
        });
    });
}

function updateStepIndicator() {
    $('.step-dot').removeClass('active completed');
    $('.step-dot').each(function() {
        const n = parseInt($(this).attr('data-step-dot'));
        if (n < currentStep) $(this).addClass('completed');
        else if (n === currentStep) $(this).addClass('active');
    });
}

function showStep(n) {
    if (n < 0 || n >= totalSteps) return;
    currentStep = n;
    $('.categoria-step').removeClass('active');
    getCurrentStepEl().addClass('active');
    updateStepIndicator();
    updateCounter();
    updateButtons();
    // Scroll to top of step
    $('html, body').animate({ scrollTop: 0 }, 200);
}

function updateButtons() {
    if (currentStep > 0) {
        $('#indietro').show();
    } else {
        $('#indietro').hide();
    }
    if (currentStep < totalSteps - 1) {
        $('#conferma').text('AVANTI');
    } else {
        $('#conferma').text('CONFERMA');
    }
}

// === Click su card laboratorio: send immediato real-time ===
$(document).on('click', '.laboratorio', function () {
    const $card = $(this);
    if ($card.is('.choosen') || $card.is('.completo') || $card.is('.busy')) {
        return;
    }
    const labId = $card.attr('data-lab-id');
    const catId = $card.attr('data-categoria-id') || getCurrentCategoriaId();
    if (!ordineScelte[catId]) ordineScelte[catId] = [];
    const arr = ordineScelte[catId];
    let check = $card.find('input[type="checkbox"]');
    const SELEZIONABILI = parseInt($card.attr('data-max-scelte')) || 5;

    if (check.prop('checked')) {
        // Deseleziona: DELETE real-time
        $card.addClass('busy');
        var data = new FormData();
        data.append('codice', codice);
        data.append('laboratorio', labId);
        data.append('_method', 'DELETE');
        $.ajax({
            url: 'api/scelte',
            method: 'POST',
            processData: false,
            contentType: false,
            data: data
        }).done(() => {
            $card.removeClass('selected');
            check.prop('checked', false);
            ordineScelte[catId] = arr.filter(id => id !== labId);
            updateBadges();
            updateCounter();
            showToast(`Rimosso da le tue scelte`, 'info', 2000);
        }).fail((xhr) => {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nella rimozione della scelta';
            showToast(msg, 'error');
        }).always(() => {
            $card.removeClass('busy');
        });
    } else if (arr.length < SELEZIONABILI) {
        // Seleziona: POST real-time
        $card.addClass('busy');
        const ordine = arr.length + 1;
        var data = new FormData();
        data.append('codice', codice);
        data.append('laboratorio', labId);
        data.append('ordine', ordine);
        $.ajax({
            url: 'api/scelte',
            method: 'POST',
            processData: false,
            contentType: false,
            data: data
        }).done(() => {
            $card.addClass('selected');
            check.prop('checked', true);
            ordineScelte[catId].push(labId);
            updateBadges();
            updateCounter();
            showToast(`✓ "${$card.find('.name').text()}" aggiunto alle tue scelte!`, 'success', 2500);
        }).fail((xhr) => {
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Errore nel salvataggio della scelta. Il laboratorio potrebbe essere esaurito.';
            showToast(msg, 'error');
            refreshPosti();
        }).always(() => {
            $card.removeClass('busy');
        });
    } else {
        showToast(`Puoi scegliere al massimo ${SELEZIONABILI} attività per questo passo!`, 'warning');
    }
});

// === Caricamento iniziale: scelte già fatte + posti esauriti ===
function refreshPosti() {
    return $.get("api/laboratori?codice=" + encodeURIComponent(codice), (labs) => {
        (labs || []).forEach(lab => {
            const $card = $(`.laboratorio[data-lab-id="${lab.id}"]`);
            // Aggiorna data attributes
            $card.attr('data-prenotazioni', lab.prenotazioni);
            $card.attr('data-posti', lab.posti);
            $card.find('.esaurito-label').remove();
            $card.removeClass('completo');
            if ((lab.posti - lab.prenotazioni) <= 0) {
                if (!$card.is('.selected') && !$card.is('.choosen')) {
                    $card.addClass('completo')
                        .append('<div class="esaurito-label">ESAURITO</div>');
                }
            }
        });
    });
}

if (codice && $('.categoria-step').length > 0) {
    totalSteps = $('.categoria-step').length;
    updateButtons();
    updateStepIndicator();

    // Carica scelte già fatte e assegna a ogni categoria
    $.get("api/scelte?codice=" + encodeURIComponent(codice), (scelte) => {
        (scelte || []).forEach(scelta => {
            const $card = $(`.laboratorio[data-lab-id="${scelta.id_laboratorio}"]`);
            if (!$card.length) return;
            const catId = $card.attr('data-categoria-id') || '0';
            if (!ordineScelte[catId]) ordineScelte[catId] = [];
            $card.find('input[type="checkbox"]').prop('checked', true);
            $card.addClass('selected');
            ordineScelte[catId].push(String(scelta.id_laboratorio));
        });
        updateBadges();
        updateCounter();
    }).then(() => {
        refreshPosti();
    });
}

// === Navigazione step ===
function goAvanti() {
    const max = getMaxScelte();
    const count = getCurrentOrdine().length;
    if (count < max) {
        showToast(`Devi scegliere esattamente ${max} attività in questo passo prima di proseguire!`, 'warning');
        return;
    }
    if (currentStep < totalSteps - 1) {
        showToast(`Perfetto! Procedi al passo successivo ✨`, 'success', 1500);
        setTimeout(() => showStep(currentStep + 1), 300);
    } else {
        showToast(`Tutte le tue scelte sono state salvate! 🎉`, 'success', 1500);
        setTimeout(() => {
            window.location.href = "?done=" + encodeURIComponent(codice);
        }, 600);
    }
}

function goIndietro() {
    if (currentStep > 0) {
        showStep(currentStep - 1);
    }
}

// === Submit codice (stato NOCODICE / NONVALIDO / EXPIRED) ===
function inviaCodice() {
    window.location.href = "?codice=" + encodeURIComponent($("#codice").val());
}

// Expose globally
window.inviaScelte = goAvanti;       // alias retrocompatibile per index.php
window.inviaCodice = inviaCodice;
window.goAvanti = goAvanti;
window.goIndietro = goIndietro;

$(document).ready(function() {
    $('#indietro').click(goIndietro);

    // Auto-focus codice input
    const codiceInput = $('#codice');
    if (codiceInput.length) {
        codiceInput.focus();
        // Enter key submit
        codiceInput.keypress(function(e) {
            if (e.which == 13) {
                inviaCodice();
                return false;
            }
        });
    }
});
