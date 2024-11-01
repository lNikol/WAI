document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form") as HTMLFormElement;

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const formData = new FormData(form); // Użycie FormData do zbierania danych z formularza

    const imie = formData.get("imie") as string;
    const nazwisko = formData.get("nazwisko") as string;
    const data = formData.get("data") as string;
    const zespol_muzyczny = formData.get("zespol_muzyczny") as string;
    const album = formData.get("album") as string;
    const ulubiony_album = formData.get("ulubiony_album") as string;
    const rok_wydania = Number(formData.get("rok_wydania")); // Konwersja na liczbę
    const today = formData.get("today") as string;

    console.log(formData);

    fetch("odbierz.php", {
      method: "POST",
      body: formData,
    });
  });
});
