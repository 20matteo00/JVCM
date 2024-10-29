<?php

namespace Joomstarter\Helpers;

defined(constant_name: '_JEXEC') or die; // Assicurati che il file venga caricato solo da Joomla

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Exception;

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

    public static function getCompetizioniPerUtente($userId)
    {
        // Importa il database di Joomla
        $db = Factory::getDbo();

        // Costruisci la query per selezionare i dati dalla tabella delle competizioni solo per l'utente corrente
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__competizioni'))
            ->where($db->quoteName('user_id') . ' = ' . $db->quote($userId)); // Filtra per user_id

        // Imposta ed esegui la query
        $db->setQuery($query);

        // Restituisci i risultati della query come un array di oggetti
        return $db->loadObjectList();
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

    public static function CreaTabelleCompetizione($idCompetizione, $squadre)
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
        `VC` INT DEFAULT NULL,
        `NC` INT DEFAULT NULL,
        `PC` INT DEFAULT NULL,
        `GFC` INT DEFAULT NULL,
        `GSC` INT DEFAULT NULL,
        `VT` INT DEFAULT NULL,
        `NT` INT DEFAULT NULL,
        `PT` INT DEFAULT NULL,
        `GFT` INT DEFAULT NULL,
        `GST` INT DEFAULT NULL,
        `girone` INT DEFAULT NULL,
        PRIMARY KEY (`squadra`)
    )";
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            echo 'Errore nella creazione della tabella statistiche: ' . $e->getMessage();
        }

        // Popola la tabella statistiche con tutte le squadre della competizione
        foreach ($squadre as $squadraId) {
            $query = "INSERT IGNORE INTO `$tableStatistiche` (`squadra`) VALUES (" . (int) $squadraId . ")";
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
                echo 'Errore durante l\'inserimento nella tabella statistiche: ' . $e->getMessage();
            }
        }
    }
    public static function GeneraCampionato($squadre, $tablePartite, $ar)
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
                    if ($ar == 1) {
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
    }

    public static function GeneraStatistiche($squadre, $tableStatistiche, $tablePartite)
    {
        $db = Factory::getDbo();

        // Inizializza le statistiche
        $statistiche = [];

        // Prepara la query per ottenere tutte le partite
        $query = $db->getQuery(true)
            ->select('squadra1, squadra2, gol1, gol2')
            ->from($db->quoteName($tablePartite));

        $db->setQuery($query);

        try {
            $partite = $db->loadObjectList();
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle partite: ' . $e->getMessage();
            return;
        }

        // Calcola le statistiche per ogni partita
        foreach ($partite as $partita) {
            $squadra1 = $partita->squadra1;
            $squadra2 = $partita->squadra2;
            $gol1 = $partita->gol1;
            $gol2 = $partita->gol2;

            if ($gol1 === NULL || $gol2 === NULL)
                continue;
            // Inizializza le statistiche per le squadre se non esistono
            if (!isset($statistiche[$squadra1])) {
                $statistiche[$squadra1] = [
                    'VC' => 0, // Vittorie in casa
                    'NC' => 0, // Nulle in casa
                    'PC' => 0, // Perde in casa
                    'GFC' => 0, // Gol Fatti
                    'GSC' => 0, // Gol Subiti
                    'VT' => 0,
                    'NT' => 0,
                    'PT' => 0,
                    'GFT' => 0,
                    'GST' => 0,
                    'girone' => null, // Puoi gestire i gironi se necessario
                ];
            }

            if (!isset($statistiche[$squadra2])) {
                $statistiche[$squadra2] = [
                    'VC' => 0, // Vittorie in casa
                    'NC' => 0, // Nulle in casa
                    'PC' => 0, // Perde in casa
                    'GFC' => 0, // Gol Fatti
                    'GSC' => 0, // Gol Subiti
                    'VT' => 0,
                    'NT' => 0,
                    'PT' => 0,
                    'GFT' => 0,
                    'GST' => 0,
                    'girone' => null, // Puoi gestire i gironi se necessario
                ];
            }

            // Aggiorna le statistiche in base al risultato
            if ($gol1 > $gol2) { // Squadra 1 vince
                $statistiche[$squadra1]['VC']++;
                $statistiche[$squadra2]['PT']++;
            } elseif ($gol1 < $gol2) { // Squadra 2 vince
                $statistiche[$squadra2]['VT']++;
                $statistiche[$squadra1]['PC']++;
            } else { // Pareggio
                $statistiche[$squadra1]['NC']++;
                $statistiche[$squadra2]['NT']++;
            }

            // Aggiorna gol fatti e subiti
            $statistiche[$squadra1]['GFC'] += $gol1;
            $statistiche[$squadra1]['GSC'] += $gol2;
            $statistiche[$squadra2]['GFT'] += $gol2;
            $statistiche[$squadra2]['GST'] += $gol1;
        }

        // Aggiorna la tabella statistiche nel database
        foreach ($squadre as $squadraId) {
            if (isset($statistiche[$squadraId])) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName($tableStatistiche))
                    ->set($db->quoteName('VC') . ' = ' . $statistiche[$squadraId]['VC'])
                    ->set($db->quoteName('NC') . ' = ' . $statistiche[$squadraId]['NC'])
                    ->set($db->quoteName('PC') . ' = ' . $statistiche[$squadraId]['PC'])
                    ->set($db->quoteName('GFC') . ' = ' . $statistiche[$squadraId]['GFC'])
                    ->set($db->quoteName('GSC') . ' = ' . $statistiche[$squadraId]['GSC'])
                    ->set($db->quoteName('VT') . ' = ' . $statistiche[$squadraId]['VT'])
                    ->set($db->quoteName('NT') . ' = ' . $statistiche[$squadraId]['NT'])
                    ->set($db->quoteName('PT') . ' = ' . $statistiche[$squadraId]['PT'])
                    ->set($db->quoteName('GFT') . ' = ' . $statistiche[$squadraId]['GFT'])
                    ->set($db->quoteName('GST') . ' = ' . $statistiche[$squadraId]['GST'])
                    ->where($db->quoteName('squadra') . ' = ' . (int) $squadraId);

                $db->setQuery($query);
                try {
                    $db->execute();
                } catch (Exception $e) {
                    echo 'Errore durante l\'aggiornamento delle statistiche: ' . $e->getMessage();
                }
            }
        }
    }

    // Funzione per ottenere la classifica delle squadre
    public static function getClassifica($tableStatistiche)
    {
        $db = Factory::getDbo();

        // Query per ottenere tutte le statistiche e calcolare i punti, la differenza reti e i gol fatti
        $query = $db->getQuery(true)
            ->select('*')
            ->select('
            (VC + VT) * 3 + (NC + NT) AS punti,  
            (GFC + GFT - GSC - GST) AS diff_reti, 
            (GFC + GFT) AS gol_fatti')
            ->from($db->quoteName($tableStatistiche))
            ->order('punti DESC, diff_reti DESC, gol_fatti DESC, squadra ASC'); // Ordina secondo i criteri specificati


        $db->setQuery($query);

        try {
            return $db->loadObjectList(); // Restituisce un array di oggetti
        } catch (Exception $e) {
            echo 'Errore durante il recupero delle statistiche: ' . $e->getMessage();
            return [];
        }
    }

    public static function getClassificaAR($tablePartite, $ar, $numsquadre, $view)
    {
        if ($ar === 0) {
            return [];
        }

        if ($ar === 1) {
            $classifica = []; // Array per contenere la classifica delle squadre

            // Ottieni il database
            $db = Factory::getDbo();

            // Crea la query per ottenere le partite
            $query = $db->getQuery(true)
                ->select('*') // Seleziona tutti i campi
                ->from($db->quoteName($tablePartite)); // Sostituisci con il nome della tua tabella

            // Esegui la query
            $db->setQuery($query);
            $partite = $db->loadObjectList(); // Ottieni i risultati come array di oggetti

            // Controlla se $partite è valido
            if (!is_array($partite) && !is_object($partite)) {
                return []; // Gestisci l'errore
            }

            // Itera su tutte le partite
            foreach ($partite as $partita) {
                // Controlla se la partita è stata giocata nella giornata valida
                if ($view === "andata") {
                    $partitedaprendere = $partita->giornata < $numsquadre;
                } elseif ($view === "ritorno") {
                    $partitedaprendere = $partita->giornata >= $numsquadre;
                }
                if ($partitedaprendere) {
                    // Estrai le squadre e i risultati
                    $squadraCasa = $partita->squadra1; // ID della squadra di casa
                    $squadraTrasferta = $partita->squadra2; // ID della squadra in trasferta
                    $golCasa = $partita->gol1; // Gol della squadra di casa
                    $golTrasferta = $partita->gol2; // Gol della squadra in trasferta

                    // Inizializza le squadre se non già presente
                    if (!isset($classifica[$squadraCasa])) {
                        $classifica[$squadraCasa] = new \stdClass();
                        $classifica[$squadraCasa]->ID = $squadraCasa;
                        $classifica[$squadraCasa]->V = 0;
                        $classifica[$squadraCasa]->N = 0;
                        $classifica[$squadraCasa]->P = 0;
                        $classifica[$squadraCasa]->GF = 0;
                        $classifica[$squadraCasa]->GS = 0;
                    }
                    if (!isset($classifica[$squadraTrasferta])) {
                        $classifica[$squadraTrasferta] = new \stdClass();
                        $classifica[$squadraTrasferta]->ID = $squadraTrasferta;
                        $classifica[$squadraTrasferta]->V = 0;
                        $classifica[$squadraTrasferta]->N = 0;
                        $classifica[$squadraTrasferta]->P = 0;
                        $classifica[$squadraTrasferta]->GF = 0;
                        $classifica[$squadraTrasferta]->GS = 0;
                    }

                    // Calcola i risultati
                    if ($golCasa > $golTrasferta) {
                        // Vittoria per la squadra di casa
                        $classifica[$squadraCasa]->V++;
                        $classifica[$squadraTrasferta]->P++;
                    } elseif ($golCasa < $golTrasferta) {
                        // Vittoria per la squadra in trasferta
                        $classifica[$squadraTrasferta]->V++;
                        $classifica[$squadraCasa]->P++;
                    } else {
                        // Pareggio
                        $classifica[$squadraCasa]->N++;
                        $classifica[$squadraTrasferta]->N++;
                    }

                    // Aggiorna i gol fatti e subiti
                    $classifica[$squadraCasa]->GF += $golCasa;
                    $classifica[$squadraCasa]->GS += $golTrasferta;
                    $classifica[$squadraTrasferta]->GF += $golTrasferta;
                    $classifica[$squadraTrasferta]->GS += $golCasa;
                }
            }

            return array_values($classifica); // Restituisci un array di oggetti
        }

        return []; // Se non ci sono altre condizioni, restituisci un array vuoto
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

    public static function getTablePartite($ID)
    {
        // Recupera le giornate dalla competizione
        $db = Factory::getDbo();
        $prefix = $db->getPrefix();
        $tablePartite = $prefix . 'competizione' . $ID . '_partite';
        return $tablePartite;
    }
    public static function getTableStatistiche($ID)
    {
        // Recupera le giornate dalla competizione
        $db = Factory::getDbo();
        $prefix = $db->getPrefix();
        $getTableStatistiche = $prefix . 'competizione' . $ID . '_statistiche';
        return $getTableStatistiche;
    }

    public static function calculateStatistics($squadra, $view, $ar)
    {
        $squadraID = $punti = $giocate = $vinte = $pari = $perse = $golFatti = $golSubiti = $differenza = 0;

        if ($view === 'casa') {
            $punti = ($squadra->VC * 3) + $squadra->NC;
            $giocate = $squadra->VC + $squadra->NC + $squadra->PC;
            $vinte = $squadra->VC;
            $pari = $squadra->NC;
            $perse = $squadra->PC;
            $golFatti = $squadra->GFC;
            $golSubiti = $squadra->GSC;
        } elseif ($view === 'trasferta') {
            $punti = ($squadra->VT * 3) + $squadra->NT;
            $giocate = $squadra->VT + $squadra->NT + $squadra->PT;
            $vinte = $squadra->VT;
            $pari = $squadra->NT;
            $perse = $squadra->PT;
            $golFatti = $squadra->GFT;
            $golSubiti = $squadra->GST;
        } elseif ($view === 'andata') {
            if ($ar === 0) {
                $punti = (($squadra->VC + $squadra->VT) * 3) + ($squadra->NC + $squadra->NT);
                $giocate = $squadra->VC + $squadra->VT + $squadra->NC + $squadra->NT + $squadra->PC + $squadra->PT;
                $vinte = $squadra->VC + $squadra->VT;
                $pari = $squadra->NC + $squadra->NT;
                $perse = $squadra->PC + $squadra->PT;
                $golFatti = $squadra->GFC + $squadra->GFT;
                $golSubiti = $squadra->GSC + $squadra->GST;
            } elseif ($ar === 1) {
                $squadraID = $squadra->ID; // Accedi all'ID della squadra
                $punti = ($squadra->V * 3) + $squadra->N; // Calcola i punti
                $giocate = $squadra->V + $squadra->N + $squadra->P; // Partite giocate
                $vinte = $squadra->V; // Partite vinte
                $pari = $squadra->N; // Partite pareggiate
                $perse = $squadra->P; // Partite perse
                $golFatti = $squadra->GF; // Gol fatti
                $golSubiti = $squadra->GS; // Gol subiti

            }
        } elseif ($view === 'ritorno') {
            if ($ar === 0) {
                $punti = 0;
                $giocate = 0;
                $vinte = 0;
                $pari = 0;
                $perse = 0;
                $golFatti = 0;
                $golSubiti = 0;
            } elseif ($ar === 1) {
                $squadraID = $squadra->ID; // Accedi all'ID della squadra
                $punti = ($squadra->V * 3) + $squadra->N; // Calcola i punti
                $giocate = $squadra->V + $squadra->N + $squadra->P; // Partite giocate
                $vinte = $squadra->V; // Partite vinte
                $pari = $squadra->N; // Partite pareggiate
                $perse = $squadra->P; // Partite perse
                $golFatti = $squadra->GF; // Gol fatti
                $golSubiti = $squadra->GS; // Gol subiti

            }
        } elseif ($view === 'totale') {
            $punti = (($squadra->VC + $squadra->VT) * 3) + ($squadra->NC + $squadra->NT);
            $giocate = $squadra->VC + $squadra->VT + $squadra->NC + $squadra->NT + $squadra->PC + $squadra->PT;
            $vinte = $squadra->VC + $squadra->VT;
            $pari = $squadra->NC + $squadra->NT;
            $perse = $squadra->PC + $squadra->PT;
            $golFatti = $squadra->GFC + $squadra->GFT;
            $golSubiti = $squadra->GSC + $squadra->GST;
        }

        $differenza = $golFatti - $golSubiti;

        return [
            'squadra' => $squadraID,
            'punti' => $punti,
            'giocate' => $giocate,
            'vinte' => $vinte,
            'pari' => $pari,
            'perse' => $perse,
            'golFatti' => $golFatti,
            'golSubiti' => $golSubiti,
            'differenza' => $differenza,
        ];
    }

    public static function getAndamento($tablePartite)
    {
        // Inizializza un array per tenere traccia dei punti accumulati per ogni squadra
        $andamento = [];

        // Ottieni il database
        $db = Factory::getDbo();

        // Crea la query per ottenere le partite
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName($tablePartite))
            ->order('giornata ASC'); // Assicurati di ordinare per giornata

        // Esegui la query
        $db->setQuery($query);
        $partite = $db->loadObjectList();

        // Itera su tutte le partite
        foreach ($partite as $partita) {
            $giornata = $partita->giornata;
            $squadraCasa = $partita->squadra1;
            $squadraTrasferta = $partita->squadra2;
            $golCasa = $partita->gol1;
            $golTrasferta = $partita->gol2;

            // Inizializza le squadre se non già presente
            if (!isset($andamento[$squadraCasa])) {
                $andamento[$squadraCasa] = [
                    'squadra' => $squadraCasa,
                    'risultati' => array_fill(1, max(array_column($partite, 'giornata')), 0) // Inizializza con 0
                ];
            }
            if (!isset($andamento[$squadraTrasferta])) {
                $andamento[$squadraTrasferta] = [
                    'squadra' => $squadraTrasferta,
                    'risultati' => array_fill(1, max(array_column($partite, 'giornata')), 0) // Inizializza con 0
                ];
            }

            // Calcola i punti per la giornata
            $puntiCasa = 0;
            $puntiTrasferta = 0;

            if ($golCasa > $golTrasferta) {
                $puntiCasa = 3; // Vittoria per la squadra di casa
            } elseif ($golCasa < $golTrasferta) {
                $puntiTrasferta = 3; // Vittoria per la squadra in trasferta
            } else {
                $puntiCasa = 1; // Pareggio
                $puntiTrasferta = 1; // Pareggio
            }

            // Aggiorna i punti per la squadra di casa e accumula i risultati
            $andamento[$squadraCasa]['risultati'][$giornata] += $puntiCasa;
            $andamento[$squadraCasa]['risultati'][$giornata] += ($andamento[$squadraCasa]['risultati'][$giornata - 1] ?? 0); // Accumula punti

            // Aggiorna i punti per la squadra in trasferta e accumula i risultati
            $andamento[$squadraTrasferta]['risultati'][$giornata] += $puntiTrasferta;
            $andamento[$squadraTrasferta]['risultati'][$giornata] += ($andamento[$squadraTrasferta]['risultati'][$giornata - 1] ?? 0); // Accumula punti
        }

        // Ritorna l'andamento calcolato
        return $andamento;
    }

    public static function getGiornate($tablePartite)
    {
        // Ottieni il database
        $db = Factory::getDbo();

        // Crea la query per ottenere il numero massimo di giornate
        $query = $db->getQuery(true)
            ->select('MAX(giornata) AS max_giornata')
            ->from($db->quoteName($tablePartite));

        // Esegui la query
        $db->setQuery($query);
        $maxGiornate = $db->loadResult();

        // Ora puoi usare $maxGiornate come numero di giornate
        return $maxGiornate;
    }

}
