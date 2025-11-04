<?php
// inc/auth.php
session_start();
require_once __DIR__.'/db.php';

function is_logged_in(){
    return !empty($_SESSION['user']);
}
function require_login(){
    if(!is_logged_in()){
        header('Location: /order-management-system/index.php');
        exit;
    }
}
function current_user(){
    return $_SESSION['user'] ?? null;
}
function require_role($role){
    $u = current_user();
    if(!$u || $u['role'] !== $role){
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}
