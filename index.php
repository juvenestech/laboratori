<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Scelta laboratori</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bitter:400,700&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,700,800,900&amp;display=swap">
    <link rel="stylesheet" href="assets/css/Footer-Dark.css">
    <link rel="stylesheet" href="assets/css/Header-Dark.css">
    <link rel="stylesheet" href="assets/css/Projects-Horizontal.css">
    <link rel="stylesheet" href="assets/css/Responsive-Youtube-Embed.css">
    <link rel="stylesheet" href="assets/css/styles.css">
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

<body style="background: url(&quot;assets/img/Juvenes-immagine-ingresso-per-sito-1.jpg&quot;) center fixed;">
    <section class="projects-horizontal" style="background-color: #00000000">
        <div class="container" style="padding-bottom: 200px;">
            <form onsubmit="return false">
                <div class="intro">
                    <h2 class="text-center" style="font-family: Montserrat, sans-serif;font-weight: bold;text-shadow: 5px 5px rgb(0,0,0);">SCELTA LABORATORI</h2>
                    <p class="text-center" style="font-family: Montserrat, sans-serif;">
                        <?php
                        if ($STATO == 'DONE') echo 'Grazie per aver inviato le tue preferenze!';
                        elseif ($STATO == 'OK') echo 'Scegli i 4 laboratori che più ti piacciono!';
                        elseif ($STATO == 'NOCODICE') echo 'Inserisci il tuo codice';
                        elseif ($STATO == 'EXPIRED') echo 'Il codice inserito è scaduto<br>Immetti un codice valido';
                        else echo 'Il codice inserito non è valido<br>Immetti il tuo codice';
                        ?>
                    </p>
                </div>
                <div class="row projects">
                    <?php
                if ($STATO == 'OK')
                        foreach ($lista as $lab)
                            echo '<div class="col-sm-6 d-flex item">
                                <div class="row laboratorio" lab="' . $lab['id'] . '">
                                    <input type="checkbox" style="display:none"></input>
                                    <div class="col-md-12 col-lg-5 d-flex align-items-center"><img class="img-fluid" src="' . $lab['gif'] . '"></div>
                                    <div class="col">
                                        <h3 class="name" style="font-family: Montserrat, sans-serif;">' . $lab['nome'] . '</h3>
                                        <p class="description" style="font-family: Montserrat, sans-serif;">' . $lab['descrizione'] . '</p>
                                    </div>
                                </div>
                            </div>';
                    elseif ($STATO != 'DONE')
                        echo '<input type="text"
                            id="codice"
                            class="form-control"
                            pattern="^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$"
                            title="Codice personale">
                        </input>';
                    ?>
                </div>
                <?php
                if ($STATO != 'DONE') {
                    echo '<div class="row text-center">
                        <div class="col" style="margin: 10px;">
                            <button id="conferma" class="btn btn-dark border-dark" type="button">CONFERMA</button>
                        </div>
                    </div>';
                } else {
                    $path = $_SERVER['DOCUMENT_ROOT'];
                    $path .= "/models/scelte.php";
                    include_once $path;

                    $scelte = new Scelte();
                    $lista = $scelte->fromCodice($_GET['done']);

                    $path = $_SERVER['DOCUMENT_ROOT'];
                    $path .= "/models/laboratori.php";
                    include_once $path;

                    $laboratori = new Laboratori();
                    echo 'Ecco le tue scelte:<ul>';
                    foreach ($lista as $scelta) 
                        echo '<li>' . $laboratori->fromId($scelta['id_laboratorio'])[0]['nome'] . '</li>';
                    echo '</ul>';
                    echo '<small>Per qualsiasi dubbio, scrivi a <a href="mailto:iscrizioni+laboratori@juvenes.it">iscrizioni+laboratori@juvenes.it</a> o contatta un educatore.</small>';
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
    <script src="assets/js/script.js"></script>

    <?php
    if ($STATO != 'OK') echo '<script>$("#conferma").click(() => inviaCodice())</script>';
    else echo '<script>$("#conferma").click(() => inviaScelte())</script>';
    ?>
</body>

</html>
