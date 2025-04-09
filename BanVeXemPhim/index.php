<?php
include('includes/header.php');

?>

<div class="mt-4">
    <?php include('includes/slider.php'); ?>
</div>


<div id="toast"></div>

<?php alertMessage(); ?>
<div class="container mx-10 py-3 content ">
    <?php include('views/list-film.php'); ?>
    <?php include('views/list-content.php'); ?>
</div>

<?php include('includes/footer.php'); ?>
