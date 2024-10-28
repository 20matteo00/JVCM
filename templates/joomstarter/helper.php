<?php

namespace Joomstarter\Helpers;

defined(constant_name: '_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

abstract class Competizione
{

    public static function getCustomFields($itemId)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Eseguiamo la query per ottenere i campi personalizzati
        $query->select($db->quoteName(['field_id', 'value']))
            ->from($db->quoteName('#__fields_values'))
            ->where($db->quoteName('item_id') . ' = ' . (int) $itemId); // Convertiamo in intero per sicurezza

        $db->setQuery($query);

        // Restituisci i campi personalizzati come array indicizzati per field_id
        return $db->loadObjectList('field_id');
    }
    public static function getArticlesFromSubcategories($categoryId)
    {
        // Ottieni l'oggetto del database
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Query per ottenere gli articoli delle sottocategorie della categoria specificata
        $query->select('a.id, a.title, a.images, a.catid, a.created, c.title as category_title, f1.value as color1, f2.value as color2, f3.value as number_value')
            ->from('#__content AS a')
            ->join('INNER', '#__categories AS c ON a.catid = c.id')
            ->join('LEFT', '#__fields_values AS f1 ON f1.item_id = a.id AND f1.field_id = 1') // Colore 1
            ->join('LEFT', '#__fields_values AS f2 ON f2.item_id = a.id AND f2.field_id = 2') // Colore 2
            ->join('LEFT', '#__fields_values AS f3 ON f3.item_id = a.id AND f3.field_id = 3') // Numero
            ->where('c.parent_id = ' . (int) $categoryId)
            ->order('c.id ASC, a.title ASC'); // Ordina prima per ID categoria e poi per titolo dell'articolo

        $db->setQuery($query);

        // Restituisci gli articoli come array di oggetti
        return $db->loadObjectList();
    }
    // Funzione per ottenere il titolo dell'articolo
    public static function getArticleTitleById($articleId)
    {
        $db = Factory::getDbo();
        return $db->setQuery("SELECT title FROM #__content WHERE id = " . (int) $articleId)->loadResult() ?: '';
    }
    // Funzione per ottenere l'URL dell'articolo
    public static function getArticleUrlById($articleId)
    {
        $db = Factory::getDbo();
        $article = $db->setQuery("SELECT id, alias, catid FROM #__content WHERE id = " . (int) $articleId)->loadObject();

        return $article ? Route::_('index.php?option=com_content&view=article&id=' . (int) $articleId . '&catid=' . (int) $article->catid) : '';
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
        return $db->setQuery("SELECT title FROM #__categories WHERE id = " . (int) $categoryId)->loadResult() ?: '';
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
    // Funzione per recuperare una competizione dal database in base all'ID della competizione e all'ID dell'utente
    public static function getCompetizioneById($idcomp, $userId)
    {
        // Connessione al database
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Costruisci la query per selezionare i dati della competizione basata sull'ID della competizione e dell'utente
        $query->select('*')
            ->from($db->quoteName('#__competizioni')) // Sostituisci con il nome corretto della tua tabella
            ->where($db->quoteName('id') . ' = ' . $db->quote($idcomp))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userId)); // Aggiungi il controllo dell'ID utente

        // Esegui la query
        $db->setQuery($query);

        // Recupera la competizione
        return $db->loadObject();
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
            (int) $data['user_id'], // Assicurati di impostare l'ID utente correttamente
            $db->quote($data['nome_competizione']),
            (int) $data['modalita'],
            (int) $data['gironi'],
            (int) $data['andata_ritorno'],
            (int) $data['partecipanti'],
            (int) $data['fase_finale'],
            (int) $data['finita'],
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

    public static function CreaTabelleCompetizione($idCompetizione)
    {
        $db = Factory::getDbo();
        $prefix = $db->getPrefix();
        $tablePartite = $prefix . 'competizione' . $idCompetizione . '_partite';
        $tableStatistiche = $prefix . 'competizione' . $idCompetizione . '_statistiche';

        // Creazione della tabella partite
        $query = "CREATE TABLE IF NOT EXISTS `$tablePartite` (
            `squadra1` INT NOT NULL,
            `squadra2` INT NOT NULL,
            `gol1` INT DEFAULT NULL,
            `gol2` INT DEFAULT NULL,
            `giornata` INT,
            `girone` INT DEFAULT 0,
            PRIMARY KEY (`squadra1`, `squadra2`)
        )";
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            echo 'Errore nella creazione della tabella partite: ' . $e->getMessage();
        }

        // Creazione della tabella statistiche
        $query = "CREATE TABLE IF NOT EXISTS `$tableStatistiche` (
            `squadra` INT NOT NULL,
            `VC` INT DEFAULT 0,
            `NC` INT DEFAULT 0,
            `PC` INT DEFAULT 0,
            `GFC` INT DEFAULT 0,
            `GSC` INT DEFAULT 0,
            `VT` INT DEFAULT 0,
            `NT` INT DEFAULT 0,
            `PT` INT DEFAULT 0,
            `GFT` INT DEFAULT 0,
            `GST` INT DEFAULT 0,
            `girone` INT DEFAULT 0,
            PRIMARY KEY (`squadra`)
        )";
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            echo 'Errore nella creazione della tabella statistiche: ' . $e->getMessage();
        }
        return $tablePartite;

    }

    public static function GeneraCampionato($squadre, $tablePartite)
    {
        $db = Factory::getDbo();

        // Verifica se la tabella è vuota
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tablePartite));

        $db->setQuery($query);
        $count = $db->loadResult();

        if ($count == 0) {
            // Procedi solo se ci sono già dati nella tabella
            $giornate = [];
            $numeroSquadre = count($squadre);

            if ($numeroSquadre == 0) {
                return; // O gestisci l'errore come preferisci
            }

            shuffle($squadre);

            if ($numeroSquadre % 2 != 0) {
                $squadre[] = 'Riposo';
                $numeroSquadre++;
            }

            for ($giornata = 0; $giornata < $numeroSquadre - 1; $giornata++) {
                $partite = [];
                for ($i = 0; $i < $numeroSquadre / 2; $i++) {
                    $squadraCasa = $squadre[$i];
                    $squadraTrasferta = $squadre[$numeroSquadre - 1 - $i];

                    if ($squadraTrasferta !== 'Riposo') {
                        $partite[] = [
                            'squadra1' => $squadraCasa,
                            'squadra2' => $squadraTrasferta,
                        ];
                    }
                }
                if (!empty($partite)) {
                    $giornate[] = $partite;
                }

                $squadre = array_merge(
                    [$squadre[0]],
                    array_slice($squadre, 2),
                    [$squadre[1]]
                );
            }

            $numeroSquadre = count($squadre);
            foreach ($giornate as $index => $partite) {
                foreach ($partite as $partita) {
                    // Inserisci la partita di andata nel DB
                    $inserimento = (object) [
                        'squadra1' => $partita['squadra1'],
                        'squadra2' => $partita['squadra2'],
                        'giornata' => $index + 1,
                    ];

                    // Esegui l'inserimento
                    try {
                        $db->insertObject($tablePartite, $inserimento);
                    } catch (Exception $e) {
                        echo 'Error inserting first match: ' . $e->getMessage();
                        // Puoi anche loggare l'errore o fare altre operazioni
                    }

                    // Inserisci la partita di ritorno
                    $inserimentoRitorno = (object) [
                        'squadra1' => $partita['squadra2'],
                        'squadra2' => $partita['squadra1'],
                        'giornata' => $numeroSquadre + $index,
                    ];

                    // Esegui l'inserimento
                    try {
                        $db->insertObject($tablePartite, $inserimentoRitorno);
                    } catch (Exception $e) {
                        echo 'Error inserting return match: ' . $e->getMessage();
                        // Puoi anche loggare l'errore o fare altre operazioni
                    }
                }
            }
        }
    }

    public static function getGiornateByCompetizioneId($idcomp, $tablePartite)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite)) // Sostituisci con il nome corretto della tua tabella
            ->order($db->quoteName('giornata') . ' ASC');

        $db->setQuery($query);
        return $db->loadObjectList(); // Restituisce un array di oggetti
    }
}
