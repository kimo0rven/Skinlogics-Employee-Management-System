<?php
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    echo $_SESSION['edit_id'];
}
?>


<div>
    <?php echo $_SESSION['edit_id']; ?>
</div>