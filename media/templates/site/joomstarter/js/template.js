(Joomla = window.Joomla || {}),
  (function (e, t) {
    "use strict";
    function l(e) {
      for (
        var l = (e && e.target ? e.target : t).querySelectorAll(
            "fieldset.btn-group"
          ),
          o = 0;
        o < l.length;
        o++
      ) {
        var n = l[o];
        if (!0 === n.getAttribute("disabled")) {
          n.style.pointerEvents = "none";
          for (var i = n.querySelectorAll(".btn"), r = 0; r < i.length; r++)
            i[r].classList.add("disabled");
        }
      }
    }
    t.addEventListener("DOMContentLoaded", function (e) {
      l(e);
      var o = t.getElementById("back-top");
      if (o) {
        function n() {
          t.body.scrollTop > 20 || t.documentElement.scrollTop > 20
            ? o.classList.add("visible")
            : o.classList.remove("visible");
        }
        n(),
          (window.onscroll = function () {
            n();
          }),
          o.addEventListener("click", function (e) {
            e.preventDefault(), window.scrollTo(0, 0);
          });
      }
      [].slice
        .call(t.head.querySelectorAll('link[rel="lazy-stylesheet"]'))
        .forEach(function (e) {
          e.rel = "stylesheet";
        });
    }),
      t.addEventListener("joomla:updated", l);
  })(Joomla, document);

document.addEventListener("DOMContentLoaded", function () {
  const table = document.querySelector(".category-table");
  const headers = table.querySelectorAll("th");
  const tableBody = table.querySelector("tbody");
  const rows = Array.from(tableBody.querySelectorAll("tr"));

  // Funzione di confronto per l'ordinamento
  const compare = (index, ascending) => (rowA, rowB) => {
    const cellA = rowA.querySelectorAll("td")[index].innerText.toLowerCase();
    const cellB = rowB.querySelectorAll("td")[index].innerText.toLowerCase();

    if (!isNaN(cellA) && !isNaN(cellB)) {
      return ascending ? cellA - cellB : cellB - cellA;
    }

    if (cellA < cellB) {
      return ascending ? -1 : 1;
    }
    if (cellA > cellB) {
      return ascending ? 1 : -1;
    }
    return 0;
  };

  // Funzione per riordinare la tabella
  const sortTable = (index, ascending) => {
    const sortedRows = rows.sort(compare(index, ascending));

    while (tableBody.firstChild) {
      tableBody.removeChild(tableBody.firstChild);
    }

    tableBody.append(...sortedRows);
  };

  // Aggiunge il listener agli header per rendere le colonne ordinabili
  headers.forEach((header, index) => {
    let ascending = true;
    header.style.cursor = "pointer"; // Aggiungi il cursore a puntatore

    header.addEventListener("click", () => {
      sortTable(index, ascending);
      ascending = !ascending; // Alterna l'ordinamento
    });
  });
});

// Funzione per estrarre i colori dall'immagine
function extractColors(imageSrc) {
  const img = new Image();
  img.src = imageSrc;

  img.onload = function () {
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    canvas.width = img.width;
    canvas.height = img.height;
    ctx.drawImage(img, 0, 0);

    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;

    const colorCounts = {};

    // Conta i colori
    for (let i = 0; i < data.length; i += 4) {
      const r = data[i];
      const g = data[i + 1];
      const b = data[i + 2];
      const hex =
        "#" +
        ((1 << 24) + (r << 16) + (g << 8) + b)
          .toString(16)
          .slice(1)
          .toUpperCase();

      colorCounts[hex] = (colorCounts[hex] || 0) + 1;
    }

    // Ordina i colori per frequenza (dal più presente al meno presente)
    const sortedColors = Object.entries(colorCounts).sort(
      (a, b) => b[1] - a[1]
    );

    // Estrai i primi 5 colori più presenti
    const dominantColors = sortedColors.slice(0, 5).map((color) => color[0]);

    // Mostra i colori nel DOM
    const colorBoxes = document.getElementById("colorBoxes");
    colorBoxes.innerHTML = ""; // Pulisci eventuali colori precedenti
    dominantColors.forEach((color) => {
      const box = document.createElement("div");
      box.style.backgroundColor = color;
      box.style.width = "50px";
      box.style.height = "50px";
      box.style.display = "inline-block";
      box.style.marginRight = "5px";
      colorBoxes.appendChild(box);
    });

    // Mostra anche i nomi dei colori e le frequenze
    const colorInfo = sortedColors.slice(0, 5); // Ottieni i 5 colori con le loro frequenze
    const colorInfoDiv = document.createElement("div");
    colorInfo.forEach(([color, count]) => {
      const info = document.createElement("div");
      info.textContent = `${color}: ${count} volte`;
      colorInfoDiv.appendChild(info);
    });
    colorBoxes.appendChild(colorInfoDiv); // Aggiungi le informazioni dei colori
  };
}

// Esegui l'estrazione dei colori quando il documento è pronto
document.addEventListener("DOMContentLoaded", function () {
  const imageSrc = document.getElementById("articleImage").src;
  extractColors(imageSrc);
});

document.addEventListener("DOMContentLoaded", function () {
  const gironiSelect = document.getElementById("gironi");
  const partecipantiSelect = document.getElementById("numero_partecipanti");
  const faseFinaleSelect = document.getElementById(
    "numero_partecipanti_fasefinale"
  );

  const optionsMapping = {
    2: [8, 16, 32], // Available options for 2 gironi
    4: [16, 32, 64], // Available options for 4 gironi
    8: [32, 64, 128], // Available options for 8 gironi
  };

  const faseFinaleMapping = {
    8: [4, 2], // Available options for 8 partecipanti
    16: [8, 4], // Available options for 16 partecipanti
    32: [16, 8], // Available options for 32 partecipanti
    64: [32, 16], // Available options for 64 partecipanti
    128: [64, 32], // Available options for 128 partecipanti
  };

  function populateOptions(selectElement, options) {
    selectElement.innerHTML = "";
    options.forEach(function (value) {
      const option = document.createElement("option");
      option.value = value;
      option.text = value;
      selectElement.appendChild(option);
    });
  }

  gironiSelect.addEventListener("change", function () {
    const selectedValue = parseInt(gironiSelect.value);
    const availableOptions = optionsMapping[selectedValue] || [];
    populateOptions(partecipantiSelect, availableOptions);
    partecipantiSelect.dispatchEvent(new Event("change"));
  });

  partecipantiSelect.addEventListener("change", function () {
    const selectedValue = parseInt(partecipantiSelect.value);
    const availableOptions = faseFinaleMapping[selectedValue] || [];
    populateOptions(faseFinaleSelect, availableOptions);
  });

  // Trigger change event to populate the initial options based on the default selected value
  gironiSelect.dispatchEvent(new Event("change"));
});

document.addEventListener("DOMContentLoaded", function () {
  const submitButton = document.getElementById("submit-button");
  const partecipantiInput = document.getElementById("numero_partecipanti");
  const articlesList = document.getElementById("articles-list");

  // Funzione per controllare il numero di selezioni
  function checkSubmitButton() {
    const selectedCheckboxes = articlesList.querySelectorAll(
      "input[type='checkbox']:checked"
    );
    const participantCount = parseInt(partecipantiInput.value) || 0;

    // Controlla se il numero di articoli selezionati corrisponde al numero di partecipanti
    submitButton.disabled = selectedCheckboxes.length !== participantCount;
  }

  // Aggiungi un listener a ogni checkbox
  articlesList.addEventListener("change", checkSubmitButton);

  // Aggiungi un listener al campo partecipanti per gestire il cambiamento
  partecipantiInput.addEventListener("input", checkSubmitButton);
});

/* FORM CREA COMPETIZIONE */

document.addEventListener("DOMContentLoaded", function () {
  // Riferimento ai campi di filtro
  const searchInput = document.getElementById("search");
  const tagSelect = document.getElementById("tags");
  const categorySelect = document.getElementById("cat");
  const articlesList = document.getElementById("articles-list");

  // Funzione per eseguire il filtraggio
  function filterArticles() {
    const searchValue = searchInput.value.toLowerCase();
    const selectedTags = Array.from(tagSelect.selectedOptions).map(
      (option) => option.value
    );
    const selectedCategories = Array.from(categorySelect.selectedOptions).map(
      (option) => option.value
    );

    // Loop attraverso gli articoli e applica i filtri
    const articles = articlesList.querySelectorAll(".col-6");
    articles.forEach((article) => {
      const title = article.querySelector("label").textContent.toLowerCase();
      const tags = article.getAttribute("data-tag").split(",");
      const categoryId = article.getAttribute("data-cat");

      // Verifica se il titolo contiene la ricerca
      const matchesSearch = title.includes(searchValue);

      // Verifica se il tag e la categoria sono selezionati
      const matchesTags =
        selectedTags.includes("all") ||
        selectedTags.some((tag) => tags.includes(tag));
      const matchesCategories =
        selectedCategories.includes("all") ||
        selectedCategories.includes(categoryId);

      // Mostra o nasconde l'articolo in base ai filtri
      if (matchesSearch && matchesTags && matchesCategories) {
        article.style.display = "";
      } else {
        article.style.display = "none";
      }
    });
  }

  // Ascolta gli eventi di input sui filtri
  searchInput.addEventListener("input", filterArticles);
  tagSelect.addEventListener("change", filterArticles);
  categorySelect.addEventListener("change", filterArticles);
});

document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(
    "#articles-list .form-check-input"
  ); // Seleziona tutte le checkbox
  const countDisplay = document.getElementById("selected-count");
  const clearButton = document.getElementById("clear-selection");
  const submitButton = document.getElementById("submit-button");
  const partecipantiInput = document.getElementById("numero_partecipanti");

  // Funzione per aggiornare il conteggio e gestire lo stato dei pulsanti
  function updateSelectedCount() {
    const selectedCount = Array.from(checkboxes).filter(
      (checkbox) => checkbox.checked
    ).length;
    countDisplay.textContent = selectedCount;

    // Disabilita il pulsante "Deseleziona tutte" se non ci sono selezioni
    clearButton.disabled = selectedCount === 0;

    // Verifica se il numero di selezioni corrisponde al valore del campo "numero_partecipanti"
    const partecipanti = parseInt(partecipantiInput.value, 10);
    submitButton.disabled = selectedCount !== partecipanti;
  }

  // Aggiungi un listener di evento a ciascuna checkbox
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateSelectedCount);
  });

  // Aggiungi un listener per il bottone "Deseleziona tutte"
  clearButton.addEventListener("click", function (event) {
    event.preventDefault(); // Impedisci l'invio della form
    checkboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });
    updateSelectedCount(); // Aggiorna il conteggio a zero
  });

  // Aggiungi un listener per il campo "numero_partecipanti" per aggiornare lo stato del pulsante "Invia"
  partecipantiInput.addEventListener("change", updateSelectedCount);

  // Inizializza lo stato del pulsante al caricamento della pagina
  updateSelectedCount();
});

document.addEventListener("DOMContentLoaded", function () {
  const partecipantiInput = document.getElementById("numero_partecipanti");
  const articlesList = document.getElementById("articles-list");

  // Funzione per aggiornare lo stato delle checkbox
  function updateCheckboxes() {
    const checkboxes = articlesList.querySelectorAll('input[type="checkbox"]');
    const selectedCount = Array.from(checkboxes).filter(
      (checkbox) => checkbox.checked
    ).length;
    const maxPartecipanti = parseInt(partecipantiInput.value) || 0;

    // Se il numero di checkbox selezionate è uguale al numero di partecipanti, disabilita le altre
    if (selectedCount >= maxPartecipanti) {
      checkboxes.forEach((checkbox) => {
        if (!checkbox.checked) {
          checkbox.disabled = true; // Disabilita le checkbox non selezionate
        }
      });
    } else {
      // Riabilita tutte le checkbox se il numero selezionato non è uguale al numero di partecipanti
      checkboxes.forEach((checkbox) => {
        checkbox.disabled = false; // Riabilita tutte le checkbox
      });
    }

    // Aggiorna il conteggio delle selezioni
    document.getElementById("selected-count").innerText = selectedCount;
  }

  // Aggiungi un listener per il cambiamento nel campo dei partecipanti
  partecipantiInput.addEventListener("change", updateCheckboxes);

  // Aggiungi un listener a ogni checkbox per aggiornare lo stato quando viene selezionata o deselezionata
  const checkboxes = articlesList.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", updateCheckboxes);
  });
});

function updateAllGolValues(index) {
  const partite = document.querySelectorAll(
    `[id^='gol1-${index}-'], [id^='gol2-${index}-']`
  );

  partite.forEach((partita) => {
    const i = partita.id.split("-").pop(); // Ottieni l'indice della partita

    // Recupera i valori attuali di gol1 e gol2
    const gol1Value = document.getElementById(`gol1-${index}-${i}`).value;
    const gol2Value = document.getElementById(`gol2-${index}-${i}`).value;

    // Imposta i valori nei campi nascosti del form del footer
    document.getElementById(`hidden-gol1-${index}-${i}`).value = gol1Value;
    document.getElementById(`hidden-gol2-${index}-${i}`).value = gol2Value;
  });
}

// Funzione per selezionare il contenuto dell'input
function selezionaInput(input) {
  input.select();
}
