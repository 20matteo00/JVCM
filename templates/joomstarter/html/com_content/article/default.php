<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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

// Creiamo un oggetto per l'articolo attuale
$app = Factory::getApplication();
$db = Factory::getDbo();
$user = $app->getIdentity();
$id = (int) $this->item->id;

// Eseguiamo la query per ottenere i campi personalizzati
$query = $db->getQuery(true)
    ->select($db->quoteName(['field_id', 'value']))
    ->from($db->quoteName('#__fields_values'))
    ->where($db->quoteName('item_id') . ' = ' . $db->quote($id));

$db->setQuery($query);
$customFields = $db->loadObjectList('field_id');

// Assegniamo i valori ai colori, alla forza e all'immagine
$color1 = !empty($customFields[1]) ? $customFields[1]->value : '#000000'; // Colore di sfondo del titolo
$color2 = !empty($customFields[2]) ? $customFields[2]->value : '#ffffff'; // Colore del testo
$strength = !empty($customFields[3]) ? $customFields[3]->value : 'N/A'; // Forza di default

$params  = $this->item->params;

// ... (il tuo codice PHP esistente)

// Ottieni l'immagine dell'articolo
$images = json_decode($this->item->images);
$imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : '';

// Rimuovi eventuali parametri dall'URL dell'immagine
$imageSrc = strtok($imageSrc, '#'); // Questo restituirà solo la parte prima di '#'

// Stampa l'immagine per il DOM
?>
<div class="com-content-article item-page<?php echo $this->pageclass_sfx; ?>">
    <meta itemprop="inLanguage" content="<?php echo ($this->item->language === '*') ? $app->get('language') : $this->item->language; ?>">

    <div class="row text-center">
        <div class="col-md-8">
            <?php if ($this->params->get('show_title')) : ?>
                <div class="com-content-article__header" style="background-color: <?php echo $color1; ?>;">
                    <h1 class="com-content-article__title" style="color: <?php echo $color2; ?>;"><?php echo $this->escape($this->item->title); ?></h1>
                </div>
            <?php endif; ?>
            <div class="com-content-article__body" style="color: <?php echo $color2; ?>;">
                <?php echo $this->item->text; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="com-content-article__image">
                <img id="articleImage" src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($this->item->title); ?>">
            </div>
        </div>
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

    <?php echo $this->item->event->afterDisplayContent; ?>
</div>

<!-- Sezione per i colori dominanti -->
<!-- <div class="dominant-colors" id="dominantColors">
    <h3>Colori Dominanti:</h3>
    <div id="colorBoxes"></div>
</div> -->