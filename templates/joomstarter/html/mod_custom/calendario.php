<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;
// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;


if (isset($_GET['id'])) {
    $idcomp = (int) $_GET['id'];
    $competizione = Competizione::getCompetizioneById($idcomp, $userId);
    // Recupera le giornate dalla competizione
    $db = Factory::getDbo();
    $prefix = $db->getPrefix();
    $tablePartite = $prefix . 'competizione' . $idcomp . '_partite';
    $giornateRaw = Competizione::getGiornateByCompetizioneId($idcomp, $tablePartite);
    // Riorganizza le partite in giornate
    $giornate = [];
    foreach ($giornateRaw as $partita) {
        $giornate[$partita->giornata][] = [
            'squadra1' => $partita->squadra1,
            'squadra2' => $partita->squadra2,
            'gol1' => $partita->gol1,
            'gol2' => $partita->gol2,
            'giornata' => $partita->giornata,
            'girone' => $partita->girone,
        ];
    } ?>
    <div class="container calendario">
        <div class="row">
            <?php foreach ($giornate as $index => $partite): ?>
                <div class="col-12 col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header p-2">
                            <h5 class="text-center m-0 fw-bold">GIORNATA <?php echo $index; ?></h5>
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
                                if(isset($partita['gol1'])){ $gol1 = $partita['gol1']; } else $gol1 = "";
                                if(isset($partita['gol2'])){ $gol2 = $partita['gol2']; } else $gol2 = "";
                                ?>
                                <div class="d-flex my-3 fw-bold align-items-center myinput">
                                    <div class="p-1 text-center"
                                        style="border-radius:50px; width:200px; background-color: <?php echo $colors1; ?>;">
                                        <span style="color: <?php echo $colort1; ?>;"><?php echo htmlspecialchars($s1); ?></span>
                                    </div>
                                    <div class="mx-3"></div>
                                    <div class="p-1 text-center"
                                        style="border-radius:50px; width:200px; background-color: <?php echo $colors2; ?>;">
                                        <span style="color: <?php echo $colort2; ?>;"><?php echo htmlspecialchars($s2); ?></span>
                                    </div>
                                    <form action="" class="d-flex align-items-center ms-3" method="post">
                                        <input type="number" name="<?php echo $partita['squadra1']; ?>_gol1"
                                            class="form-control me-2 text-center" value="<?php echo $gol1; ?>">
                                        <input type="number" name="<?php echo $partita['squadra2']; ?>_gol2"
                                            class="form-control text-center" value="<?php echo $gol2; ?>">
                                        <button type="submit" name="save" class="btn btn-success ms-2"
                                            style="width: 30px; height: 30px; border-radius: 50%;">
                                            <span class="bi bi-check2 text-white" style="font-size:25px;"></span> <!-- Spunta -->
                                        </button>
                                        <button type="submit" name="delete" class="btn btn-danger ms-1"
                                            style="width: 30px; height: 30px; border-radius: 50%;">
                                            <span class="bi bi-x text-white" style="font-size:25px;"></span> <!-- X -->
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer ">
                            <form action="" class="p-2 d-flex justify-content-between" method="post">
                                <button type="submit" name="saveall" class="btn btn-success" style="width: 80px;">Salva</button>
                                <button type="submit" name="deleteall" class="btn btn-danger"
                                    style="width: 80px;">Elimina</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
?>