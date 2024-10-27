<?php

namespace Joomstarter\Helpers;

defined(constant_name: '_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

abstract class Competizione
{
    // Funzione per ottenere il titolo dell'articolo
    public static function getArticleTitleById($articleId)
    {
        $db = Factory::getDbo();
        return $db->setQuery("SELECT title FROM #__content WHERE id = " . (int)$articleId)->loadResult() ?: '';
    }
    // Funzione per ottenere l'URL dell'articolo
    public static function getArticleUrlById($articleId)
    {
        $db = Factory::getDbo();
        $article = $db->setQuery("SELECT id, alias, catid FROM #__content WHERE id = " . (int)$articleId)->loadObject();

        return $article ? Route::_('index.php?option=com_content&view=article&id=' . (int)$articleId . '&catid=' . (int)$article->catid) : '';
    }
    // Funzione per recuperare gli articoli in base alle sottocategorie
    public static function getArticlesInSubcategories($subcategoryIds)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title, images, catid') // Aggiungi 'catid' qui
            ->from('#__content')
            ->where('catid IN (' . implode(',', array_map('intval', $subcategoryIds)) . ')')
            ->where('state = 1'); // Solo articoli pubblicati

        return $db->setQuery($query)->loadObjectList();
    }
    // Funzione per recuperare il titolo della categoria
    public static function getCategoryNameById($categoryId)
    {
        $db = Factory::getDbo();
        return $db->setQuery("SELECT title FROM #__categories WHERE id = " . (int)$categoryId)->loadResult() ?: '';
    }
    // Funzione per recuperare le sottocategorie di una data categoria per ricavare gli articoli
    public static function getSubcategories($categoryId, $asObject = false)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($asObject ? 'id, title' : 'id')
            ->from('#__categories')
            ->where('parent_id = ' . (int) $categoryId);

        return $asObject ? $db->setQuery($query)->loadObjectList() : $db->setQuery($query)->loadColumn();
    }
    // Funzione per recuperare i sottotag di un tag specifico
    public static function getSubTags($tagId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title')
            ->from('#__tags')
            ->where('parent_id = ' . (int) $tagId);

        return $db->setQuery($query)->loadObjectList();
    }
    // Funzione per recuperare il tag associato alla categoria dell'articolo
    public static function getCategoryTag($categoryId)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('t.id')
            ->from('#__tags AS t')
            ->join('INNER', '#__contentitem_tag_map AS m ON m.tag_id = t.id')
            ->where('m.type_alias = "com_content.category"')
            ->where('m.content_item_id = ' . (int) $categoryId)
            ->where('t.published = 1'); // Solo tag pubblicati

        return $db->setQuery($query)->loadResult();
    }
    // Funzione per inserire una competizione nella tabella
    public static function insertCompetizione($data)
    {
        $db = Factory::getDbo();
        $tableName = $db->getPrefix() . 'competizioni';

        // Prepara l'oggetto di inserimento
        $query = $db->getQuery(true);
        $columns = ['user_id', 'nome_competizione', 'modalita', 'gironi', 'andata_ritorno', 'partecipanti', 'fase_finale', 'finita', 'squadre'];
        $values = [
            (int)$data['user_id'], // Assicurati di impostare l'ID utente correttamente
            $db->quote($data['nome_competizione']),
            (int)$data['modalita'],
            (int)$data['gironi'],
            (int)$data['andata_ritorno'],
            (int)$data['partecipanti'],
            (int)$data['fase_finale'],
            (int)$data['finita'],
            $db->quote(json_encode($data['squadre'])) // Codifica l'array in JSON
        ];

        // Crea la query di inserimento
        $query
            ->insert($db->quoteName($tableName))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        // Esegui la query di inserimento
        $db->setQuery($query);
        $db->execute();
    }
}
