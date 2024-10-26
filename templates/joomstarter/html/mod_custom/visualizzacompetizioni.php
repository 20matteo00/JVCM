<?php
defined('_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

// Funzione per ottenere l'URL dell'articolo
function getArticleUrlById($articleId)
{
    $db = Factory::getDbo();
    $article = $db->setQuery("SELECT id, alias, catid FROM #__content WHERE id = " . (int)$articleId)->loadObject();

    return $article ? Route::_('index.php?option=com_content&view=article&id=' . (int)$articleId . '&catid=' . (int)$article->catid) : '';
}

function getCategoryNameById($categoryId)
{
    $db = Factory::getDbo();
    return $db->setQuery("SELECT title FROM #__categories WHERE id = " . (int)$categoryId)->loadResult() ?: '';
}

function getArticleTitleById($articleId)
{
    $db = Factory::getDbo();
    return $db->setQuery("SELECT title FROM #__content WHERE id = " . (int)$articleId)->loadResult() ?: '';
}

// Ottieni l'ID della voce di menu attiva
$menu = Factory::getApplication()->getMenu();
$activeMenuItem = $menu->getActive();
$menuItemId = $activeMenuItem ? $activeMenuItem->id : null;

// Importa il database di Joomla
$db = Factory::getDbo();

// Costruisci la query per selezionare i dati dalla tabella delle competizioni
$query = $db->getQuery(true)->select('*')->from($db->quoteName('#__competizioni'));
$db->setQuery($query);
$results = $db->loadObjectList();

$pagconsentite = [106, 107];

if (in_array($menuItemId, $pagconsentite)) {
    // Visualizza i risultati in un formato HTML
    if (!empty($results)) { ?>
        <h1 class="text-center fw-bold">Competizioni
            <?php echo ($menuItemId == 106) ? " in Corso" : " Finite"; ?>
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
                    <?php foreach ($results as $competizione):
                        // Decodifica la stringa JSON o PHP serializzata
                        $squadre = json_decode($competizione->squadre);
                        $idcomp = $competizione->id;

                        if (($menuItemId == 106 && $competizione->finita == 0) || ($menuItemId == 107 && $competizione->finita == 1)): ?>
                            <tr>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->nome_competizione); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars(getCategoryNameById($competizione->modalita)); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->gironi); ?></td>
                                <td class="category-title-cell"><?php echo ($competizione->andata_ritorno == 0) ? "No" : "Si"; ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->partecipanti); ?></td>
                                <td class="category-title-cell"><?php echo htmlspecialchars($competizione->fase_finale); ?></td>
                                <td class="category-title-cell">
                                    <div style="max-height: 200px; overflow-y: scroll;">
                                        <?php foreach ($squadre as $id):
                                            $customFields = $db->setQuery("SELECT field_id, value FROM #__fields_values WHERE item_id = " . (int)$id)->loadObjectList('field_id');
                                            $color1 = !empty($customFields[1]) ? $customFields[1]->value : '#000000';
                                            $color2 = !empty($customFields[2]) ? $customFields[2]->value : '#ffffff';
                                            $articleTitle = htmlspecialchars(getArticleTitleById($id));
                                            $articleUrl = getArticleUrlById($id); ?>
                                            <div class="p-1 mx-2 my-1" style="background-color:<?php echo $color1; ?>; display: inline-block; border-radius:50px;">
                                                <a class="h5 fw-bold" style="color:<?php echo $color2; ?>" href="<?php echo htmlspecialchars($articleUrl); ?>"><?php echo $articleTitle; ?></a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="category-title-cell">
                                    <form action="" method="post">
                                        <input type="hidden" value="<?php echo $idcomp; ?>" name="id">
                                        <button type="submit" class="btn btn-success btn-sm me-1" name="visualizza">Visualizza</button>
                                        <button type="submit" class="btn btn-danger btn-sm" name="elimina">Elimina</button>
                                    </form>
                                </td>
                            </tr>
                    <?php endif;
                    endforeach; ?>
                </tbody>
            </table>
        </div>
<?php } else {
        echo "<p class='h1'>Nessuna competizione presente.</p>";
    }
}

// Gestione della richiesta POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    if (isset($_POST['visualizza'])) {
        // Ottieni l'URL della voce di menu con ID 110
        $menuItem = $menu->getItem(110);
        if ($menuItem) {
            $url = Route::_('index.php?Itemid=' . (int) $menuItem->id . '&id=' . $id);
            // Reindirizza alla pagina
            header("Location: " . $url);
            exit; // Assicurati di uscire dopo il reindirizzamento
        }
    } elseif (isset($_POST['elimina'])) {
        // Importa il database di Joomla
        $db = Factory::getDbo();
        // Crea la query per eliminare la competizione
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__competizioni'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($id));

        $db->setQuery($query);

        try {
            $db->execute();
            // Ricarica la pagina
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
            exit;
        } catch (Exception $e) {
            // Gestione degli errori
            echo "Errore durante l'eliminazione: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>