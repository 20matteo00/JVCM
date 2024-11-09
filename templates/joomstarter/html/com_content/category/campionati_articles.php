<?php
defined('_JEXEC') or die;
require_once JPATH_SITE . '/templates/joomstarter/helper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomstarter\Helpers\Competizione;

$categoryId = $this->category->id; // ID della categoria corrente
$articles = Competizione::getArticlesFromCategory($categoryId);
$categoryTitle = Competizione::getCategoryTitleById($categoryId); // Carica solo il valore del titolo
$modificasquadra = Competizione::getUrlMenu(112);
// Verifica se il titolo è stato recuperato correttamente
if ($categoryTitle) {
    echo "<p class='text-center m-0 h1 fw-bold'>" . $categoryTitle . "</p>"; // Stampa il titolo della categoria
}
// Controllo se ci sono articoli
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
                        <!-- Colonna dell'immagine dell'articolo -->
                        <td class="category-image-cell">
                            <?php
                            // Ottieni l'immagine dell'articolo
                            $images = json_decode($article->images);
                            $imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : 'https://via.placeholder.com/80';
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>"
                                alt="<?php echo htmlspecialchars($article->title); ?>" class="category-image">
                        </td>

                        <!-- Colonna del titolo dell'articolo con colori dagli extra fields -->
                        <td class="category-title-cell">
                            <div class="squadra" style="background-color:<?php echo htmlspecialchars($article->color1); ?>;">
                                <a href="<?php echo Route::_("index.php?option=com_content&view=article&id={$article->id}&catid={$article->catid}"); ?>"
                                    class="category-title w-100 d-block"
                                    style="color:<?php echo htmlspecialchars($article->color2); ?>;">
                                    <?php echo htmlspecialchars($article->title); ?>
                                </a>
                            </div>
                        </td>

                        <!-- Colonna della forza (extra field "Forza") -->
                        <td class="category-items-cell">
                            <?php echo htmlspecialchars($article->forza); ?>
                        </td>

                        <td class="category-items-cell">
                            <form action="/jvcm/index.php/modifica-squadra" method="get">
                                <input type="hidden" value="<?php echo $article->id; ?>" name="id">
                                <button type="submit" class="btn btn-warning btn-sm" name="modifica" value="modifica">Modifica</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


<?php
if (isset($_POST['modifica'])) {
    echo "ciao";
}
?>