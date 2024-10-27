<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;

// Verifica se l'ID è presente nei parametri GET
if (isset($_GET['id'])) {
    // Ottieni l'ID della competizione in modo sicuro, convertendolo in un intero
    $idcomp = (int)$_GET['id'];
    
    // Recupera la competizione utilizzando la funzione
    $competizione = Competizione::getCompetizioneById($idcomp);
    
    // Controlla se la competizione è stata trovata
    if ($competizione) {
        $nomemodalita = Competizione::getCategoryNameById($competizione->modalita);
        
        // Visualizza i dettagli della competizione
        echo '<h1 class="text-center">' . htmlspecialchars($competizione->nome_competizione) . '</h1>';
        ?>
        
        <form method="post" action="">
            <div class="d-flex justify-content-between m-5">
                <button type="submit" name="module_id" value="116" class="btn btn-success">Calendario</button>
                <button type="submit" name="module_id" value="117" class="btn btn-success">Classifica</button>
                <button type="submit" name="module_id" value="118" class="btn btn-success">Tabellone</button>
                <button type="submit" name="module_id" value="119" class="btn btn-success">Statistiche</button>
            </div>
        </form>
        
        <?php
        // Verifica se è stato inviato un ID modulo
        if (isset($_POST['module_id'])) {
            $modulo = $_POST['module_id'];
            $module = ModuleHelper::getModuleById($modulo);
            echo ModuleHelper::renderModule($module);
        }
        ?>
        <?php
    }
}
?>
