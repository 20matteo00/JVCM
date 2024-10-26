<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

// Ottieni l'ID della categoria attuale
$currentCategoryId = $this->category->id;
$user = Factory::getUser();
$userId = $user->id; // ID dell'utente corrente
if ($userId==0) $userId = 988;

// Funzione per inserire una competizione nella tabella
function insertCompetizione($data)
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

// Funzione per recuperare le sottocategorie di una data categoria per ricavare gli articoli
function getSubcategories($categoryId)
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('id, title')
        ->from('#__categories')
        ->where('parent_id = ' . (int) $categoryId);

    return $db->setQuery($query)->loadColumn();
}

// Funzione per recuperare le sottocategorie di una data categoria per ricavarmi i nomi delle subcategory
function getSubcategories2($categoryId)
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('id, title')
        ->from('#__categories')
        ->where('parent_id = ' . (int) $categoryId);

    return $db->setQuery($query)->loadObjectList();
}

// Funzione per recuperare gli articoli in base alle sottocategorie
function getArticlesInSubcategories($subcategoryIds)
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('id, title, images, catid') // Aggiungi 'catid' qui
        ->from('#__content')
        ->where('catid IN (' . implode(',', array_map('intval', $subcategoryIds)) . ')')
        ->where('state = 1'); // Solo articoli pubblicati

    return $db->setQuery($query)->loadObjectList();
}

// Funzione per recuperare il tag associato alla categoria dell'articolo
function getCategoryTag($categoryId)
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


// Funzione per recuperare i sottotag di un tag specifico
function getSubTags($tagId)
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('id, title')
        ->from('#__tags')
        ->where('parent_id = ' . (int) $tagId);

    return $db->setQuery($query)->loadObjectList();
}

// Recupera le sottocategorie della categoria 8
$subcategoryIds = getSubcategories(8);
// Recupera gli articoli delle sottocategorie
$articles = getArticlesInSubcategories($subcategoryIds);
// Recupera i sottotag del tag 2
$subTags = getSubTags(2);
// Modalità specifiche
$modalita = [68, 69, 70];

$campionati = getSubcategories2(8);

if (in_array($this->category->id, $modalita)): ?>
    <form action="#" method="post" id="form-participanti">

        <div class="container mt-5">
            <div class="row justify-content-center"> <!-- Centra il contenuto -->
                <div class="col-md-6">
                    <?php
                    // Determina i colori e il titolo della card
                    $cardClass = '';
                    $headerClass = '';

                    if ($this->category->id == 68) {
                        $cardClass = 'border-primary';
                        $headerClass = 'bg-primary text-white';
                    } elseif ($this->category->id == 69) {
                        $cardClass = 'border-success';
                        $headerClass = 'bg-success text-white';
                    } elseif ($this->category->id == 70) {
                        $cardClass = 'border-warning';
                        $headerClass = 'bg-warning text-dark';
                    }
                    ?>

                    <div class="card text-center <?= $cardClass; ?> mb-4">
                        <div class="card-header <?= $headerClass; ?>">
                            <h2><?= htmlspecialchars($this->category->title); ?></h2>
                        </div>
                        <div class="card-body">
                            <!-- Campo "Nome" -->
                            <div class="form-group">
                                <label for="nome_campionato">Nome:</label>
                                <input type="text" class="form-control" id="nome_campionato" name="nome_campionato" required="">
                            </div>

                            <!-- Campo "Andata/Ritorno" -->
                            <div class="form-group">
                                <label for="andata_ritorno">Andata/Ritorno:</label>
                                <select class="form-control" id="andata_ritorno" name="andata_ritorno" required="">
                                    <option value="1">Sì</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <!-- Campo "Partecipanti" in base all'ID categoria -->
                            <div class="form-group">
                                <?php if ($this->category->id == 68): ?>
                                    <label for="numero_partecipanti">Partecipanti (multipli di 2):</label>
                                    <input type="number" class="form-control" id="numero_partecipanti" name="numero_partecipanti" min="2" max="24" step="2" required="">
                                <?php elseif ($this->category->id == 69): ?>
                                    <label for="numero_partecipanti">Partecipanti (esponenti di 2):</label>
                                    <select class="form-control" id="numero_partecipanti" name="numero_partecipanti" required="">
                                        <option value="2">2</option>
                                        <option value="4">4</option>
                                        <option value="8">8</option>
                                        <option value="16">16</option>
                                        <option value="32">32</option>
                                        <option value="64">64</option>
                                        <option value="128">128</option>
                                    </select>
                                <?php elseif ($this->category->id == 70): ?>
                                    <label for="gironi">Gironi:</label>
                                    <select class="form-control" id="gironi" name="gironi" required="">
                                        <option value="2">2</option>
                                        <option value="4">4</option>
                                        <option value="8">8</option>
                                    </select>

                                    <label for="numero_partecipanti">Partecipanti:</label>
                                    <select class="form-control" id="numero_partecipanti" name="numero_partecipanti" required=""></select>

                                    <label for="numero_partecipanti_fasefinale">Fase Finale:</label>
                                    <select class="form-control" id="numero_partecipanti_fasefinale" name="fase_finale" required=""></select>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary my-3" id="submit-button" name="submit-button" disabled>Invia</button>
                        </div> <!-- Fine card body -->
                    </div> <!-- Fine card -->
                </div> <!-- Fine colonna -->
            </div> <!-- Fine row -->
            <div class="row">
                <div class="col-md-6">
                    <!-- Campo "TAG" -->
                    <div class="form-group mt-4">
                        <label for="tags">Stati:</label>
                        <select class="form-control" id="tags" name="tags[]" multiple>
                            <option value="all">Tutti</option> <!-- Opzione "Tutti" -->
                            <?php foreach ($subTags as $tag): ?>
                                <option value="<?= $tag->id; ?>"><?= htmlspecialchars($tag->title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Seleziona gli stati desiderati. Tieni premuto Ctrl (Windows) o Cmd (Mac) per selezionare più di uno.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Campo "CATEGORIA" -->
                    <div class="form-group mt-4">
                        <label for="cat">Campionati:</label>
                        <select class="form-control" id="cat" name="cat[]" multiple>
                            <option value="all">Tutti</option> <!-- Opzione "Tutti" -->
                            <?php foreach ($campionati as $camp): ?>
                                <option value="<?= $camp->id; ?>">
                                    <?= htmlspecialchars($camp->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Seleziona i campionati desiderati. Tieni premuto Ctrl (Windows) o Cmd (Mac) per selezionare più di uno.</small>
                    </div>
                </div>
            </div>
            <!-- Lista articoli -->
            <h4 class="mt-4">Seleziona Squadre: <span id="selected-count">0</span> selezionate</h4>
            <button id="clear-selection" class="btn btn-secondary btn-sm mb-3">Deseleziona tutte</button>
            <div class="row" id="articles-list">
                <?php foreach ($articles as $article): ?>
                    <?php
                    // Recupera il tag della categoria dell'articolo
                    $categoryTag = getCategoryTag($article->catid);
                    ?>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3" data-tag="<?= htmlspecialchars($categoryTag); ?>" data-cat="<?= htmlspecialchars($article->catid); ?>"> <!-- Layout responsive con colonne adattive -->
                        <div class="form-check">
                            <!-- Input checkbox per selezionare l'articolo -->
                            <input
                                class="form-check-input"
                                type="checkbox"
                                value="<?= $article->id; ?>"
                                id="article-<?= $article->id; ?>"
                                name="articles[]"
                                style="margin-top: 20px;">
                            <label class="form-check-label d-flex align-items-center" for="article-<?= $article->id; ?>">
                                <?php
                                // Decodifica JSON per estrarre l'immagine introduttiva
                                $images = json_decode($article->images);
                                if (isset($images->image_intro)) : ?>
                                    <img
                                        src="<?= htmlspecialchars($images->image_intro); ?>"
                                        alt="<?= htmlspecialchars($article->title); ?>"
                                        class="me-2 miniimg" /> <!-- Aggiunge margine a destra dell'immagine -->
                                <?php endif; ?>
                                <!-- Nome dell'articolo -->
                                <span class="overflow-hidden"><?= htmlspecialchars($article->title); ?></span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div> <!-- Fine container -->
    </form>
<?php endif; ?>


<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-button'])) {
    // Assicurati di convalidare e filtrare i dati di input
    $data = [
        'user_id' => (int)$_POST['user_id'], // ID dell'utente
        'nome_competizione' => $_POST['nome_campionato'], // Nome della competizione
        'modalita' => (int)$_POST['modalita'], // Modalità
        'gironi' => isset($_POST['gironi']) ? (int)$_POST['gironi'] : 0, // Gironi
        'andata_ritorno' => (int)$_POST['andata_ritorno'], // Andata/Ritorno
        'partecipanti' => (int)$_POST['numero_partecipanti'], // Partecipanti
        'fase_finale' => isset($_POST['gironi']) ? (int)$_POST['fase_finale'] : 0, // Fase Finale
        'finita' => 0, // Finita, di default a 0
        'squadre' => isset($_POST['articles']) ? $_POST['articles'] : [] // Squadre
    ];

    // Inserisci la competizione
    insertCompetizione($data);
}
?>