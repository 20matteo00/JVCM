<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;
// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;
?>
<div style=" margin-bottom:100px;">
    <?php
    // Verifica se l'ID è presente nei parametri GET
    if (isset($_GET['id'])) {
        // Ottieni l'ID della competizione in modo sicuro, convertendolo in un intero
        $idcomp = (int) $_GET['id'];

        // Recupera la competizione utilizzando la funzione
        $competizione = Competizione::getCompetizioneById($idcomp, $userId);
        $mod = $competizione->modalita;
        $ar = $competizione->andata_ritorno;
        $finita = $competizione->finita;
        $squadreJson = $competizione->squadre;
        // Decodifica la stringa JSON in un array
        $squadre = json_decode($squadreJson, true);
        $numsquadre = count($squadre);
        // Controlla se la competizione è stata trovata
        if ($competizione) {
            $nomemodalita = Competizione::getCategoryNameById($competizione->modalita);
            Competizione::CreaTabelleCompetizione($idcomp, $squadre);
            $tablePartite = Competizione::getTablePartite($idcomp);
            $tableStatistiche = Competizione::getTableStatistiche($idcomp);
            if ($mod == 68) {
                Competizione::GeneraCampionato($squadre, $tablePartite, $ar);
            } elseif ($mod == 69) {
                Competizione::GeneraEliminazione($squadre, $tablePartite, $ar);
            } elseif ($mod == 70) {
                Competizione::GeneraChampions($squadre, $tablePartite, $ar);
            }
            Competizione::GeneraStatistiche($squadre, $tableStatistiche, $tablePartite);
            // Visualizza i dettagli della competizione
            echo '<h1 class="text-center fw-bold h1 mb-5">' . htmlspecialchars($competizione->nome_competizione) . '</h1>';
            ?>
            <form method="post" action="">
                <div class="d-flex justify-content-between p-2 fixed-buttons">
                    <button type="submit" name="module_id" value="116" class="btn btn-success mx-3">Calendario</button>
                    <button type="submit" name="module_id" value="117" class="btn btn-success mx-3">Classifica</button>
                    <button type="submit" name="module_id" value="118" class="btn btn-success mx-3">Tabellone</button>
                    <button type="submit" name="module_id" value="119" class="btn btn-success mx-3">Statistiche</button>
                </div>
            </form>

            <?php
            // Verifica se è stato inviato un ID modulo
            if (isset($_POST['module_id'])) {
                $modulo = $_POST['module_id'];
                $module = ModuleHelper::getModuleById($modulo);
                echo ModuleHelper::renderModule($module);
            } elseif (isset($_GET['module_id'])) {
                $modulo = $_GET['module_id'];
                $module = ModuleHelper::getModuleById($modulo);
                echo ModuleHelper::renderModule($module);
            }
            ?>

            <?php
            $numpartitevalide = Competizione::getNumeroPartite($tablePartite);
            $totpart = 0;
            if ($ar === 0) {
                $totgior = $numsquadre - 1;
                $totpart = $totgior * ($numsquadre / 2);
            } elseif ($ar === 1) {
                $totgior = ($numsquadre - 1) * 2;
                $totpart = $totgior * ($numsquadre / 2);
            }
            if ($numpartitevalide === $totpart && $finita === 0) {
                ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center" role="alert">
                    <span><?php echo text::_('JOOM_COMPLIMENTI') ?></span>
                    <form action="" method="post">
                        <button class="btn btn-warning" name="closecomp">Chiudi Competizione</button>
                    </form>
                </div>
                <?php
            } elseif ($finita === 1) {
                ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center" role="alert">
                    <span><?php echo text::_('JOOM_RIAPRI') ?></span>
                    <form action="" method="post">
                        <button class="btn btn-warning" name="opencomp">Riapri Competizione</button>
                    </form>
                </div>
                <?php
            }
            ?>
            <?php
        }
        if (isset($_POST['closecomp'])) {
            Competizione::setCompetizioneFinita($idcomp);
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=116");
            exit;
        } elseif (isset($_POST['opencomp'])) {
            Competizione::setCompetizionenonFinita($idcomp);
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$idcomp&module_id=116");
            exit;
        }
    }
    ?>
</div>