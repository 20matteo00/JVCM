<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

// Funzione per ottenere l'URL dell'articolo
function getArticleUrlById($articleId)
{
    $db = Factory::getDbo();
    $article = $db->setQuery("SELECT id, alias, catid FROM #__content WHERE id = " . (int)$articleId)->loadObject();

    if ($article) {
        $slug = $article->id . ':' . $article->alias;
        return Route::_('index.php?option=com_content&view=article&id=' . (int)$articleId . '&catid=' . (int)$article->catid);
    }

    return '';
}

function getCategoryNameById($categoryId)
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true);

    // Costruisci la query per selezionare il nome della categoria in base al suo ID
    $query->select($db->quoteName('title'))
        ->from($db->quoteName('#__categories'))
        ->where($db->quoteName('id') . ' = ' . (int) $categoryId);

    // Imposta ed esegui la query
    $db->setQuery($query);
    $categoryName = $db->loadResult();

    return $categoryName;
}


function getArticleTitleById($articleId)
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true);

    // Costruisci la query per selezionare il titolo dell'articolo in base al suo ID
    $query->select($db->quoteName('title'))
        ->from($db->quoteName('#__content'))
        ->where($db->quoteName('id') . ' = ' . (int) $articleId);

    // Imposta ed esegui la query
    $db->setQuery($query);
    $articleTitle = $db->loadResult();

    return $articleTitle;
}

$menu = Factory::getApplication()->getMenu();
$activeMenuItem = $menu->getActive();
$menuItemId = $activeMenuItem ? $activeMenuItem->id : null;

// Importa il database di Joomla
$db = Factory::getDbo();
$query = $db->getQuery(true);

// Costruisci la query per selezionare i dati dalla tabella delle competizioni
$query->select('*')
    ->from($db->quoteName('#__competizioni')); // Sostituisci con il nome della tua tabella

// Esegui la query
$db->setQuery($query);
$results = $db->loadObjectList();

$pagconsentite = [106, 107];

if (in_array($menuItemId, $pagconsentite)) {

    // Visualizza i risultati in un formato HTML
    if (!empty($results)) { ?>
        <h1 class="text-center fw-bold">Competizioni
            <?php if ($menuItemId == 106): ?>
                in Corso
            <?php elseif ($menuItemId == 107): ?>
                Finite
            <?php endif; ?>
        </h1>
        <div class="table-responsive category-table-container competizioni">
            <table class="table table-striped category-table" style="min-width:1200px;">
                <thead>
                    <tr>
                        <th class="category-header-title" style="min-width:200px;">Nome Competizione</th>
                        <th class="category-header-title" style="min-width:100px;">Modalità</th>
                        <th class="category-header-title" style="min-width:50px;">Gironi</th>
                        <th class="category-header-title" style="min-width:150px;">Andata/Ritorno</th>
                        <th class="category-header-title" style="min-width:100px;">Partecipanti</th>
                        <th class="category-header-title" style="min-width:100px;">Fase Finale</th>
                        <th class="category-header-title" style="min-width:300px;">Squadre</th>
                        <th class="category-header-title" style="min-width:200px;">Azioni</th>
                    </tr>
                </thead>
                <tbody class="allarticles">
                    <?php foreach ($results as $competizione): ?>
                        <?php
                        // Decodifica la stringa JSON o PHP serializzata
                        $squadre = json_decode($competizione->squadre);
                        $count = count($squadre);
                        $idcomp = $competizione->id;
                        ?>
                        <?php if ($menuItemId == 106 && $competizione->finita == 0): ?>
                            <tr>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->nome_competizione); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars(getCategoryNameById($competizione->modalita)); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->gironi); ?></td>
                                <td class="category-title-cell">
                                    <?php
                                    if ($competizione->andata_ritorno == 0) {
                                        echo "No";
                                    } elseif ($competizione->andata_ritorno == 1) {
                                        echo "Si";
                                    }
                                    ?>
                                </td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->partecipanti); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->fase_finale); ?></td>
                                <td class="category-title-cell">
                                    <div style="max-height: 200px; overflow-y: scroll;">
                                        <?php
                                        // Esempio di utilizzo
                                        foreach ($squadre as $index => $id) {
                                            // Eseguiamo la query per ottenere i campi personalizzati
                                            $query = $db->getQuery(true)
                                                ->select($db->quoteName(['field_id', 'value']))
                                                ->from($db->quoteName('#__fields_values'))
                                                ->where($db->quoteName('item_id') . ' = ' . (int)$id); // Assicurati di castare l'ID a intero

                                            $db->setQuery($query);
                                            $customFields = $db->loadObjectList('field_id'); // Carica i risultati in un array indicizzato per field_id
                                            // Assegniamo i valori ai colori, alla forza e all'immagine
                                            $color1 = !empty($customFields[1]) ? $customFields[1]->value : '#000000'; // Colore di sfondo del titolo
                                            $color2 = !empty($customFields[2]) ? $customFields[2]->value : '#ffffff'; // Colore del testo

                                            $articleTitle = htmlspecialchars(getArticleTitleById($id)); // Ottieni il titolo dell'articolo
                                            $articleUrl = getArticleUrlById($id); // Ottieni l'URL dell'articolo

                                            // Crea un link all'articolo
                                            echo '<div class="p-1 mx-2 my-1" style="background-color:' . $color1 . '; display: inline-block; border-radius:50px;">';
                                            echo '<a class="h5" style="color:' . $color2 . '" href="' . htmlspecialchars($articleUrl) . '">' . $articleTitle . '</a>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                </td>

                                <td class="category-title-cell">
                                    <form action="#" method="post">
                                        <input type="hidden" value="<?php echo $idcomp; ?>" name="id">
                                        <a href="url_dettaglio_articolo" class="btn btn-success btn-sm me-1" name="visualizza">Visualizza</a>
                                        <a href="url_elimina_articolo" class="btn btn-danger btn-sm" name="elimina">Elimina</a>
                                    </form>
                                </td>
                            </tr>
                        <?php elseif ($menuItemId == 107 && $competizione->finita == 1): ?>
                            <tr>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->nome_competizione); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars(getCategoryNameById($competizione->modalita)); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->gironi); ?></td>
                                <td class="category-title-cell">
                                    <?php
                                    if ($competizione->andata_ritorno == 0) {
                                        echo "No";
                                    } elseif ($competizione->andata_ritorno == 1) {
                                        echo "Si";
                                    }
                                    ?>
                                </td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->partecipanti); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->fase_finale); ?></td>
                                <td class="category-title-cell">
                                    <div style="max-height: 200px; overflow-y: scroll;">
                                        <?php
                                        // Esempio di utilizzo
                                        foreach ($squadre as $index => $id) {
                                            // Eseguiamo la query per ottenere i campi personalizzati
                                            $query = $db->getQuery(true)
                                                ->select($db->quoteName(['field_id', 'value']))
                                                ->from($db->quoteName('#__fields_values'))
                                                ->where($db->quoteName('item_id') . ' = ' . (int)$id); // Assicurati di castare l'ID a intero

                                            $db->setQuery($query);
                                            $customFields = $db->loadObjectList('field_id'); // Carica i risultati in un array indicizzato per field_id
                                            // Assegniamo i valori ai colori, alla forza e all'immagine
                                            $color1 = !empty($customFields[1]) ? $customFields[1]->value : '#000000'; // Colore di sfondo del titolo
                                            $color2 = !empty($customFields[2]) ? $customFields[2]->value : '#ffffff'; // Colore del testo

                                            $articleTitle = htmlspecialchars(getArticleTitleById($id)); // Ottieni il titolo dell'articolo
                                            $articleUrl = getArticleUrlById($id); // Ottieni l'URL dell'articolo

                                            // Crea un link all'articolo
                                            echo '<div class="p-1 mx-2 my-1" style="background-color:' . $color1 . '; display: inline-block; border-radius:50px;">';
                                            echo '<a class="h5" style="color:' . $color2 . '" href="' . htmlspecialchars($articleUrl) . '">' . $articleTitle . '</a>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </div>
                                </td>

                                <td class="category-title-cell">
                                    <form action="#" method="post">
                                        <input type="hidden" value="<?php echo $idcomp; ?>" name="id">
                                        <a href="url_dettaglio_articolo" class="btn btn-success btn-sm me-1" name="visualizza">Visualizza</a>
                                        <a href="url_elimina_articolo" class="btn btn-danger btn-sm" name="elimina">Elimina</a>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php
    }
}
