<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/private/auth.php";
include_once $path;

if(!$AUTH) {
    // Mostra form di login
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Scelta Laboratori</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-login-body">
    <div class="login-card">
        <h2>🔒 Admin Panel</h2>
        <p class="text-muted">Scelta Laboratori</p>
        <form id="loginForm">
            <div class="mb-3">
                <input type="text" class="form-control" id="username" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" id="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Accedi</button>
            <div id="loginError" class="text-danger mt-2 text-center" style="display:none">Credenziali non valide</div>
        </form>
    </div>
    <script src="../assets/js/jquery.min.js"></script>
    <script>
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        $.post('../login', {
            username: $('#username').val(),
            password: $('#password').val()
        }).done(() => location.reload())
          .fail(() => $('#loginError').show());
    });
    </script>
</body>
</html>
<?php
    return;
}
// === DASHBOARD ADMIN (utente autenticato) ===
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Scelta Laboratori</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="admin-sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">🧪</span>
            <span class="brand-text">Laboratori</span>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-item active" data-section="dashboard">
                <span class="nav-icon">📊</span> Dashboard
            </li>
            <li class="nav-item" data-section="edizioni">
                <span class="nav-icon">📅</span> Edizioni
            </li>
            <li class="nav-item" data-section="categorie">
                <span class="nav-icon">📁</span> Categorie
            </li>
            <li class="nav-item" data-section="laboratori">
                <span class="nav-icon">🎨</span> Laboratori
            </li>
            <li class="nav-item" data-section="settimane">
                <span class="nav-icon">🗓️</span> Settimane
            </li>
            <li class="nav-item" data-section="codici">
                <span class="nav-icon">🔑</span> Codici
            </li>
            <li class="nav-item" data-section="export">
                <span class="nav-icon">📥</span> Export
            </li>
            <li class="nav-item" data-section="sync">
                <span class="nav-icon">🔄</span> Sync Gestionale
            </li>
        </ul>
        <div class="sidebar-footer">
            <button id="btnLogout" class="btn btn-outline-light btn-sm w-100">Logout</button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- DASHBOARD -->
        <section id="sec-dashboard" class="admin-section active">
            <h1>Dashboard</h1>
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <div class="stat-value" id="statEdizione">—</div>
                    <div class="stat-label">Edizione Attiva</div>
                </div>
                <div class="stat-card accent">
                    <div class="stat-value" id="statLaboratori">—</div>
                    <div class="stat-label">Laboratori</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-value" id="statCodici">—</div>
                    <div class="stat-label">Codici Generati</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-value" id="statScelte">—</div>
                    <div class="stat-label">Scelte Espresse</div>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5>Riempimento Laboratori</h5>
                        <div class="fill-mode-toggle">
                            <button class="fill-mode-btn active" id="btnModeFill">📊 Riempimento</button>
                            <button class="fill-mode-btn" id="btnModeDemand">🔥 Più richiesti</button>
                        </div>
                    </div>
                    <div class="lab-tabs-wrapper mt-2" style="margin-bottom:0;padding-bottom:0">
                        <ul class="lab-tabs" id="dashCategoryTabs">
                            <li class="lab-tab" data-cat-id="all">Tutti</li>
                        </ul>
                    </div>
                </div>
                <div class="card-body" id="fillBars">
                    <p class="text-muted">Seleziona un'edizione per visualizzare i dati.</p>
                </div>
            </div>
        </section>

        <!-- EDIZIONI -->
        <section id="sec-edizioni" class="admin-section">
            <div class="section-header">
                <h1>Edizioni</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEdizione">+ Nuova Edizione</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tblEdizioni">
                    <thead><tr><th>ID</th><th>Anno</th><th>Nome</th><th>Stato</th><th>Azioni</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <!-- CATEGORIE -->
        <section id="sec-categorie" class="admin-section">
            <div class="section-header">
                <h1>Categorie</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria">+ Nuova Categoria</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tblCategorie">
                    <thead><tr><th>ID</th><th>Nome</th><th>Max Scelte</th><th>Descrizione</th><th>Azioni</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <!-- LABORATORI -->
        <section id="sec-laboratori" class="admin-section">
            <div class="section-header">
                <h1>Laboratori</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLaboratorio">+ Nuovo Laboratorio</button>
            </div>
            <!-- Tab categorie -->
            <div class="lab-tabs-wrapper">
                <ul class="lab-tabs" id="labCategoryTabs">
                    <li class="lab-tab active" data-cat-id="all">Tutti</li>
                </ul>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tblLaboratori">
                    <thead><tr><th>ID</th><th>Nome</th><th>Categoria</th><th>Posti</th><th>Prenotazioni</th><th>Azioni</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <!-- SETTIMANE -->
        <section id="sec-settimane" class="admin-section">
            <div class="section-header">
                <h1>Settimane</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSettimana">+ Nuova Settimana</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tblSettimane">
                    <thead><tr><th>ID</th><th>Nome</th><th>Edizione</th><th>Azioni</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <!-- CODICI -->
        <section id="sec-codici" class="admin-section">
            <div class="section-header">
                <h1>Codici</h1>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h5>Genera Codici</h5>
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label">ID Iscritto</label>
                            <input type="number" class="form-control" id="genIscritto" placeholder="Es: 618">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">Settimana</label>
                            <input type="number" class="form-control" id="genSettimana" placeholder="Es: 1">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" id="btnGenCodice">Genera</button>
                        </div>
                    </div>
                    <div id="genResult" class="mt-2"></div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="tblCodici">
                    <thead><tr><th>Codice</th><th>Iscritto</th><th>Settimana</th><th>Scaduto</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <!-- EXPORT -->
        <section id="sec-export" class="admin-section">
            <h1>Export Dati</h1>
            <div class="card">
                <div class="card-body">
                    <p>Esporta le scelte espresse in formato CSV, leggibile con Excel.</p>
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label">Settimana</label>
                            <input type="number" class="form-control" id="exportSettimana" value="1">
                        </div>
                        <div class="col-auto">
                            <a id="btnExport" class="btn btn-success" href="../api/export?settimana=1" target="_blank">📥 Scarica CSV</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SYNC GESTIONALE -->
        <section id="sec-sync" class="admin-section">
            <h1>Sincronizzazione Gestionale</h1>

            <!-- Step 1: Login -->
            <div class="card mb-3" id="syncStep1">
                <div class="card-header"><h5>1. Autenticazione dbjuvenes.juvenes.it</h5></div>
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" id="gestUser" autocomplete="off">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" id="gestPass">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" id="btnGestLogin">Connetti</button>
                        </div>
                    </div>
                    <div id="gestLoginStatus" class="mt-2"></div>
                </div>
            </div>

            <!-- Step 2: Seleziona attività -->
            <div class="card mb-3" id="syncStep2" style="display:none">
                <div class="card-header"><h5>2. Seleziona Attivit&agrave; e importa iscritti</h5></div>
                <div class="card-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-auto">
                            <label class="form-label">Anno</label>
                            <input type="number" class="form-control" id="syncAnno" value="2026">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-primary" id="btnLoadAttivita">Carica Attivit&agrave;</button>
                        </div>
                    </div>
                    <div id="attivitaList"></div>
                </div>
            </div>

            <!-- Step 3: Genera codici -->
            <div class="card mb-3" id="syncStep3" style="display:none">
                <div class="card-header"><h5>3. Genera codici mancanti</h5></div>
                <div class="card-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-auto">
                            <label class="form-label">Settimana</label>
                            <input type="number" class="form-control" id="syncSettimana" value="1">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" id="btnGenCodiciSync">Genera Codici</button>
                        </div>
                    </div>
                    <div id="syncIscritti" class="table-responsive"></div>
                </div>
            </div>

            <!-- Step 4: Invio email -->
            <div class="card mb-3" id="syncStep4" style="display:none">
                <div class="card-header"><h5>4. Invio email con codici</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Oggetto</label>
                        <input type="text" class="form-control" id="emailOggetto" value="Il tuo codice per la scelta dei laboratori">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template HTML <small class="text-muted">(variabili: \${nome}, \${cognome}, \${codice})</small></label>
                        <textarea class="form-control" id="emailTemplate" rows="10" style="font-family:monospace;font-size:0.8rem"></textarea>
                    </div>
                    <button class="btn btn-warning" id="btnInviaEmail">📧 Invia Email a tutti</button>
                    <div id="emailResults" class="mt-3"></div>
                </div>
            </div>
        </section>
    </main>

    <!-- MODALS -->
    <div class="modal fade" id="modalEdizione" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edizione</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="edId">
            <div class="mb-3"><label class="form-label">Anno</label><input type="number" class="form-control" id="edAnno" placeholder="2026"></div>
            <div class="mb-3"><label class="form-label">Nome</label><input type="text" class="form-control" id="edNome" placeholder="Giorni del Sole 2026"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary" id="btnSaveEdizione">Salva</button></div>
    </div></div></div>

    <div class="modal fade" id="modalCategoria" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Categoria</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="catId">
            <div class="mb-3"><label class="form-label">Nome</label><input type="text" class="form-control" id="catNome"></div>
            <div class="mb-3"><label class="form-label">Max Scelte</label><input type="number" class="form-control" id="catMax" value="5"></div>
            <div class="mb-3"><label class="form-label">Descrizione</label><textarea class="form-control" id="catDesc"></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary" id="btnSaveCategoria">Salva</button></div>
    </div></div></div>

    <div class="modal fade" id="modalSettimana" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Settimana</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="setId">
            <div class="mb-3"><label class="form-label">Nome</label><input type="text" class="form-control" id="setNome" placeholder="Es: Prima settimana"></div>
            <div class="mb-3"><label class="form-label">Edizione</label><select class="form-select" id="setEdizione"></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary" id="btnSaveSettimana">Salva</button></div>
    </div></div></div>

    <div class="modal fade" id="modalLaboratorio" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Laboratorio</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" id="labId">
            <div class="mb-3"><label class="form-label">Nome</label><input type="text" class="form-control" id="labNome"></div>
            <div class="mb-3"><label class="form-label">Descrizione</label><textarea class="form-control" id="labDesc"></textarea></div>
            <div class="mb-3">
                <label class="form-label">Immagine/GIF</label>
                <input type="text" class="form-control" id="labGif" placeholder="assets/img/gif/nome.gif">
                <input type="file" class="form-control mt-2" id="labGifFile" accept="image/*,.gif">
                <small class="text-muted">Path manuale oppure carica un file (max 5MB, .gif/.jpg/.png/.webp).</small>
                <div id="labGifPreview" class="mt-2" style="display:none">
                    <img id="labGifPreviewImg" src="" alt="Preview" style="max-width:200px;border-radius:8px;border:1px solid #ddd">
                </div>
            </div>
            <div class="mb-3"><label class="form-label">Posti</label><input type="number" class="form-control" id="labPosti" value="40"></div>
            <div class="mb-3"><label class="form-label">Categoria</label><select class="form-select" id="labCategoria"></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary" id="btnSaveLaboratorio">Salva</button></div>
    </div></div></div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="admin.js"></script>
</body>
</html>
