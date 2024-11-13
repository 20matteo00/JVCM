<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomstarter\Helpers\Competizione;

// Creiamo un oggetto per l'articolo attuale
$app = Factory::getApplication();
$db = Factory::getDbo();
$user = Factory::getUser();
$userId = $user->id;
$id = (int) $this->item->id;

$customFields = Competizione::getCustomFields($id);

// Assegniamo i valori ai colori, alla forza e all'immagine
$color1 = !empty($customFields[1]) ? $customFields[1]->value : '#000000'; // Colore di sfondo del titolo
$color2 = !empty($customFields[2]) ? $customFields[2]->value : '#ffffff'; // Colore del testo
$strength = !empty($customFields[3]) ? $customFields[3]->value : 'N/A'; // Forza di default

$params = $this->item->params;

// ... (il tuo codice PHP esistente)
$stato = Competizione::getCategoriaTag($id);
// Ottieni l'immagine dell'articolo
$images = json_decode($this->item->images);
$imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : '';

// Rimuovi eventuali parametri dall'URL dell'immagine
$imageSrc = strtok($imageSrc, '#'); // Questo restituirà solo la parte prima di '#'

// Stampa l'immagine per il DOM
?>
<div class="com-content-article item-page<?php echo $this->pageclass_sfx; ?>">
    <meta itemprop="inLanguage"
        content="<?php echo ($this->item->language === '*') ? $app->get('language') : $this->item->language; ?>">

    <div class="row">
        <div class="col-md-8 my-3">
            <?php if ($this->params->get('show_title')): ?>
                <div class="com-content-article__header  text-center" style="background-color: <?php echo $color1; ?>;border-radius:50px;">
                    <h1 class="com-content-article__title" style="color: <?php echo $color2; ?>;">
                        <?php echo $this->escape($this->item->title); ?>
                    </h1>
                </div>
            <?php endif; ?>
            <div class="com-content-article__body" style="color: <?php echo $color2; ?>;">
                <?php echo $this->item->text; ?>
            </div>
            <div class="com-content-article__strength">
                <span class="h4 fw-bold">Valore: <?php echo $strength; ?>Mln €</span>
            </div>
            <br>

            <div class="com-content-article__metadata">
                <?php
                // Verifica se la categoria è presente
                if (!empty($this->item->catid)) {
                    $categories = '<a class="campionato" href="' . Route::_('index.php?option=com_content&view=category&id=' . $this->item->catid) . '">' . $this->escape($this->item->category_title) . '</a>';
                }
                echo '<span class="h4 fw-bold">Campionato: ' . $categories . '</span>';
                ?>
            </div>
            <br>

            <div class="com-content-article__metadata">
                <?php
                // Verifica se la categoria è presente
                if ($stato !== null) {
                    $tag = '<a class="campionato" href="' . htmlspecialchars($stato['link']) . '">' . htmlspecialchars($stato['title']) . '</a>';
                }
                echo '<span class="h4 fw-bold">Stato: ' . $tag . '</span>';
                ?>
            </div>
            <br>
            <span class="h4 fw-bold"><a class="campionato"
                    href="/jvcm/index.php/modifica-squadra?id=<?php echo $id; ?>&modifica=modifica">Modifica</a></span>
        </div>
        <div class="col-md-4  text-center my-3">
            <div class="com-content-article__image">
                <img id="articleImage" src="<?php echo htmlspecialchars($imageSrc); ?>"
                    alt="<?php echo htmlspecialchars($this->item->title); ?>">
            </div>
        </div>
    </div>

    <div class="row">
        
        <?php
        $c = Competizione::getAllCompetizioni($id, $userId);
        if ($c!=null) echo '<h2 class="text-center">Competizioni</h2>';
        for ($i = 0; $i < count($c); $i++) {
            $competizione = Competizione::getCompetizioneById($c[$i], $userId);
            echo "
            <div class='col-12 col-sm-6 col-md-4 col-lg-3 mb-4'>
                <div class='text-center p-3'>
                    <a style='border-radius:50px;' href='/jvcm/index.php/visualizza-competizione?id=" . $competizione->id . "' class='btn btn-outline-dark w-100'>
                        " . $competizione->nome_competizione . "
                    </a>
                </div>
            </div>
        ";
        }
        ?>
    </div>


    <div class="accordion my-5" id="archivioAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingarchivio">
                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsearchivio" aria-expanded="false" aria-controls="collapsearchivio">
                    Archivio
                </button>
            </h2>
            <div id="collapsearchivio" class="accordion-collapse collapse" aria-labelledby="headingarchivio"
                data-bs-parent="#archivioAccordion">
                <div class="accordion-body">
                    <?php
                    $c = Competizione::getAllCompetizioni($id, $userId);
                    echo '<div class="row text-center">';
                    for ($i = 0; $i < count($c); $i++) {
                        $tablePartite = Competizione::getTablePartite($c[$i]);
                        $partite = Competizione::getPartitePerSquadra($id, $tablePartite);

                        $competizione = Competizione::getCompetizioneById($c[$i], $userId);
                        echo '<div class="col-12 my-4">';
                        echo '<h3>' . htmlspecialchars($competizione->nome_competizione) . '</h3>';
                        echo '<table class="table table-striped table-bordered">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Giornata</th>';
                        echo '<th>Partita</th>';
                        echo '<th>Risultato</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';

                        if (!empty($partite)) {
                            foreach ($partite as $partita) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($partita->giornata) . '</td>';
                                echo '<td>' . Competizione::getArticleTitleById(htmlspecialchars($partita->squadra1)) . " - " . Competizione::getArticleTitleById(htmlspecialchars($partita->squadra2)) . '</td>';
                                if ($partita->gol1 !== null && $partita->gol2 !== null) {
                                    echo '<td>' . htmlspecialchars($partita->gol1) . " - " . htmlspecialchars($partita->gol2) . '</td>';
                                } else {
                                    echo '<td> - </td>';
                                }
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr>
                                <td colspan="3" class="text-center">Nessuna partita trovata</td>
                              </tr>';
                        }

                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo "</div>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion my-5" id="statisticheAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingstatistiche">
                <button class="accordion-button collapsed bg-dark text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsestatistiche" aria-expanded="false" aria-controls="collapsestatistiche">
                    Statistiche
                </button>
            </h2>
            <div id="collapsestatistiche" class="accordion-collapse collapse" aria-labelledby="headingstatistiche"
                data-bs-parent="#statisticheAccordion">
                <div class="accordion-body">
                    <?php
                    $c = Competizione::getAllCompetizioni($id, $userId);
                    $campWin = $elimWin = $countpartite = 0;
                    $vc = $nc = $pc = $gfc = $gsc = $vt = $nt = $pt = $gft = $gst = 0;
                    for ($i = 0; $i < count($c); $i++) {
                        $tableStatistiche = Competizione::getTableStatistiche($c[$i]);
                        $tablePartite = Competizione::getTablePartite($c[$i]);
                        $countpartite += count(Competizione::getPartitePerSquadra($id, $tablePartite));
                        $stats = Competizione::getStats($tableStatistiche, $id);
                        $vc += $stats[0]->VC;
                        $nc += $stats[0]->NC;
                        $pc += $stats[0]->PC;
                        $gfc += $stats[0]->GFC;
                        $gsc += $stats[0]->GSC;
                        $vt += $stats[0]->VT;
                        $nt += $stats[0]->NT;
                        $pt += $stats[0]->PT;
                        $gft += $stats[0]->GFT;
                        $gst += $stats[0]->GST;
                        $competizione = Competizione::getCompetizioneById($c[$i], $userId);
                        $mod = $competizione->modalita;
                        $winner = Competizione::checkWinner($tablePartite, $tableStatistiche, $id, $mod);
                        if ($mod === 68 && $winner)
                            $campWin++;
                        elseif ($mod === 69 && $winner)
                            $elimWin++;
                    }
                    $gc = $vc + $nc + $pc;
                    $gt = $vt + $nt + $pt;
                    $dc = $gfc - $gsc;
                    $dt = $gft - $gst;
                    $v = $vc + $vt;
                    $n = $nc + $nt;
                    $p = $pc + $pt;
                    $gf = $gfc + $gft;
                    $gs = $gsc + $gst;
                    $d = $dc + $dt;
                    if ($d > 0)
                        $d = "<span style='color:limegreen'>+" . $d . "</span>";
                    elseif ($d < 0)
                        $d = "<span style='color:crimson'>" . $d . "</span>";
                    if ($dc > 0)
                        $dc = "<span style='color:limegreen'>+" . $dc . "</span>";
                    elseif ($dc < 0)
                        $dc = "<span style='color:crimson'>" . $dc . "</span>";
                    if ($dt > 0)
                        $dt = "<span style='color:limegreen'>+" . $dt . "</span>";
                    elseif ($dt < 0)
                        $dt = "<span style='color:crimson'>" . $dt . "</span>";
                    if ($campWin > 0)
                        $campWin = "<span style='color:chartreuse'>" . $campWin . "</span>";
                    if ($elimWin > 0)
                        $elimWin = "<span style='color:chartreuse'>" . $elimWin . "</span>";
                    // Array associativo con le etichette e i valori
                    $record = [
                        'Campionati Vinti' => $campWin,
                        'Coppe Vinte' => $elimWin,
                        'Giocate Totali' => $countpartite,
                        'Vinte Totali' => "<span style='color:yellowgreen'>" . $v . "</span>",
                        'Pareggiate Totali' => "<span style='color:orange'>" . $n . "</span>",
                        'Perse Totali' => "<span style='color:orangered'>" . $p . "</span>",
                        'Gol Fatti Totali' => "<span style='color:green'>" . $gf . "</span>",
                        'Gol Subiti Totali' => "<span style='color:red'>" . $gs . "</span>",
                        'Differenza Reti Totale' => $d,
                        'Giocate Casa' => $gc,
                        'Vinte Casa' => "<span style='color:yellowgreen'>" . $vc . "</span>",
                        'Pareggiate Casa' => "<span style='color:orange'>" . $nc . "</span>",
                        'Perse Casa' => "<span style='color:orangered'>" . $pc . "</span>",
                        'Gol Fatti Casa' => "<span style='color:green'>" . $gfc . "</span>",
                        'Gol Subiti Casa' => "<span style='color:red'>" . $gsc . "</span>",
                        'Differenza Reti Casa' => $dc,
                        'Giocate Trasferta' => $gt,
                        'Vinte Trasferta' => "<span style='color:yellowgreen'>" . $vt . "</span>",
                        'Pareggiate Trasferta' => "<span style='color:orange'>" . $nt . "</span>",
                        'Perse Trasferta' => "<span style='color:orangered'>" . $pt . "</span>",
                        'Gol Fatti Trasferta' => "<span style='color:green'>" . $gft . "</span>",
                        'Gol Subiti Trasferta' => "<span style='color:red'>" . $gst . "</span>",
                        'Differenza Reti Trasferta' => $dt
                    ];
                    ?>

                    <table class="table table-striped table-bordered text-center">
                        <thead>
                            <tr>
                                <th colspan="2">Record</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($record as $label => $value): ?>
                                <tr>
                                    <td><?php echo $label; ?></td>
                                    <td class="fw-bold"><?php echo $value; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>


                </div>
            </div>
        </div>
    </div>
    <?php echo $this->item->event->afterDisplayContent; ?>
</div>