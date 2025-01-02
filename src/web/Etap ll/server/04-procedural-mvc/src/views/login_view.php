<?php
if (isset($_SESSION['user_id'])) {
    echo "<p>You are already logged in. <a href='logout'>Logout</a></p>";
    exit;
}
?>
<h1>Login</h1>
<?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
<form method="POST">
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Login</button>
</form>
