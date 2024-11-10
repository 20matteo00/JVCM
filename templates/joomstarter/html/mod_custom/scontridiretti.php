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
if (isset($_POST['submit'])) {
    $squadra1 = (int) $_POST['squadra1'];
    $squadra2 = (int) $_POST['squadra2'];
    $scontriDiretti = Competizione::getScontriDiretti($squadra1, $squadra2, $userId);
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
                    <button type="submit" name="submit" class="btn btn-primary btn-lg">Mostra Scontri Diretti</button>
                </div>
            </div>
        </div>
    </form>


    <?php if (!empty($scontriDiretti)): ?>
        <h2 class="my-4">Risultati degli Scontri Diretti</h2>
        <table class="table table-striped table-bordered text-center">
            <thead>
                <tr>
                    <th>Competizione</th>
                    <th>Giornata</th>
                    <th>Partita</th>
                    <th>Risultato</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scontriDiretti as $scontro): ?>
                    <?php
                    $partita = $scontro['partita']; // Dettagli della partita
                    $competizioneId = $scontro['competizione']; // ID della competizione
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($competizioneId); ?></td>
                        <td><?php echo htmlspecialchars($partita->giornata); ?></td>
                        <td><?php echo htmlspecialchars(Competizione::getArticleTitleById($partita->squadra1)); ?> vs
                            <?php echo htmlspecialchars(Competizione::getArticleTitleById($partita->squadra2)); ?>
                        </td>
                        <td>
                            <?php if ($partita->gol1 !== null && $partita->gol2 !== null): ?>
                                <?php echo htmlspecialchars($partita->gol1); ?> - <?php echo htmlspecialchars($partita->gol2); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $partite = count($scontriDiretti);
        $gc1 = $gc2 = $vc1 = $vc2 = $nc1 = $nc2 = $pc1 = $pc2 = $gfc1 = $gfc2 = $gsc1 = $gsc2 = $dc1 = $dc2 = 0;
        $gt1 = $gt2 = $vt1 = $vt2 = $nt1 = $nt2 = $pt1 = $pt2 = $gft1 = $gft2 = $gst1 = $gst2 = $dt1 = $dt2 = 0;
        $g1 = $g2 = $v1 = $v2 = $n1 = $n2 = $p1 = $p2 = $gf1 = $gf2 = $gs1 = $gs2 = $d1 = $d2 = 0;
        foreach ($scontriDiretti as $scontro) {
            $partita = $scontro['partita']; // Dettagli della partita
    
            if ($partita->gol1 !== null && $partita->gol2 !== null) {
                // Se la squadra1 è la prima squadra della partita
                if ($squadra1 == $partita->squadra1 && $squadra2 == $partita->squadra2) {
                    if ($partita->gol1 > $partita->gol2) {
                        $vc1++; // Vittoria squadra 1
                        $pt2++; // Punti persi dalla squadra 2
                    } elseif ($partita->gol1 == $partita->gol2) {
                        $nc1++; // Pareggio squadra 1
                        $nt2++; // Pareggio squadra 2
                    } elseif ($partita->gol1 < $partita->gol2) {
                        $pc1++; // Partita persa squadra 1
                        $vt2++; // Vittoria squadra 2
                    }
                    // Aggiornamento statistiche per squadra1
                    $gfc1 += $partita->gol1; // Gol fatti squadra 1
                    $gsc1 += $partita->gol2; // Gol subiti squadra 1
                    $gft2 += $partita->gol2; // Gol fatti squadra 2
                    $gst2 += $partita->gol1; // Gol subiti squadra 2
                }

                // Se la squadra1 è la seconda squadra della partita
                elseif ($squadra1 == $partita->squadra2 && $squadra2 == $partita->squadra1) {
                    if ($partita->gol1 > $partita->gol2) {
                        $vc2++; // Vittoria squadra 1
                        $pt1++; // Punti persi dalla squadra 2
                    } elseif ($partita->gol1 == $partita->gol2) {
                        $nc2++; // Pareggio squadra 1
                        $nt1++; // Pareggio squadra 2
                    } elseif ($partita->gol1 < $partita->gol2) {
                        $pc2++; // Partita persa squadra 1
                        $vt1++; // Vittoria squadra 2
                    }
                    // Aggiornamento statistiche per squadra1
                    $gfc2 += $partita->gol1; // Gol fatti squadra 1
                    $gsc2 += $partita->gol2; // Gol subiti squadra 1
                    $gft1 += $partita->gol2; // Gol fatti squadra 2
                    $gst1 += $partita->gol1; // Gol subiti squadra 2
                }

                $gc1 = $vc1 + $nc1 + $pc1;
                $gt1 = $vt1 + $nt1 + $pt1;
                $gc2 = $vc2 + $nc2 + $pc2;
                $gt2 = $vt2 + $nt2 + $pt2;
                $dc1 = $gfc1 - $gsc1;
                $dt1 = $gft1 - $gst1;
                $dc2 = $gfc2 - $gsc2;
                $dt2 = $gft2 - $gst2;

                $g1 = $gc1 + $gt1;
                $v1 = $vc1 + $vt1;
                $n1 = $nc1 + $nt1;
                $p1 = $pc1 + $pt1;
                $gf1 = $gfc1 + $gft1;
                $gs1 = $gsc1 + $gst1;
                $d1 = $dc1 + $dt1;

                $g2 = $gc2 + $gt2;
                $v2 = $vc2 + $vt2;
                $n2 = $nc2 + $nt2;
                $p2 = $pc2 + $pt2;
                $gf2 = $gfc2 + $gft2;
                $gs2 = $gsc2 + $gst2;
                $d2 = $dc2 + $dt2;
            }
        }
        // A questo punto avrai aggiornato tutte le statistiche relative alle due squadre ($squadra1 e $squadra2)            
        ?>
        <div class="row g-4">
            <!-- Card per la prima squadra -->
            <div class="col-lg-6 col-12">
                <div class="card shadow-sm border-light rounded">
                    <div class="card-header text-center bg-success text-white">
                        <h5 class="m-0"><?php echo Competizione::getArticleTitleById($squadra1); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12 ">
                                <h4 class="text-muted">Totale</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <strong>Giocate:</strong> <span><?php echo $g1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Vinte:</strong> <span><?php echo $v1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Pari:</strong> <span><?php echo $n1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Perse:</strong> <span><?php echo $p1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Fatti:</strong> <span><?php echo $gf1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Subiti:</strong> <span><?php echo $gs1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $d1; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 col-12 ">
                                <h4 class="text-muted">Casa</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <strong>Giocate:</strong> <span><?php echo $gc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Vinte:</strong> <span><?php echo $vc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Pari:</strong> <span><?php echo $nc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Perse:</strong> <span><?php echo $pc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Fatti:</strong> <span><?php echo $gfc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Subiti:</strong> <span><?php echo $gsc1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $dc1; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 col-12 ">
                                <h4 class="text-muted">Trasferta</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between"><strong>Giocate:</strong>
                                        <span><?php echo $gt1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Vinte:</strong>
                                        <span><?php echo $vt1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Pari:</strong>
                                        <span><?php echo $nt1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Perse:</strong>
                                        <span><?php echo $pt1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Fatti:</strong>
                                        <span><?php echo $gft1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Subiti:</strong>
                                        <span><?php echo $gst1; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $dt1; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card per la seconda squadra -->
            <div class="col-lg-6 col-12">
                <div class="card shadow-sm border-light rounded">
                    <div class="card-header text-center bg-success text-white">
                        <h5 class="m-0"><?php echo Competizione::getArticleTitleById($squadra2); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-12 ">
                                <h4 class="text-muted">Totale</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <strong>Giocate:</strong> <span><?php echo $g2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Vinte:</strong> <span><?php echo $v2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Pari:</strong> <span><?php echo $n2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Perse:</strong> <span><?php echo $p2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Fatti:</strong> <span><?php echo $gf2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Gol Subiti:</strong> <span><?php echo $gs2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $d2; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 col-12 ">
                                <h4 class="text-muted">Casa</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between"><strong>Giocate:</strong>
                                        <span><?php echo $gc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Vinte:</strong>
                                        <span><?php echo $vc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Pari:</strong>
                                        <span><?php echo $nc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Perse:</strong>
                                        <span><?php echo $pc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Fatti:</strong>
                                        <span><?php echo $gfc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Subiti:</strong>
                                        <span><?php echo $gsc2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $dc2; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4 col-12 ">
                                <h4 class="text-muted">Trasferta</h4>
                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between"><strong>Giocate:</strong>
                                        <span><?php echo $gt2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Vinte:</strong>
                                        <span><?php echo $vt2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Pari:</strong>
                                        <span><?php echo $nt2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Perse:</strong>
                                        <span><?php echo $pt2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Fatti:</strong>
                                        <span><?php echo $gft2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between"><strong>Gol Subiti:</strong>
                                        <span><?php echo $gst2; ?></span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <strong>Differenza Reti:</strong> <span><?php echo $dt2; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <p class="text-center">Nessun incontro trovato tra le due squadre.</p>
    <?php endif; ?>
</div>