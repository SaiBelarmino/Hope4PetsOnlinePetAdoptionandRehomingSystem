<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hope4Pets</title>
    <link rel="shortcut icon" type="image/png" href="../../assets/images/logos/seodashlogo.png" />
    <link rel="stylesheet" href="../../assets/css/styles.min.css" />
</head>
<style>
/* Inline layout fix so header doesn't scroll */
body.pu-layout {height:100vh;display:flex;flex-direction:column;overflow:hidden;}
body.pu-layout .page-wrapper {flex:1 1 auto;display:flex;flex-direction:column;min-height:0;overflow:hidden;}
body.pu-layout .page-wrapper .body-wrapper {flex:1 1 auto;display:flex;flex-direction:column;min-height:0;overflow:hidden;}
body.pu-layout .pu-scroll-wrapper, body.pu-layout .container-fluid {flex:1 1 auto;overflow-y:auto;min-height:0;-webkit-overflow-scrolling:touch;}
body.pu-layout header.app-header {position:sticky;top:0;z-index:1030;}
html {scrollbar-gutter:stable;}
</style>

<body class="pu-layout">
    <!-- Body Wrapper: public layout (no left sidebar) -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
    <div class="body-wrapper px-0">
        <!-- Header (top nav) will be included next by pages -->