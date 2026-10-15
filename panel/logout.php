<?php
session_start();
session_destroy();
require_once __DIR__ . '/inc/config.php';
header('Location: ' . panel_url('login.php'));
exit;
