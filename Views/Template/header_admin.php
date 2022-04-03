<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="referrer" content="origin">
    <meta name="theme-color" content="#000">
    <meta name="msapplication-navbutton-color" content="#000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="#000">
    <meta content="yes" name="apple-touch-fullscreen"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="CineColombia">
    <meta name="author" content="Kevin Santiago Martinez - Joshep Mateo Granada">
    <meta name="description"
          content="Proyecto curso Bases de Datos, estudiantes Kevin Santiago Martinez y Joshep Mateo Granada">

    <link rel="apple-touch-icon" sizes="180x180"
          href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/apple-touch-icon.png">
    <link rel="apple-touch-startup-image"
          href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/default.png">

    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/iphone5_splash.png"
          media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/iphone6_splash.png"
          media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/iphoneplus_splash.png"
          media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/iphonex_splash.png"
          media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/iphonexr_splash.png"
          media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/iphonexsmax_splash.png"
          media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/ipad_splash.png"
          media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/ipadpro1_splash.png"
          media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/ipadpro3_splash.png"
          media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)"
          rel="apple-touch-startup-image"/>
    <link href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/pwa-icons/ios/ipadpro2_splash.png"
          media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)"
          rel="apple-touch-startup-image"/>

    <link rel="icon" type="image/png" sizes="32x32"
          href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/favicon/cineco/favicon-32x32.png ">
    <link rel="icon" type="image/png" sizes="16x16"
          href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/favicon/cineco/favicon-16x16.png">
    <link rel="mask-icon"
          href="https://archivos.cinecolombia.com/cineco-cms-frontend/1.0.78/dist/images/favicon/cineco/safari-pinned-tab.svg"
          color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <title><?php echo $data['page_tag']; ?></title>

    <link rel="stylesheet" href="<?= media() ?>css/style.css">
</head>
<body>

<header>
    <div class="header-bar">
        <div class="header-columns">
            <div class="navigation">
                <div class="sidebar-button">
                    <span onclick="openNav()">
                        <i class="fa-solid fa-bars"></i>
                    </span>
                </div>
                <div class="logo">
                    <a href="<?= base_url() ?>dashboard">
                        <img src="<?= media() ?>images/logo_cineco.svg" alt="cinecolombia logo" class="logo-image"/>
                    </a>
                </div>
                <nav>
                    <div class="nav-menu">
                        <ul class="nav-tabs">
                            <li><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
                        </ul>
                    </div>
                </nav>
            </div>
            <div class="profile">
                <div class="user">
                    <div class="dropdown">
                        <button class="dropbtn">
                            <div class="user-image">
                                <i class="fa fa-caret-down"></i>
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </div>
                        </button>
                        <div class="dropdown-content">
                            <a href="<?= base_url() ?>settings">
                                <i class="fa fa-cogs" aria-hidden="true"></i> Configuración
                            </a>
                            <a href="<?= base_url() ?>profile">
                                <i class="fa fa-user" aria-hidden="true"></i> Perfil
                            </a>
                            <a href="<?= base_url() ?>logout">
                                <i class="fa  fa-sign-out" aria-hidden="true"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</header>
<?php
require_once("nav_admin.php");
?>