<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;

if (isset($_GET['id'])) {
    $idcomp = (int) $_GET['id'];
    $competizione = Competizione::getCompetizioneById($idcomp);
    $squadreJson = $competizione->squadre;

    // Decodifica la stringa JSON in un array
    $squadre = json_decode($squadreJson, true);

    // Controlla se la decodifica è andata a buon fine
    if (json_last_error() === JSON_ERROR_NONE && is_array($squadre)) {
        $giornate = generaCampionato($squadre);
        ?>

        <div class="container">
            <div class="row">
                <?php foreach ($giornate as $index => $partite): ?>
                    <div class="col-12 col-lg-6">
                        <div class="card mb-4">
                            <div class="card-title p-2">
                                <h5 class="text-center m-0 fw-bold">GIORNATA <?php echo $index + 1; ?></h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($partite as $partita): ?>
                                    <?php
                                    $s1 = Competizione::getArticleTitleById($partita['squadra1']);
                                    $s2 = Competizione::getArticleTitleById($partita['squadra2']);
                                    $cf1 = Competizione::getCustomFields($partita['squadra1']);
                                    $cf2 = Competizione::getCustomFields($partita['squadra2']);
                                    $colors1 = !empty($cf1[1]) ? $cf1[1]->value : '#000000'; // Colore di sfondo del titolo
                                    $colort1 = !empty($cf1[2]) ? $cf1[2]->value : '#ffffff'; // Colore del testo
                                    $colors2 = !empty($cf2[1]) ? $cf2[1]->value : '#000000'; // Colore di sfondo del titolo
                                    $colort2 = !empty($cf2[2]) ? $cf2[2]->value : '#ffffff'; // Colore del testo
                                    ?>
                                    <div class="d-flex my-3">
                                        <div class="p-1 text-center" style="border-radius:50px; width:200px; background-color: <?php echo $colors1; ?>;">
                                            <span  style="color: <?php echo $colort1; ?>;"><?php echo htmlspecialchars($s1); ?> </span>
                                        </div>
                                        <div class="mx-3"></div>
                                        <div class="p-1 text-center" style="border-radius:50px; width:200px; background-color: <?php echo $colors2; ?>;">
                                            <span  style="color: <?php echo $colort2; ?>;"><?php echo htmlspecialchars($s2); ?> </span>
                                        </div>
                                        
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}


function generaCampionato($squadre)
{
    $giornate = [];
    $numeroSquadre = count($squadre);
    // Mischia l'array delle squadre
    shuffle($squadre);
    // Se il numero di squadre è dispari, aggiungi un "buono"
    if ($numeroSquadre % 2 != 0) {
        $squadre[] = 'Riposo'; // Una squadra non gioca
        $numeroSquadre++;
    }

    // Genera il calendario
    for ($giornata = 0; $giornata < $numeroSquadre - 1; $giornata++) {
        $partite = [];
        for ($i = 0; $i < $numeroSquadre / 2; $i++) {
            // La squadra fissa è sempre la prima
            $squadraCasa = $squadre[$i];
            $squadraTrasferta = $squadre[$numeroSquadre - 1 - $i];

            if ($squadraTrasferta !== 'Riposo') {
                $partite[] = [
                    'squadra1' => $squadraCasa,
                    'squadra2' => $squadraTrasferta,
                ];
            }
        }
        $giornate[] = $partite;

        // Ruota le squadre, mantenendo la prima fissa
        $squadre = array_merge(
            [$squadre[0]], // La squadra fissa
            array_slice($squadre, 2), // Tutte tranne la prima
            [$squadre[1]] // L'ultima squadra torna alla fine
        );
    }

    // Aggiungi le partite di ritorno
    foreach ($giornate as $index => $partite) {
        $giornate[] = array_map(function ($partita) {
            return [
                'squadra1' => $partita['squadra2'],
                'squadra2' => $partita['squadra1'],
            ];
        }, $partite);
    }

    return $giornate;
}



?>