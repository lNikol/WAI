document.addEventListener("copy", function (event) {
  // Zapobieganie domyślnej akcji kopiowania
  event.preventDefault();

  // Pobranie zaznaczonego tekstu
  const selectedText = window.getSelection().toString();

  if (selectedText) {
    const textToCopy = selectedText + "\n\n'Skopiowano ze strony WAI-Lavrinov'";

    // Użycie Clipboard API do ustawienia tekstu w schowku
    event.clipboardData.setData("text/plain", textToCopy);

    // Wyświetlenie skopiowanego tekstu w dialogu
    $("#copiedText").text(textToCopy);
    $("#copyDialog").dialog({
      modal: true,
      buttons: {
        Zamknij: function () {
          $(this).dialog("close");
        },
      },
    });
    console.log("Tekst został skopiowany z dopiskiem: " + textToCopy);
  }
});
