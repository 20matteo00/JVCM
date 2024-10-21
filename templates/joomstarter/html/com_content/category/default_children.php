<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Factory;

// Creiamo un'istanza del database
$db = Factory::getDbo();

/** @var \Joomla\Component\Content\Site\View\Category\HtmlView $this */
$lang   = $this->getLanguage();
$user   = $this->getCurrentUser();
$groups = $user->getAuthorisedViewLevels();

?>

<div class="table-responsive category-table-container">
    <table class="table table-striped category-table">
        <thead>
            <tr>
                <th class="category-header-logo"><?php echo Text::_('LOGO'); ?></th>
                <th class="category-header-title"><?php echo Text::_('CAMPIONATO'); ?></th>
                <th class="category-header-participants"><?php echo Text::_('PARTECIPANTI'); ?></th>
                <th class="category-header-participants"><?php echo Text::_('STATO'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
                <?php if (in_array($child->access, $groups)) : ?>
                    <tr>
                        <td class="category-image-cell">
                            <?php if ($child->getParams()->get('image')) : ?>
                                <img src="<?php echo htmlspecialchars($child->getParams()->get('image')); ?>" alt="<?php echo $this->escape($child->title); ?>" class="category-image">
                            <?php endif; ?>
                        </td>
                        <td class="category-title-cell">
                            <a href="<?php echo Route::_(RouteHelper::getCategoryRoute($child->id, $child->language)); ?>" class="category-title h4">
                                <?php echo $this->escape($child->title); ?>
                            </a>
                        </td>
                        <td class="category-items-cell">
                            <span class="badge bg-info category-badge" title="<?php echo HTMLHelper::_('tooltipText', 'JOOM_NUM_ITEMS'); ?>">
                                <?php echo $child->getNumItems(true); ?>
                            </span>
                        </td>
                        <td class="category-items-cell">
                            <?php

                            // Assicurati di avere accesso all'oggetto della categoria
                            $categoryId = (int) $child->id;

                            // Creazione della query per ottenere i tag associati alla categoria
                            $query = $db->getQuery(true)
                                ->select('*') // Seleziona tutte le colonne dalla mappa dei contenuti e tag
                                ->from($db->quoteName('vcmdb_contentitem_tag_map'))
                                ->where($db->quoteName('content_item_id') . ' = ' . (int) $categoryId); // Confronta con content_item_id

                            // Esegui la query
                            $db->setQuery($query);
                            $tagMappings = $db->loadObjectList();

                            // Controlla se ci sono tag disponibili
                            if (!empty($tagMappings)) {
                                foreach ($tagMappings as $tagMapping) {
                                    // Ora recuperiamo il titolo del tag usando il tag_id
                                    $tagId = (int) $tagMapping->tag_id;

                                    // Creazione della query per ottenere il nome del tag
                                    $tagQuery = $db->getQuery(true)
                                        ->select($db->quoteName('t.title', 'tag_title'))
                                        ->from($db->quoteName('#__tags', 't'))
                                        ->where($db->quoteName('t.id') . ' = ' . $tagId);

                                    // Esegui la query per ottenere il titolo del tag
                                    $db->setQuery($tagQuery);
                                    $tag = $db->loadObject();

                                    // Controlla se il tag esiste
                                    if ($tag) {
                                        // Stampa il nome del tag come link cliccabile
                                        echo '<a href="' . Route::_('index.php?option=com_tags&view=tag&id=' . $tagId) . '" class="badge bg-warning category-badge">' . $this->escape($tag->tag_title) . '</a>';                                    }
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>