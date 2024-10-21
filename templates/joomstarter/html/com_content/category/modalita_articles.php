<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// Ottieni l'ID della categoria attuale
$currentCategoryId = $this->category->id;

// Funzione per recuperare le sottocategorie di una data categoria
function getSubcategories($categoryId)
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('id')
        ->from('#__categories')
        ->where('parent_id = ' . (int) $categoryId);

    return $db->setQuery($query)->loadColumn();
}

// Funzione per recuperare gli articoli in base alle sottocategorie
function getArticlesInSubcategories($subcategoryIds)
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('id, title, images')
        ->from('#__content')
        ->where('catid IN (' . implode(',', array_map('intval', $subcategoryIds)) . ')')
        ->where('state = 1'); // Solo articoli pubblicati

    return $db->setQuery($query)->loadObjectList();
}

// Recupera le sottocategorie della categoria 8
$subcategoryIds = getSubcategories(8);

// Recupera gli articoli delle sottocategorie
$articles = getArticlesInSubcategories($subcategoryIds);

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

// Recupera i sottotag del tag 2
$subTags = getSubTags(2);

// Modalità specifiche
$modalita = [68, 69, 70];

if (in_array($this->category->id, $modalita)): ?>
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
                        <form action="" method="post" id="form-participanti">
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

                            <!-- Campo "TAG" -->
                            <div class="form-group mt-4">
                                <label for="tags">Tag:</label>
                                <select class="form-control" id="tags" name="tags[]" multiple>
                                    <option value="all">Tutti</option> <!-- Opzione "Tutti" -->
                                    <?php foreach ($subTags as $tag): ?>
                                        <option value="<?= $tag->id; ?>"><?= htmlspecialchars($tag->title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Seleziona i tag desiderati. Tieni premuto Ctrl (Windows) o Cmd (Mac) per selezionare più di uno.</small>
                            </div>

                            <button type="submit" class="btn btn-primary my-3" id="submit-button" disabled>Invia</button>
                        </form>

                    </div> <!-- Fine card body -->
                </div> <!-- Fine card -->
            </div> <!-- Fine colonna -->
        </div> <!-- Fine row -->
        <!-- Lista articoli -->
        <h4 class="mt-4">Seleziona Squadre:</h4>
        <div class="row" id="articles-list">
            <?php foreach ($articles as $article): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3"> <!-- Colonne per layout responsive -->
                    <div class="form-check">
                        <input class="form-check-input" style="margin-top: 20px;" type="checkbox" value="<?= $article->id; ?>" id="article-<?= $article->id; ?>">
                        <label class="form-check-label d-flex align-items-center" for="article-<?= $article->id; ?>">
                            <?php
                            // Decodifica la stringa JSON in un oggetto
                            $images = json_decode($article->images);

                            // Controlla se la decodifica ha avuto successo e se image_intro è impostato
                            if (isset($images->image_intro)) {
                                // Stampa il tag img per visualizzare l'immagine
                                echo '<img src="' . htmlspecialchars($images->image_intro) . '" alt="' . htmlspecialchars($article->title) . '" class="me-2 miniimg" />'; // Aggiungi margine a destra
                            }
                            ?>
                            <span class="overflow-hidden"><?= htmlspecialchars($article->title); ?></span> <!-- Nome della squadra -->
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div> <!-- Fine container -->
<?php endif; ?>