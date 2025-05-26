<?php
session_start();
unset($_SESSION['historial']);
header("Location: index.html");
exit;
?>
