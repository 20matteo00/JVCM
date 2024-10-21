<?php

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Factory;

// Creiamo un'istanza del database
$db = Factory::getDbo();

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */
$lang = $this->getLanguage();
$user = $this->getCurrentUser();
$groups = $user->getAuthorisedViewLevels();

?>

<div class="table-responsive category-table-container">
    <div class="container mt-5 w-25 text-center">
        <div class="row">
            <?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
                <?php if (in_array($child->access, $groups)) : ?>
                    <div class="col-12 mb-2">
                        <div class="card creacomp">
                            <div class="card-body text-center ">
                                <a href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $child->id); ?>">
                                    <?php echo htmlspecialchars($child->title); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>