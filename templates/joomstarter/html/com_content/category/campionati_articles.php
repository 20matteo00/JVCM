<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

// Ottieni l'ID della categoria corrente dalla vista
$categoryId = $this->category->id;  // Questo prende l'ID della categoria corrente dinamicamente

// Ottieni il database
$db = Factory::getDbo();
$query = $db->getQuery(true);

// Query per ottenere gli articoli della categoria e i valori degli extra fields
$query->select('a.id, a.title, a.images, a.catid, a.created, c.title as category_title, f1.value as color1, f2.value as color2, f3.value as forza')
    ->from('#__content as a')
    ->join('LEFT', '#__categories as c ON a.catid = c.id') // Aggiungi la join per il titolo della categoria
    ->join('LEFT', '#__fields_values AS f1 ON f1.item_id = a.id AND f1.field_id = 1') // Extra field Colore 1
    ->join('LEFT', '#__fields_values AS f2 ON f2.item_id = a.id AND f2.field_id = 2') // Extra field Colore 2
    ->join('LEFT', '#__fields_values AS f3 ON f3.item_id = a.id AND f3.field_id = 3') // Extra field Forza
    ->where('a.catid = ' . (int) $categoryId) // Filtro per la categoria corrente
    ->where('a.state = 1') // Solo articoli pubblicati
    ->order('a.title ASC');

$db->setQuery($query);
$articles = $db->loadObjectList();

// Creazione della query per ottenere il titolo della categoria
$query = $db->getQuery(true)
    ->select($db->quoteName('title')) // Seleziona solo il titolo
    ->from($db->quoteName('#__categories')) // Seleziona dalla tabella delle categorie
    ->where($db->quoteName('id') . ' = ' . (int) $categoryId); // Confronta con l'ID della categoria

// Esegui la query
$db->setQuery($query);
$categoryTitle = $db->loadResult(); // Carica solo il valore del titolo

// Verifica se il titolo è stato recuperato correttamente
if ($categoryTitle) {
    echo "<p class='text-center m-0 h1 fw-bold'>".$categoryTitle."</p>"; // Stampa il titolo della categoria
}
// Controllo se ci sono articoli
?>
<?php if (!empty($articles)) : ?>
    <div class="table-responsive category-table-container">
        <p class="text-center"></p>
        <table class="table table-striped category-table">
            <thead>
                <tr>
                    <th class="category-header-logo"><?php echo Text::_('LOGO'); ?></th>
                    <th class="category-header-title"><?php echo Text::_('SQUADRA'); ?></th>
                    <th class="category-header-force"><?php echo Text::_('FORZA'); ?></th>
                </tr>
            </thead>
            <tbody class="allarticles">
                <?php foreach ($articles as $article) : ?>
                    <tr>
                        <!-- Colonna dell'immagine dell'articolo -->
                        <td class="category-image-cell">
                            <?php
                            // Ottieni l'immagine dell'articolo
                            $images = json_decode($article->images);
                            $imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : 'https://via.placeholder.com/80';
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($article->title); ?>" class="category-image">
                        </td>

                        <!-- Colonna del titolo dell'articolo con colori dagli extra fields -->
                        <td class="category-title-cell">
                            <div class="squadra" style="background-color:<?php echo htmlspecialchars($article->color1); ?>;">
                                <a href="<?php echo Route::_("index.php?option=com_content&view=article&id={$article->id}&catid={$article->catid}"); ?>" class="category-title w-100 d-block" style="color:<?php echo htmlspecialchars($article->color2); ?>;">
                                    <?php echo htmlspecialchars($article->title); ?>
                                </a>
                            </div>
                        </td>

                        <!-- Colonna della forza (extra field "Forza") -->
                        <td class="category-items-cell">
                            <?php echo htmlspecialchars($article->forza); ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
