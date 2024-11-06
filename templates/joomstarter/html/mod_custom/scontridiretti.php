<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper; // Aggiungi questa riga per utilizzare JModuleHelper
use Joomstarter\Helpers\Competizione;
// Ottieni l'ID dell'utente corrente
$user = Factory::getUser();
$userId = $user->id;
// Gestisci l'invio del form
$scontriDiretti = [];
if (isset($_POST['squadra1']) && isset($_POST['squadra2'])) {
    $squadra1 = (int) $_POST['squadra1'];
    $squadra2 = (int) $_POST['squadra2'];
    $scontriDiretti = Competizione::getScontriDiretti($squadra1, $squadra2);
}
$squadre = Competizione::getArticlesFromSubcategories(8);

?>

<div class="container my-5">
    <h1 class="text-center">Scontri Diretti tra Due Squadre</h1>

    <form method="POST" class="my-4">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Campo per la prima squadra -->
                <div class="col-md-6 col-12 mb-3">
                    <label for="squadra1" class="form-label fs-5 fw-bold">Squadra 1</label>
                    <select name="squadra1" id="squadra1" class="form-select form-select-lg">
                        <?php
                        foreach ($squadre as $squadra) {
                            echo '<option value="' . $squadra->id . '">' . htmlspecialchars($squadra->title) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Campo per la seconda squadra -->
                <div class="col-md-6 col-12 mb-3">
                    <label for="squadra2" class="form-label fs-5 fw-bold">Squadra 2</label>
                    <select name="squadra2" id="squadra2" class="form-select form-select-lg">
                        <?php
                        foreach ($squadre as $squadra) {
                            echo '<option value="' . $squadra->id . '">' . htmlspecialchars($squadra->title) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Bottone per invio -->
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Mostra Scontri Diretti</button>
                </div>
            </div>
        </div>
    </form>


    <?php if (!empty($scontriDiretti)): ?>
        <h2 class="my-4">Risultati degli Scontri Diretti</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Partita</th>
                    <th>Risultato</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scontriDiretti as $partita): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($partita->data_partita); ?></td>
                        <td><?php echo htmlspecialchars($partita->squadra1); ?> vs
                            <?php echo htmlspecialchars($partita->squadra2); ?>
                        </td>
                        <td><?php echo htmlspecialchars($partita->gol1); ?> - <?php echo htmlspecialchars($partita->gol2); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">Nessun incontro trovato tra le due squadre.</p>
    <?php endif; ?>
</div>