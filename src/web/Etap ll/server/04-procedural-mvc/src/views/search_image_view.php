<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wyszukiwarka zdjęć</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Wyszukiwarka zdjęć</h1>
        <input type="text" id="searchInput" placeholder="Wpisz tytuł zdjęcia..." onkeyup="searchImages()">
        <div id="searchResults"></div>
    </div>

    <script>
        function searchImages() {
            var query = $('#searchInput').val(); // Pobieramy tekst z pola wyszukiwania

            // Wysyłamy zapytanie AJAX
            $.ajax({
                url: 'get_images_by_title', // Ścieżka do skryptu obsługującego zapytanie
                type: 'GET',
                data: { query: query }, // Przesyłamy zapytanie
                success: function(response) {
                    $('#searchResults').html(response); // Wyświetlamy odpowiedź (miniatury)
                },
                error: function() {
                    $('#searchResults').html('<p>Wystąpił błąd podczas wyszukiwania.</p>');
                }
            });
        }
    </script>
</body>
</html>
