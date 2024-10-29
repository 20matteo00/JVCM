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
    $tablePartite = Competizione::getTablePartite($idcomp);
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
                <div class="col-12 col-lg-6" id="<?php echo $index; ?>">
                    <div class="card mb-4">
                        <div class="card-header p-2">
                            <h5 class="text-center m-0 fw-bold">GIORNATA <?php echo $index; ?></h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($partite as $i => $partita): ?>
                                <?php
                                $s1 = Competizione::getArticleTitleById($partita['squadra1']);
                                $s2 = Competizione::getArticleTitleById($partita['squadra2']);
                                $cf1 = Competizione::getCustomFields($partita['squadra1']);
                                $cf2 = Competizione::getCustomFields($partita['squadra2']);
                                $colors1 = !empty($cf1[1]) ? $cf1[1]->value : '#000000';
                                $colort1 = !empty($cf1[2]) ? $cf1[2]->value : '#ffffff';
                                $colors2 = !empty($cf2[1]) ? $cf2[1]->value : '#000000';
                                $colort2 = !empty($cf2[2]) ? $cf2[2]->value : '#ffffff';
                                $gol1 = isset($partita['gol1']) ? $partita['gol1'] : '';
                                $gol2 = isset($partita['gol2']) ? $partita['gol2'] : '';
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
                                        <input type="hidden" name="module_id" value="116">
                                        <input type="hidden" name="giornata" value="<?php echo $index; ?>">
                                        <input type="hidden" name="squadra1" value="<?php echo $partita['squadra1']; ?>">
                                        <input type="hidden" name="squadra2" value="<?php echo $partita['squadra2']; ?>">
                                        <input type="number" id="gol1-<?php echo $index . '-' . $i; ?>" name="gol1" class="form-control me-2 text-center" value="<?php echo $gol1; ?>" onclick="selezionaInput(this)">
                                        <input type="number" id="gol2-<?php echo $index . '-' . $i; ?>" name="gol2" class="form-control text-center" value="<?php echo $gol2; ?>" onclick="selezionaInput(this)">
                                        <button type="submit" name="save" class="btn btn-success ms-2" style="width: 30px; height: 30px; border-radius: 50%;">
                                            <span class="bi bi-check2 text-white" style="font-size:25px;"></span>
                                        </button>
                                        <button type="submit" name="delete" class="btn btn-danger ms-1" style="width: 30px; height: 30px; border-radius: 50%;">
                                            <span class="bi bi-x text-white" style="font-size:25px;"></span>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer">
                            <form action="" class="p-2 d-flex justify-content-between" method="post" onsubmit="updateAllGolValues(<?php echo $index; ?>)">
                                <input type="hidden" name="module_id" value="116">
                                <input type="hidden" name="giornata" value="<?php echo $index; ?>">

                                <?php foreach ($partite as $i => $partita): ?>
                                    <input type="hidden" name="squadra1[]" value="<?php echo $partita['squadra1']; ?>">
                                    <input type="hidden" name="squadra2[]" value="<?php echo $partita['squadra2']; ?>">
                                    <input type="hidden" name="gol1[]" id="hidden-gol1-<?php echo $index . '-' . $i; ?>" value="<?php echo $gol1; ?>">
                                    <input type="hidden" name="gol2[]" id="hidden-gol2-<?php echo $index . '-' . $i; ?>" value="<?php echo $gol2; ?>">
                                <?php endforeach; ?>

                                <button type="submit" name="saveall" class="btn btn-success" style="width: 80px;">Salva</button>
                                <button type="submit" name="deleteall" class="btn btn-danger" style="width: 80px;">Elimina</button>
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

<?php
if (isset($_POST['save'])) {
    $squadra1 = $_POST['squadra1'];
    $squadra2 = $_POST['squadra2'];
    $giornata = $_POST['giornata'];
    if ($_POST['gol1'] != NULL) {
        $gol1 = $_POST['gol1'];
    } else $gol1 = 0;
    if ($_POST['gol2'] != NULL) {
        $gol2 = $_POST['gol2'];
    } else $gol2 = 0;
    $module_ID = $_POST['module_id'];
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->update($db->quoteName($tablePartite))
        ->set([
            'gol1 = ' . $db->quote($gol1),
            'gol2 = ' . $db->quote($gol2)
        ])
        ->where([
            'squadra1 = ' . $db->quote($squadra1),
            'squadra2 = ' . $db->quote($squadra2)
        ]);
    $db->setQuery($query);
    $db->execute();

    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
    exit;
} elseif (isset($_POST['delete'])) {
    $squadra1 = $_POST['squadra1'];
    $squadra2 = $_POST['squadra2'];
    $giornata = $_POST['giornata'];
    $module_ID = $_POST['module_id'];
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->update($db->quoteName($tablePartite))
        ->set([
            $db->quoteName('gol1') . ' = NULL',
            $db->quoteName('gol2') . ' = NULL'
        ])
        ->where([
            $db->quoteName('squadra1') . ' = ' . $db->quote($squadra1),
            $db->quoteName('squadra2') . ' = ' . $db->quote($squadra2)
        ]);
    $db->setQuery($query);
    $db->execute();


    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
    exit;
} elseif (isset($_POST['saveall'])) {
    $module_ID = $_POST['module_id'];
    $giornata = $_POST['giornata'];
    // Recupera i valori delle squadre e dei gol
    $squadre1 = $_POST['squadra1']; // Array di squadre1
    $squadre2 = $_POST['squadra2']; // Array di squadre2
    $gol1 = $_POST['gol1']; // Array di gol1
    $gol2 = $_POST['gol2']; // Array di gol2

    // Assicurati che tutti gli array abbiano la stessa lunghezza
    $count = count($squadre1);

    if ($count === count($squadre2) && $count === count($gol1) && $count === count($gol2)) {
        $db = Factory::getDbo();

        for ($i = 0; $i < $count; $i++) {
            // Prepara i dati
            $s1 = $db->quote($squadre1[$i]);
            $s2 = $db->quote($squadre2[$i]);
            $g1 = is_numeric($gol1[$i]) ? $db->quote($gol1[$i]) : 0;
            $g2 = is_numeric($gol2[$i]) ? $db->quote($gol2[$i]) : 0;

            // Costruisci la query di aggiornamento
            $query = $db->getQuery(true)
                ->update($db->quoteName($tablePartite))
                ->set([
                    $db->quoteName('gol1') . ' = ' . $g1,
                    $db->quoteName('gol2') . ' = ' . $g2
                ])
                ->where([
                    $db->quoteName('squadra1') . ' = ' . $s1,
                    $db->quoteName('squadra2') . ' = ' . $s2,
                    $db->quoteName('giornata') . ' = ' . (int) $giornata // filtro per la giornata, se necessario
                ]);

            // Esegui la query
            $db->setQuery($query);
            $db->execute();
        }
        $gio = $giornata+1;
    }
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$gio");
    exit;
} elseif (isset($_POST['deleteall'])) {
    $giornata = $_POST['giornata'];
    $module_ID = $_POST['module_id'];

    // Ottieni il database
    $db = Factory::getDbo();

    // Prepara la query per impostare a 0 i gol della giornata specificata
    $query = $db->getQuery(true)
        ->update($db->quoteName($tablePartite))
        ->set([
            'gol1 = NULL',
            'gol2 = NULL'
        ])
        ->where($db->quoteName('giornata') . ' = ' . $db->quote($giornata));

    // Esegui la query
    $db->setQuery($query);
    $db->execute();

    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=$module_ID#$giornata");
    exit;
}

?>