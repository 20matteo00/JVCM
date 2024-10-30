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

    $checkgol = Competizione::checkGolNull($tablePartite);

    // Ottieni la classifica
    $classifica = Competizione::getClassifica($tableStatistiche);
    $numsquadre = count($classifica);

    // Determina la vista
    $view = isset($_POST['Casa']) ? 'casa' :
        (isset($_POST['Trasferta']) ? 'trasferta' :
            (isset($_POST['Andata']) ? 'andata' :
                (isset($_POST['Ritorno']) ? 'ritorno' :
                    (isset($_POST['Andamento']) ? 'andamento' : 'totale'))));

    if ($view === 'andata' && $ar === 1) {
        $classifica = Competizione::getClassificaAR($tablePartite, $ar, $numsquadre, $view);
    } elseif ($view === 'ritorno' && $ar === 1) {
        $classifica = Competizione::getClassificaAR($tablePartite, $ar, $numsquadre, $view);
    } elseif ($view === 'andamento') {
        $classifica = NULL;
        $andamento = Competizione::getAndamento($tablePartite);
    }

    if (!empty($classifica) && !$checkgol): ?>
        <div class="table-responsive my-5">
            <table class="table table-striped table-bordered text-center category-table">
                <thead class="thead-dark">
                    <tr>
                        <td class="fw-bold" colspan="2">Rank</td>
                        <td class="fw-bold" colspan="8"><?php echo ucfirst($view); ?></td>
                    </tr>
                    <tr>
                        <th class="category-header-logo">#</th>
                        <th class="category-header-logo">Squadra</th>
                        <th class="category-header-logo">Pt</th>
                        <th class="category-header-logo">G</th>
                        <th class="category-header-logo">V</th>
                        <th class="category-header-logo">N</th>
                        <th class="category-header-logo">P</th>
                        <th class="category-header-logo">GF</th>
                        <th class="category-header-logo">GS</th>
                        <th class="category-header-logo">DR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $posizione = 1;
                    foreach ($classifica as $squadra):
                        // Calcola le statistiche
                        $stats = Competizione::calculateStatistics($squadra, $view, $ar);
                        ?>
                        <tr>
                            <td class="category-items-cell"><?php echo $posizione++; ?></td>
                            <td class="category-items-cell"><?php
                            if (isset($squadra->squadra)) {
                                echo htmlspecialchars(Competizione::getArticleTitleById($squadra->squadra));
                            } else {
                                echo htmlspecialchars(Competizione::getArticleTitleById($stats['squadra']));
                            }
                            ?>
                            </td>
                            <td class="category-items-cell"><?php echo isset($stats['punti']) ? $stats['punti'] : 0; ?></td>
                            <td class="category-items-cell"><?php echo isset($stats['giocate']) ? $stats['giocate'] : 0; ?></td>
                            <td class="category-items-cell"><?php echo isset($stats['vinte']) ? $stats['vinte'] : 0; ?></td>
                            <td class="category-items-cell"><?php echo isset($stats['pari']) ? $stats['pari'] : 0; ?></td>
                            <td class="category-items-cell"><?php echo isset($stats['perse']) ? $stats['perse'] : 0; ?></td>
                            <td class="category-items-cell"><?php echo isset($stats['golFatti']) ? $stats['golFatti'] : 0; ?></td>
                            <td class="category-items-cell"><?php echo isset($stats['golSubiti']) ? $stats['golSubiti'] : 0; ?></td>
                            <td class="category-items-cell"><?php echo isset($stats['differenza']) ? $stats['differenza'] : 0; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif (!empty($andamento) && !$checkgol): ?>
        <div class="table-responsive my-5">
            <table class="table table-striped table-bordered text-center category-table">
                <thead class="thead-dark">
                    <tr>
                        <td class="fw-bold"><?php echo ucfirst($view); ?></td>
                        <td class="fw-bold" colspan="<?php echo Competizione::getGiornate($tablePartite) + 1; ?>">Giornate</td>
                    </tr>
                    <tr>
                        <th class="category-header-logo">Squadra</th>
                        <?php
                        // Trova il numero massimo di giornate
                        $maxGiornate = max(array_map(function ($squadra) {
                            return count($squadra['risultati']);
                        }, $andamento));

                        for ($giornata = 1; $giornata <= $maxGiornate; $giornata++): ?>
                            <th class="category-header-logo"><?php echo $giornata; ?>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($andamento as $squadra): ?>
                        <tr>
                            <td class="category-items-cell">
                                <?php echo htmlspecialchars(Competizione::getArticleTitleById($squadra['squadra'])); ?></td>
                            <?php for ($giornata = 1; $giornata <= $maxGiornate; $giornata++): ?>
                                <td class="category-items-cell">
                                    <?php echo isset($squadra['risultati'][$giornata]) ? $squadra['risultati'][$giornata] : ""; ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="d-flex justify-content-between p-2">
            <input type="hidden" name="module_id" value="117">
            <button type="submit" name="Totale" class="btn btn-info">Totale</button>
            <button type="submit" name="Andamento" class="btn btn-info">Andamento</button>
            <button type="submit" name="Casa" class="btn btn-info">Casa</button>
            <button type="submit" name="Trasferta" class="btn btn-info">Trasferta</button>
            <button type="submit" name="Andata" class="btn btn-info">Andata</button>
            <button type="submit" name="Ritorno" class="btn btn-info">Ritorno</button>
        </div>
    </form>
    <?php
}
?>