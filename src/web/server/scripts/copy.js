"use strict";
document.addEventListener("copy", (event) => {
    var _a, _b;
    // Zapobieganie domyślnej akcji kopiowania
    event.preventDefault();
    // Pobranie zaznaczonego tekstu
    const selectedText = ((_a = window.getSelection()) === null || _a === void 0 ? void 0 : _a.toString()) || "";
    if (selectedText) {
        const textToCopy = `${selectedText}\n\n'Skopiowano ze strony WAI-Lavrinov'`;
        // Użycie API schowka do ustawienia tekstu w schowku
        (_b = event.clipboardData) === null || _b === void 0 ? void 0 : _b.setData("text/plain", textToCopy);
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
