<?php
if (isset($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $error) {
        echo "<p style='color: red;'>$error</p>";
    }
    unset($_SESSION['errors']);
}
?>
<nav>
    <ul class="menu">
        <li><a href="public">Gallery</a></li>
        <li><a href="search_image">Search Image</a></li>
        <li><a href="upload">Upload Image</a></li>
        <li><a href="save_selected">Selected Images</a></li>
        
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout">Logout</a></li>
        <?php else: ?>
            <li><a href="register">Register</a></li>
            <li><a href="login">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>
<div class="container">
    <h1>Wybrane Zdjęcia</h1>

    <?php if (!empty($_SESSION['selected_images'])): ?>
        <form method="post" action="remove_selected">
            <div class="gallery">
                <div class="thumbnails">
                    <?php foreach ($_SESSION['selected_images'] as $thumbnail): ?>
                        <div class="thumbnail-item">
                            <label for="remove-image-<?= htmlspecialchars($thumbnail['id']) ?>">
                                <a href="<?= htmlspecialchars($thumbnail['watermark_path']) ?>" target="_blank">
                                    <img src="<?= htmlspecialchars($thumbnail['thumbnail_path']) ?>" alt="<?= htmlspecialchars($thumbnail['title']) ?>">
                                </a>
                                <div class="image-info">
                                    <p><strong>ID:
                                    <input type="checkbox" id="remove-image-<?= htmlspecialchars($thumbnail['_id']) ?>" 
                                       name="remove_images[]" 
                                       value="<?= htmlspecialchars($thumbnail['_id']) ?>">

                                    </strong> <?= htmlspecialchars($thumbnail['_id']) ?></p>
                                    <p><strong>Autor:</strong> <?= htmlspecialchars($thumbnail['author_name']) ?></p>
                                    <p><strong>Status:</strong> <?= $thumbnail['public'] ? 'Publiczne' : 'Prywatne' ?></p>
                                    <p><strong>Tytuł:</strong> <?= htmlspecialchars($thumbnail['image_name']) ?></p>
                                </div>
                                
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit">Usuń zaznaczone z zapamiętanych</button>
        </form>
    <?php else: ?>
        <p>Nie wybrano żadnych zdjęć.</p>
    <?php endif; ?>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.thumbnails {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.thumbnail-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 200px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    background-color: #f9f9f9;
}

.thumbnail-item img {
    max-width: 100%;
    height: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
}

.image-info {
    text-align: center;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.pagination a {
    text-decoration: none;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #333;
    background-color: #f9f9f9;
}

.pagination a.active {
    background-color: #333;
    color: #fff;
}
nav {
    background-color: #333;
    font-family: Arial, sans-serif;
    margin-bottom:5px;
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
