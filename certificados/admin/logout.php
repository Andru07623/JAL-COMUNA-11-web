<?php
/**
 *  Cierra la sesión del funcionario y vuelve al login.
 */
require_once __DIR__ . '/auth.php';
cerrar_sesion();
header('Location: login.php');
exit;
