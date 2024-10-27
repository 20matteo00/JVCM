<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;


if (isset($_GET['id'])) {
    $idcomp = $_GET['id'];
    // Recupera la competizione utilizzando la funzione
    $competizione = Competizione::getCompetizioneById($idcomp);
    $squadreJson = $competizione->squadre; // Assicurati che questo sia una stringa JSON

    // Decodifica la stringa JSON in un array
    $squadre = json_decode($squadreJson, true); // Il secondo parametro true restituisce un array associativo

    // Controlla se la decodifica è andata a buon fine
    if (json_last_error() === JSON_ERROR_NONE && is_array($squadre)) {
        foreach ($squadre as $squadra) {
            // Ottieni il nome della squadra usando l'ID
            $nomesquadra = Competizione::getArticleTitleById($squadra);
            $customFields = Competizione::getCustomFields($squadra);
            // Assegniamo i valori ai colori, alla forza e all'immagine
            $color1 = !empty($customFields[1]) ? $customFields[1]->value : '#000000'; // Colore di sfondo del titolo
            $color2 = !empty($customFields[2]) ? $customFields[2]->value : '#ffffff'; // Colore del testo
            $strength = !empty($customFields[3]) ? $customFields[3]->value : 'N/A'; // Forza di default

            if ($nomesquadra) {
                ?>
                <div style="background-color:<?php echo $color1; ?>">
                    <p style="color:<?php echo $color2; ?>"><?php echo htmlspecialchars($nomesquadra); ?></p>
                </div>
                <?php
            }
        }
    }
}
