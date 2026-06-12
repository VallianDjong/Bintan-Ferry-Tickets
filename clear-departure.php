<?php
session_start();
unset($_SESSION['selected_departure']);
header('Location: search-result.php');
exit;