<h1>Upload Images</h1>
<?php
if (isset($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $error) {
        echo "<p style='color: red;'>$error</p>";
    }
    unset($_SESSION['errors']); 
}
?>
<form action="upload" method="POST" enctype="multipart/form-data">
    <label for="images">Choose images:</label>
    <input type="file" name="images[]" id="images" multiple required><br>

    <button type="submit">Upload</button>
</form>
