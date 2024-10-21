<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$categoryId = 8; // ID della categoria principale

$db = Factory::getDbo();
$query = $db->getQuery(true);

// Query per ottenere gli articoli delle sottocategorie della categoria con ID 8
$query->select('a.id, a.title, a.images, a.catid, a.created, c.title as category_title, f1.value as color1, f2.value as color2, f3.value as number_value')
    ->from('#__content as a')
    ->join('INNER', '#__categories as c ON a.catid = c.id')
    ->join('LEFT', '#__fields_values AS f1 ON f1.item_id = a.id AND f1.field_id = 1') // Colore 1
    ->join('LEFT', '#__fields_values AS f2 ON f2.item_id = a.id AND f2.field_id = 2') // Colore 2
    ->join('LEFT', '#__fields_values AS f3 ON f3.item_id = a.id AND f3.field_id = 3') // Numero
    ->where('c.parent_id = ' . (int) $categoryId)
    ->order('c.id ASC, a.title ASC'); // Ordina prima per ID categoria e poi per titolo dell'articolo

$db->setQuery($query);
$articles = $db->loadObjectList();

// Controlla se ci sono articoli
if ($articles) :
?>
    <div class="table-responsive category-table-container">
        <table class="table table-striped category-table">
            <thead>
                <tr>
                    <th class="category-header-logo"><?php echo Text::_('LOGO'); ?></th>
                    <th class="category-header-title"><?php echo Text::_('SQUADRA'); ?></th>
                    <th class="category-header-force"><?php echo Text::_('FORZA'); ?></th>
                    <th class="category-header-participants"><?php echo Text::_('CAMPIONATO'); ?></th>
                </tr>
            </thead>
            <tbody class="allarticles">
                <?php foreach ($articles as $article) : ?>
                    <tr>
                        <td class="category-image-cell">
                            <?php
                            // Ottieni l'immagine dell'articolo
                            $images = json_decode($article->images);
                            $imageSrc = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : 'https://via.placeholder.com/150';
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($article->title); ?>" class="category-image">
                        </td>
                        <td class="category-title-cell">
                            <div class="squadra" style="background-color:<?php echo htmlspecialchars($article->color1); ?>;">
                                <a href="<?php echo Route::_("index.php?option=com_content&view=article&id={$article->id}&catid={$article->catid}"); ?>" class="category-title w-100 d-block" style="color:<?php echo htmlspecialchars($article->color2); ?>;">
                                    <?php echo htmlspecialchars($article->title); ?>
                                </a>
                            </div>
                        </td>
                        <td class="category-items-cell">
                            <?php echo htmlspecialchars($article->number_value); ?>
                        </td>
                        <td class="category-items-cell">
                            <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($article->catid)); ?>">
                                <?php echo htmlspecialchars($article->category_title); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <p><?php echo Text::_('No articles found'); ?></p>
<?php endif; ?>