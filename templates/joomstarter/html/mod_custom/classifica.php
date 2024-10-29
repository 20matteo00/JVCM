<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;

if (isset($_GET['id'])) {
    $idcomp = (int) $_GET['id'];
    $tableStatistiche = Competizione::getTableStatistiche($idcomp);

    // Ottieni la classifica
    $classifica = Competizione::getClassifica($tableStatistiche);

    if (!empty($classifica)): ?>
        <div class="table-responsive" style="min-width:600px;">

            <table class="table table-striped table-bordered text-center category-table m-0" style="min-width:600px;">
                <thead class="thead-dark">
                    <tr>
                        <td class="fw-bold" colspan="2">Rank</td>
                        <td class="fw-bold" colspan="8">Totale</td>
                        <td class="fw-bold" colspan="8">Casa</td>
                        <td class="fw-bold" colspan="8">Trasferta</td>
                    </tr>
                    <tr>
                        <th class="category-header-force">#</th>
                        <th class="category-header-force">Squadra</th>
                        <th class="category-header-force">Pt</th>
                        <th class="category-header-force">G</th>
                        <th class="category-header-force">V</th>
                        <th class="category-header-force">N</th>
                        <th class="category-header-force">P</th>
                        <th class="category-header-force">GF</th>
                        <th class="category-header-force">GS</th>
                        <th class="category-header-force">DR</th>
                        <th class="category-header-force">Pt</th>
                        <th class="category-header-force">G</th>
                        <th class="category-header-force">V</th>
                        <th class="category-header-force">N</th>
                        <th class="category-header-force">P</th>
                        <th class="category-header-force">GF</th>
                        <th class="category-header-force">GS</th>
                        <th class="category-header-force">DR</th>
                        <th class="category-header-force">Pt</th>
                        <th class="category-header-force">G</th>
                        <th class="category-header-force">V</th>
                        <th class="category-header-force">N</th>
                        <th class="category-header-force">P</th>
                        <th class="category-header-force">GF</th>
                        <th class="category-header-force">GS</th>
                        <th class="category-header-force">DR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $posizione = 1; // Inizia la posizione da 1
                    foreach ($classifica as $squadra):
                        $punti = (($squadra->VC + $squadra->VT) * 3) + ($squadra->NC + $squadra->NT); // Calcolo punti TOT
                        $puntiC = ($squadra->VC * 3) + $squadra->NC; // Calcolo punti Casa
                        $puntiT = ($squadra->VT * 3) + $squadra->NT; // Calcolo punti Trasferta
                        $giocate = $squadra->VC + $squadra->VT + $squadra->NC + $squadra->NT + $squadra->PC + $squadra->PT;
                        $giocateC = $squadra->VC + $squadra->NC  + $squadra->PC;
                        $giocateT = $squadra->VT + $squadra->NT  + $squadra->PT;
                        $vinte = $squadra->VC + $squadra->VT;
                        $pari = $squadra->NC + $squadra->NT;
                        $perse = $squadra->PC + $squadra->PT;
                        $golfatti = $squadra->GFC + $squadra->GFT;
                        $golsubiti = $squadra->GSC + $squadra->GST;
                        $diff = ($squadra->GFC + $squadra->GFT) - ($squadra->GSC + $squadra->GST);
                        $diffC = $squadra->GFC - $squadra->GSC;
                        $diffT = $squadra->GFT - $squadra->GST;
                    ?>
                        <tr>
                            <td class="category-items-cell"><?php echo $posizione++; ?></td>
                            <td class="category-items-cell"><?php echo htmlspecialchars(Competizione::getArticleTitleById($squadra->squadra)); ?></td>
                            <td class="category-items-cell"><?php echo $punti; ?></td>
                            <td class="category-items-cell"><?php echo $giocate; ?></td>
                            <td class="category-items-cell"><?php echo $vinte; ?></td>
                            <td class="category-items-cell"><?php echo $pari; ?></td>
                            <td class="category-items-cell"><?php echo $perse; ?></td>
                            <td class="category-items-cell"><?php echo $golfatti; ?></td>
                            <td class="category-items-cell"><?php echo $golsubiti; ?></td>
                            <td class="category-items-cell"><?php echo $diff; ?></td>
                            <td class="category-items-cell"><?php echo $puntiC; ?></td>
                            <td class="category-items-cell"><?php echo $giocateC; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->VC; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->NC; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->PC; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->GFC; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->GSC; ?></td>
                            <td class="category-items-cell"><?php echo $diffC; ?></td>
                            <td class="category-items-cell"><?php echo $puntiT; ?></td>
                            <td class="category-items-cell"><?php echo $giocateT; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->VT; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->NT; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->PT; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->GFT; ?></td>
                            <td class="category-items-cell"><?php echo $squadra->GST; ?></td>
                            <td class="category-items-cell"><?php echo $diffT; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php endif;
}
?>