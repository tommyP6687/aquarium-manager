<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
logoutUser();
redirect('login.php');
