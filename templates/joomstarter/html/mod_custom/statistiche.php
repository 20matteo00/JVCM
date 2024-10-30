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

    // Determina la vista
    $view = isset($_POST['Individuali']) ? 'individuali' :
        (isset($_POST['Elenco']) ? 'elenco' : 'Generali');


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

        <?php if ($view === 'individuali' || $view === 'elenco'): ?>
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
                            <form action="#" method="post">
                                <input type="hidden" value="<?php echo $squadra; ?>">
                                <input type="hidden" name="module_id" value="119">
                                <button type="submit" class="btn w-100" name="squadra<?php echo $squadra;?>">
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
?>