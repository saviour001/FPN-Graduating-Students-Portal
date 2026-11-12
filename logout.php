<?php
require_once __DIR__ . '/includes/auth.php';
logout();
set_flash('success', 'You have been signed out successfully.');
redirect('login.php');
