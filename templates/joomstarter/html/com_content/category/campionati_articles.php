<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomstarter\Helpers\Competizione;

// Ottieni l'ID dell'utente corrente e la categoria
$user = Factory::getUser();
$userId = $user->id;
$categoryId = $this->category->id;

// Definisci il numero di articoli per pagina
$articlesPerPage = 5;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

// Ottieni tutti gli articoli della categoria
$allArticles = Competizione::getArticlesFromCategory($categoryId);
$totalArticles = count($allArticles);
$totalPages = ceil($totalArticles / $articlesPerPage);

// Calcola l'indice di partenza e di fine per gli articoli della pagina corrente
$startIndex = ($page - 1) * $articlesPerPage;
$articles = array_slice($allArticles, $startIndex, $articlesPerPage);

// Ottieni il titolo della categoria e URL di modifica
$categoryTitle = Competizione::getCategoryTitleById($categoryId);
$modificasquadra = Competizione::getUrlMenu(112);

// Verifica se il titolo è stato recuperato correttamente
if ($categoryTitle) {
    echo "<p class='text-center m-0 h1 fw-bold'>" . $categoryTitle . "</p>";
}
?>

<?php if (!empty($articles)): ?>
    <div class="table-responsive category-table-container">
        <p class="text-center"></p>
        <table class="table table-striped category-table">
            <thead>
                <tr>
                    <th class="category-header-logo"><?php echo Text::_('LOGO'); ?></th>
                    <th class="category-header-title"><?php echo Text::_('SQUADRA'); ?></th>
                    <th class="category-header-force"><?php echo Text::_('FORZA'); ?></th>
                    <th class="category-header-logo"><?php echo Text::_('AZIONI'); ?></th>
                </tr>
            </thead>
            <tbody class="allarticles">
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td class="category-image-cell">
                            <?php
                            $images = json_decode($article->images);
                            $imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : 'https://via.placeholder.com/80';
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>"
                                alt="<?php echo htmlspecialchars($article->title); ?>" class="category-image">
                        </td>
                        <td class="category-title-cell">
                            <div class="squadra" style="background-color:<?php echo htmlspecialchars($article->color1); ?>;">
                                <a href="<?php echo Route::_("index.php?option=com_content&view=article&id={$article->id}&catid={$article->catid}"); ?>"
                                    class="category-title w-100 d-block"
                                    style="color:<?php echo htmlspecialchars($article->color2); ?>;">
                                    <?php echo htmlspecialchars($article->title); ?>
                                </a>
                            </div>
                        </td>
                        <td class="category-items-cell"><?php echo htmlspecialchars($article->forza); ?></td>
                        <td class="category-items-cell">
                            <form action="/jvcm/index.php/modifica-squadra" method="get">
                                <input type="hidden" value="<?php echo $article->id; ?>" name="id">
                                <button type="submit" class="btn btn-warning btn-sm" name="modifica"
                                    value="modifica">Modifica</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginazione -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <!-- Link alla prima pagina -->
                <li class="page-item <?php echo ($page == 1) ? 'disabled' : ''; ?>">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=1'); ?>">Primo</a>
                </li>

                <!-- Link alla pagina precedente -->
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=' . ($page - 1)); ?>"
                            aria-label="Precedente">
                            &laquo; Precedente
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">&laquo; Precedente</span></li>
                <?php endif; ?>

                <!-- Link pagine centrali -->
                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);

                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=' . $i); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Link alla pagina successiva -->
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link"
                            href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=' . ($page + 1)); ?>"
                            aria-label="Successiva">
                            Successiva &raquo;
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Successiva &raquo;</span></li>
                <?php endif; ?>

                <!-- Link all'ultima pagina -->
                <li class="page-item <?php echo ($page == $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link"
                        href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $categoryId . '&page=' . $totalPages); ?>">Ultimo</a>
                </li>
            </ul>
        </nav>

    <?php endif; ?>
<?php endif; ?>

<!-- Form per simulare il campionato -->
<form action="" method="post" class="text-center">
    <input type="hidden" value="<?php echo $categoryId; ?>" name="catid">
    <button type="submit" class="btn btn-success btn" name="simula_campionato">Simula Campionato</button>
</form>

<?php
// Gestione della simulazione del campionato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simula_campionato'])) {
    $catid = $_POST['catid'];
    $squadre = Competizione::getArticlesFromCategory($catid);
    $partecipanti = count($squadre);
    if ($partecipanti < 2)
        return;
    $squad = array_map(fn($squadra) => (string) $squadra->id, $squadre);
    $data = array(
        'user_id' => $userId,
        'nome_competizione' => $categoryTitle . " - Simulazione",
        'modalita' => 68,
        'gironi' => 0,
        'squadre' => $squad,
        'andata_ritorno' => 1,
        'partecipanti' => $partecipanti,
        'fase_finale' => 0,
        'finita' => 0,
    );
    Competizione::insertCompetizione($data);
    header("Location: /jvcm/index.php/competizioni-in-corso");
    exit;
}
?>