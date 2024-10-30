<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomstarter\Helpers\Competizione;

$user = Factory::getUser();
$userId = $user->id;

if (isset($_GET['id'])) {
    $idcomp = (int) $_GET['id'];
    $tableStatistiche = Competizione::getTableStatistiche($idcomp);
    $tablePartite = Competizione::getTablePartite($idcomp);
    $competizione = Competizione::getCompetizioneById($idcomp, $userId);
    $ar = $competizione->andata_ritorno;
    $squadre = json_decode($competizione->squadre, true); // Decodifica JSON in array
    $checkgol = Competizione::checkGolNull($tablePartite);

    // Ottieni la classifica
    $classifica = Competizione::getClassifica($tableStatistiche);
    $numsquadre = count($classifica);

    // Determina la vista in base al POST
    if (isset($_POST['Generali'])) {
        $view = 'Generali';
    } elseif (isset($_POST['Individuali'])) {
        $view = 'Individuali';
    } elseif (isset($_POST['Elenco'])) {
        $view = 'Elenco';
    } elseif (!isset($view)) {
        $view = 'Generali'; // Default view if none is set
    }

    echo $view;
    ?>
    <div class="container statistiche">
        <form method="post" action="">
            <div class="d-flex justify-content-around p-2">
                <input type="hidden" name="module_id" value="119">
                <button type="submit" name="Generali" class="btn btn-info">Generali</button>
                <button type="submit" name="Individuali" class="btn btn-info">Individuali</button>
                <button type="submit" name="Elenco" class="btn btn-info">Elenco Partite</button>
            </div>
        </form>

        <?php if ($view === 'Individuali' || $view === 'Elenco'): ?>
            <div class="text-center my-5">
                <div class="row">
                    <?php foreach ($squadre as $squadra): ?>
                        <?php
                        $cf = Competizione::getCustomFields($squadra);
                        // Retrieve color values with defaults
                        $color1 = !empty($cf[1]) && isset($cf[1]->value) ? $cf[1]->value : '#000000'; // Default to black
                        $color2 = !empty($cf[2]) && isset($cf[2]->value) ? $cf[2]->value : '#ffffff'; // Default to white
                        ?>
                        <div class="col-2 my-3">
                            <form action="" method="post">
                                <input type="hidden" name="squadra" value="<?php echo $squadra; ?>">
                                <input type="hidden" name="module_id" value="119">
                                <input type="hidden" name="<?php echo htmlspecialchars($view); ?>"
                                    value="<?php echo htmlspecialchars($view); ?>">
                                <button type="submit" class="btn w-100" name="submit">
                                    <div style="border-radius:50px; background-color:<?php echo $color1; ?>">
                                        <span class="fs-5" style="color:<?php echo $color2; ?>">
                                            <?php echo htmlspecialchars(Competizione::getArticleTitleById($squadra)); ?>
                                        </span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php
}

// Handle form submission to retain view
if (isset($_POST['submit'])) {
    $module_ID = $_POST['module_id'];
    $squadra = $_POST['squadra'];
    $vieww = $_POST[$view];
    if ($vieww === 'Individuali') {

    } elseif ($vieww === 'Elenco') {// Get the matches for the selected team
        $matches = Competizione::getPartitePerSquadra($squadra, $tablePartite);

        // Now you can loop through $matches and display them
        foreach ($matches as $match) {
            var_dump($match);
        }
    }
}
?>