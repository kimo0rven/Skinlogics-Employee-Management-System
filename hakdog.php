<form action="" method="post">
    <input type="text" name="test_input">
    <button type="submit" name="test_submit">Test</button>
</form>

<?php
if (isset($_POST['test_submit'])) {
    print_r($_POST);
}
?>