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
        <div class="col-md-8">
            <?php if ($this->params->get('show_title')): ?>
                <div class="com-content-article__header  text-center" style="background-color: <?php echo $color1; ?>;">
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
        </div>
        <div class="col-md-4  text-center">
            <div class="com-content-article__image">
                <img id="articleImage" src="<?php echo htmlspecialchars($imageSrc); ?>"
                    alt="<?php echo htmlspecialchars($this->item->title); ?>">
            </div>
        </div>
    </div>

    <div class="accordion my-5" id="archivioAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingArchivio">
                <button class="accordion-button collapsed bg-primary text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseArchivio" aria-expanded="false" aria-controls="collapseArchivio">
                    Archivio
                </button>
            </h2>
            <div id="collapseArchivio" class="accordion-collapse collapse" aria-labelledby="headingArchivio"
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
                                } else{
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


    <?php echo $this->item->event->afterDisplayContent; ?>
</div>