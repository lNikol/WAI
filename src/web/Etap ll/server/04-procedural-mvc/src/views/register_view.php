<?php

if (isset($_SESSION['user_id'])) {
    echo "<p>You are already logged in. <a href='logout'>Logout</a></p>";
    exit;
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

<h1>Register</h1>
<?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
<form method="POST">
    <label>Name: <input type="text" name="name" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <label>Confirm Password: <input type="password" name="confirm_password" required></label><br>
    <button type="submit">Register</button>
</form>
<style>
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