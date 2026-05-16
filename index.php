<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="Scegli i tuoi laboratori preferiti per le attività estive Juvenes">
    <title>Scelta Attività Laboratoriali</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/frontend.css">
</head>

<?php
if (isset($_GET['done'])) {
    $STATO = 'DONE';
} elseif (!isset($_GET['codice'])) {
    $STATO = 'NOCODICE';
} else {
    $path = $_SERVER['DOCUMENT_ROOT'];
    $path .= "/models/codici.php";
    include_once $path;

    $codici = new Codici();
    $codice = $codici->fromCodice($_GET['codice']);
    if (!$codice) {
        $STATO = 'NONVALIDO';
    } else if ($codice[0]['expired']) {
        $STATO = 'EXPIRED';
    } else {
        $path = $_SERVER['DOCUMENT_ROOT'];
        $path .= "/models/laboratori.php";
        include_once $path;

        $laboratori = new Laboratori();
        $lista = $laboratori->fromCodice($_GET['codice']);

        if (!$lista) $STATO = 'NONVALIDO';
        else $STATO = 'OK';
    }
}
?>

<body style="background: url(&quot;assets/img/Juvenes-immagine-ingresso-per-sito-1.jpg&quot;) center fixed no-repeat;background-size: cover;">
    <section class="projects-horizontal" style="background-color: #00000000">
        <div class="container" style="padding-bottom: 200px;">
            <form onsubmit="return false">
                <div class="intro">
                    <h1 class="text-center page-title">SCELTA LABORATORI</h1>
                    <p class="text-center page-subtitle">
                        <?php
                        if ($STATO == 'DONE') echo 'Grazie per aver inviato le tue preferenze!';
                        elseif ($STATO == 'OK') echo 'Scegli i laboratori che più ti piacciono!';
                        elseif ($STATO == 'NOCODICE') echo 'Inserisci il tuo codice';
                        elseif ($STATO == 'EXPIRED') echo 'Il codice inserito è scaduto<br>Immetti un codice valido';
                        else echo 'Il codice inserito non è valido<br>Immetti il tuo codice';
                        ?>
                    </p>
                </div>

                <?php
                // Raggruppa laboratori per categoria (Fix 4b)
                $categorie_steps = [];
                if ($STATO == 'OK') {
                    foreach ($lista as $lab) {
                        $cat_id = $lab['id_categoria'] ?? 0;
                        if (!isset($categorie_steps[$cat_id])) {
                            $categorie_steps[$cat_id] = [
                                'id' => $cat_id,
                                'nome' => $lab['categoria_nome'] ?? 'Laboratori',
                                'descrizione' => $lab['categoria_descrizione'] ?? '',
                                'max_scelte' => $lab['max_scelte'] ?? 5,
                                'laboratori' => []
                            ];
                        }
                        $categorie_steps[$cat_id]['laboratori'][] = $lab;
                    }
                    $categorie_steps = array_values($categorie_steps);
                }
                $totalSteps = count($categorie_steps);
                ?>

                <?php if ($STATO == 'OK'): ?>
                <!-- Step Indicator (Fix 4b) -->
                <?php if ($totalSteps > 1): ?>
                <div class="step-indicator" id="stepIndicator">
                    <?php for ($i = 0; $i < $totalSteps; $i++): ?>
                        <div class="step-dot<?= $i === 0 ? ' active' : '' ?>" data-step-dot="<?= $i ?>">
                            <span class="step-num"><?= $i + 1 ?></span>
                            <span class="step-name"><?= htmlspecialchars($categorie_steps[$i]['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <?php if ($i < $totalSteps - 1): ?>
                            <div class="step-connector"></div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <!-- Counter real-time (§5) -->
                <div class="selection-counter" id="selectionCounter">
                    <span id="counterText">Hai selezionato <strong>0</strong> laboratori</span>
                </div>
                <?php endif; ?>

                <div class="projects-wrapper">
                    <?php
                if ($STATO == 'OK') {
                    foreach ($categorie_steps as $idx => $cat) {
                        $active_class = $idx === 0 ? ' active' : '';
                        echo '<div class="categoria-step' . $active_class . '" data-step="' . $idx . '" data-max-scelte="' . htmlspecialchars($cat['max_scelte'], ENT_QUOTES, 'UTF-8') . '" data-categoria-id="' . htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8') . '">';
                        echo '<h2 class="categoria-header">' . htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') . '</h2>';
                        if (!empty($cat['descrizione'])) {
                            echo '<p class="categoria-descrizione">' . htmlspecialchars($cat['descrizione'], ENT_QUOTES, 'UTF-8') . '</p>';
                        }
                        echo '<p class="categoria-info">Scegli <strong>' . htmlspecialchars($cat['max_scelte'], ENT_QUOTES, 'UTF-8') . '</strong> laboratori in ordine di preferenza.</p>';
                        echo '<div class="row projects">';
                        foreach ($cat['laboratori'] as $lab) {
                            echo '<div class="col-sm-6 d-flex mx-auto item">
                                <label class="row laboratorio" data-lab-id="' . htmlspecialchars($lab['id'], ENT_QUOTES, 'UTF-8') . '" data-posti="' . htmlspecialchars($lab['posti'], ENT_QUOTES, 'UTF-8') . '" data-prenotazioni="' . htmlspecialchars($lab['prenotazioni'], ENT_QUOTES, 'UTF-8') . '" data-max-scelte="' . htmlspecialchars($cat['max_scelte'], ENT_QUOTES, 'UTF-8') . '" data-categoria-id="' . htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8') . '" data-categoria="' . htmlspecialchars($cat['nome'], ENT_QUOTES, 'UTF-8') . '">
                                    <input type="checkbox" class="lab-checkbox visually-hidden" aria-label="' . htmlspecialchars($lab['nome'], ENT_QUOTES, 'UTF-8') . '">
                                    <div class="lab-badge" aria-hidden="true"></div>
                                    <div class="col-12 col-lg-5 d-flex align-items-center lab-image-wrap"><img class="img-fluid" src="' . htmlspecialchars($lab['gif'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($lab['nome'], ENT_QUOTES, 'UTF-8') . '" loading="lazy"></div>
                                    <div class="col lab-info">
                                        <h3 class="name">' . htmlspecialchars($lab['nome'], ENT_QUOTES, 'UTF-8') . '</h3>
                                        <p class="description">' . htmlspecialchars($lab['descrizione'], ENT_QUOTES, 'UTF-8') . '</p>
                                    </div>
                                </label>
                            </div>';
                        }
                        echo '</div></div>';
                    }
                } elseif ($STATO != 'DONE') {
                    echo '<div class="row projects"><input type="text"
                        id="codice"
                        class="form-control codice-input"
                        pattern="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"
                        title="Codice personale"
                        placeholder="Inserisci il tuo codice..."
                        autocomplete="off"></div>';
                }
                    ?>
                </div>
                <?php
                if ($STATO != 'DONE') {
                    if ($STATO == 'OK') {
                        echo '<div class="bottom-bar">
                            <div class="container text-center d-flex justify-content-center align-items-center gap-2">
                                <button id="indietro" class="btn btn-indietro" type="button" style="display:none">INDIETRO</button>
                                <button id="conferma" class="btn btn-conferma" type="button">' . ($totalSteps > 1 ? 'AVANTI' : 'CONFERMA') . '</button>
                            </div>
                        </div>';
                    } else {
                        echo '<div class="bottom-bar">
                            <div class="container text-center">
                                <button id="conferma" class="btn btn-conferma" type="button">CONFERMA</button>
                            </div>
                        </div>';
                    }
                } else {
                    $path = $_SERVER['DOCUMENT_ROOT'];
                    $path .= "/models/scelte.php";
                    include_once $path;

                    $scelte = new Scelte();
                    $done_codice = htmlspecialchars($_GET['done'], ENT_QUOTES, 'UTF-8');
                    $lista = $scelte->fromCodice($done_codice);

                    $path = $_SERVER['DOCUMENT_ROOT'];
                    $path .= "/models/laboratori.php";
                    include_once $path;

                    $laboratori = new Laboratori();
                    echo '<div class="done-recap"><h3>Ecco le tue scelte:</h3><ol class="done-list">';
                    foreach ($lista as $scelta) 
                        echo '<li>' . htmlspecialchars($laboratori->fromId($scelta['id_laboratorio'])[0]['nome'], ENT_QUOTES, 'UTF-8') . '</li>';
                    echo '</ol>';
                    echo '<small>Per qualsiasi dubbio, scrivi a <a href="mailto:iscrizioni+laboratori@juvenes.it">iscrizioni+laboratori@juvenes.it</a> o contatta un educatore.</small></div>';
                }
                ?>
            </form>
        </div>
    </section>

    <div class="modal fade" id="modalErrore" tabindex="-1" aria-labelledby="modalErrore" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="modalErroreLabel">Errore</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="testoErrore">
            Si &egrave; verificato un errore!
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
        </div>
        </div>
    </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/script.js?v=3"></script>

    <?php
    if ($STATO != 'OK') echo '<script>$("#conferma").click(() => inviaCodice())</script>';
    else echo '<script>$("#conferma").click(() => inviaScelte())</script>';
    ?>
</body>

</html>
