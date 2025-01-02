"use strict";
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    const formData = new FormData(form); // Użycie FormData do zbierania danych z formularza
    const imie = formData.get("imie");
    const nazwisko = formData.get("nazwisko");
    const data = formData.get("data");
    const zespol_muzyczny = formData.get("zespol_muzyczny");
    const album = formData.get("album");
    const ulubiony_album = formData.get("ulubiony_album");
    const rok_wydania = Number(formData.get("rok_wydania")); // Konwersja na liczbę
    const today = formData.get("today");
    //console.log(formData);
    fetch("odbierz.php", {
      method: "POST",
      body: formData,
    });
  });
});
