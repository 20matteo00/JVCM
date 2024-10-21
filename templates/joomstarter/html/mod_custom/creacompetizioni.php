<?php
// Protezione contro accesso diretto
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

$categoryId = 67;  // ID della categoria principale
$db = Factory::getDbo();

// Query per ottenere tutte le sottocategorie della categoria 67
$query = $db->getQuery(true)
    ->select($db->quoteName(['id', 'title', 'alias']))
    ->from($db->quoteName('#__categories'))
    ->where($db->quoteName('parent_id') . ' = ' . (int) $categoryId)
    ->where($db->quoteName('published') . ' = 1');

$db->setQuery($query);
$subcategories = $db->loadObjectList();

if (!empty($subcategories)) : ?>
    <div class="container mt-5 w-25 text-center">
        <div class="row">
            <?php foreach ($subcategories as $subcategory) : ?>
                <div class="col-12 mb-2">
                    <div class="card creacomp">
                        <div class="card-body text-center ">
                            <a href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $subcategory->id); ?>">
                                <?php echo htmlspecialchars($subcategory->title); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>