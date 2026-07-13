<?php

session_start();

unset($_SESSION['kiosk_cart'], $_SESSION['last_kiosk_order']);

header('Location: index.php');
exit;
