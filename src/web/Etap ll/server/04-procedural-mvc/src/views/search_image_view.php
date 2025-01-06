<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wyszukiwarka zdjęć</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<nav>
    <ul class="menu">
        <li><a href="public">Public Gallery</a></li>
        <li><a href="gallery">Private Gallery</a></li>
        <li><a href="search_image">Search Image</a></li>
        <li><a href="upload">Upload Image</a></li>
        <li><a href="save_selected">Selected Images</a></li>
        
        <?php if (isset($model['user_id'])): ?>
            <li><a href="logout">Logout</a></li>
        <?php else: ?>
            <li><a href="register">Register</a></li>
            <li><a href="login">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div class="container">
    <h1>Wyszukiwarka zdjęć</h1>
    <input type="text" id="searchInput" placeholder="Wpisz tytuł zdjęcia..." onkeyup="searchImages()">
    <div id="searchResults"></div>
</div>

<script>
function searchImages() {
    let query = $('#searchInput').val();

    $.ajax({
        url: 'search_images_by_title',
        type: 'GET',
        data: { query: query }, // Przesyłam zapytanie
        success: function(response) {
            try {
                let jsonResponse = JSON.parse(response);

                if (jsonResponse.images.length > 0) {
                    // Tworze miniaturki
                    let html = '';
                    jsonResponse.images.forEach(function(image) {
                        html += '<div class="thumbnail">';
                        html += '<a href="' + image.watermark_path + '" alt="' + image.image_name + '" target="_blank">';
                        html += '<img src="' + image.thumbnail_path + '" alt="' + image.image_name + '"> </a>';
                        html += '<p> <b>Title:</b> ' + image.image_name + '</p>';
                        html += '<p> <b>Author: </b>' + image.author_name + '</p>';
                        html += '</div>';
                    });
                    $('#searchResults').html(html);
                } else if (jsonResponse.images.length === 0) {
                    $('#searchResults').html('<p>Brak wyników dla podanego tytułu.</p>');
                } else if (jsonResponse.errors.length > 0) {
                    $('#searchResults').html('<p style="color: red;">' + jsonResponse.errors.join(', ') + '</p>');
                } else {
                    $('#searchResults').html('<p>Nieoczekiwany format danych.</p>');
                }
            } catch (error) {
                // Obsługa błędów parsowania JSON
                console.error("Błąd parsowania JSON:", error.message);
                $('#searchResults').html('<p style="color: red;">Błąd podczas przetwarzania danych: ' + error.message + '</p>');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Loguje odpowiedź w przypadku błędu
            let errorMessage = 'Wystąpił błąd: ' + textStatus + ' - ' + errorThrown;
            $('#searchResults').html('<p style="color: red;">' + errorMessage + '</p>');
        }
    });
}
</script>

<style>
.thumbnail{
    margin-top:20px;
}
nav {
    background-color: #333;
    font-family: Arial, sans-serif;
    margin-bottom: 5px;
}

.menu {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
}

.menu li {
    position: relative;
    margin: 0;
}

.menu li a {
    color: white;
    text-decoration: none;
    padding: 14px 20px;
    display: block;
}

.menu li a:hover {
    background-color: #575757;
}
</style>
</body>
</html>
