<?php
session_start(); ?>

<?php @include 'header.php'; ?>
<?php @include 'hero.php'; ?>

<?php
if (isset($_SESSION['Uloggedin']) && $_SESSION['Uloggedin'] === true) {
    // @include 'algo.php';
}
?>

<?php @include 'bloodbanks.php'; ?>

<?php @include 'all_Campain.php'; ?>




<?php @include 'footor.php'; ?>