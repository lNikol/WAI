<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Images</title>
</head>
<body>
<h1>Upload Images</h1>
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

<form action="upload" method="POST" enctype="multipart/form-data">
    <label for="images">Choose images:</label>
    <input type="file" name="images[]" multiple id="file-input" onchange="updateFormFields()">
    <div id="input-fields-container"></div>
    <button type="submit">Upload</button>
</form>

<script>
    function updateFormFields() {
    let userLogin = '<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "guest"; ?>';

    let files = document.getElementById('file-input').files;
    let container = document.getElementById('input-fields-container');
    
    container.innerHTML = '';

    for (let i = 0; i < files.length; i++) {
        let inputGroup = document.createElement('div');
        inputGroup.classList.add('input-group');
        
        let fileNameInput = document.createElement('input');
        fileNameInput.type = 'text';
        fileNameInput.name = 'file_titles[]';
        fileNameInput.placeholder = 'Title for ' + files[i].name;

        let watermarkInput = document.createElement('input');
        watermarkInput.type = 'text';
        watermarkInput.name = 'watermarks[]';
        watermarkInput.placeholder = 'Enter watermark for ' + files[i].name;
        watermarkInput.required = true;

        let authorInput = document.createElement('input');
        authorInput.type = 'text';
        authorInput.name = 'authors[]';
        authorInput.placeholder = 'Enter author for ' + files[i].name;
        authorInput.value = userLogin;
        authorInput.required = true;
        
        let fileParagraph = document.createElement('p');
        fileParagraph.appendChild(fileNameInput);
        fileParagraph.appendChild(watermarkInput);
        fileParagraph.appendChild(authorInput);
        
        <?php if (isset($_SESSION['user_id']) && strpos($_SESSION['user_id'], 'anon') === false): ?>
            let publicRadio = document.createElement('label');
            publicRadio.innerHTML = '<input type="radio" name="public[' + i + ']" value="1" checked> Publiczny';
            let privateRadio = document.createElement('label');
            privateRadio.innerHTML = '<input type="radio" name="public[' + i + ']" value="0"> Prywatny';
            fileParagraph.appendChild(publicRadio);
            fileParagraph.appendChild(privateRadio);
        <?php endif; ?>
        
        container.appendChild(fileParagraph);
    }
}
</script>

<style>
.input-group {
    margin-bottom: 10px;
}

input[readonly] {
    background-color: #f0f0f0;
    color: #888;
}

input[type="text"] {
    padding: 5px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 300px;
}

p {
    font-size: 14px;
    margin-bottom: 10px;
}

button {
    padding: 10px 15px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
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
