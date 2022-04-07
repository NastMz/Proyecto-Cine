<aside class="sidebar-nav">
    <div class="sidebar-container" id="sidebar-nav">
        <div id="mySidenav" class="sidenav">
            <div class="logo-sidebar">
                <a href="<?= base_url() ?>">
                    <img src="<?= media() ?>images/logo_cineco.svg" alt="cinecolombia logo"/>
                </a>
            </div>
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">
            <span class="close-icon">
                <i class="fa-solid fa-angle-left"></i>
            </span>
            </a>

            <h4 class="title-sidebar title-users">USUARIOS</h4>
            <a href="<?= base_url() ?>users" class="option">
                <i class="fa fa-user-group"></i> Usuarios
            </a>
            <a href="<?= base_url() ?>types" class="option">
                <i class="fa fa-user-tag"></i> Tipos Usuario
            </a>
            <a href="<?= base_url() ?>roles" class="option">
                <i class="fa fa-id-badge"></i> Roles
            </a>
            <a href="<?= base_url() ?>permissions" class="option">
                <i class="fa fa-fingerprint"></i> Permisos
            </a>

            <h4 class="title-sidebar title-movies">PELICULAS</h4>
            <a href="<?= base_url() ?>movies" class="option">
                <i class="fa fa-film"></i> Peliculas
            </a>
            <a href="<?= base_url() ?>directors" class="option">
                <i class="fa fa-user"></i> Directores
            </a>
            <a href="<?= base_url() ?>directs" class="option">
                <i class="fa fa-clapperboard"></i> Dirige
            </a>
            <a href="<?= base_url() ?>actors" class="option">
                <i class="fa fa-users"></i> Actores
            </a>
            <a href="<?= base_url() ?>cast" class="option">
                <i class="fa fa-scroll"></i> Papel
            </a>
            <a href="<?= base_url() ?>" class="option">
                <i class="fa fa-copy"></i> Reparto
            </a>

            <h4 class="title-sidebar title-cinema">SALAS</h4>
            <a href="<?= base_url() ?>cinemas" class="option">
                <i class="fa fa-desktop"></i> Salas
            </a>
            <a href="<?= base_url() ?>films" class="option">
                <i class="fa fa-video-camera"></i> Funciones
            </a>
            <a href="<?= base_url() ?>seats" class="option">
                <i class="fa fa-couch"></i> Asientos
            </a>

            <h4 class="title-sidebar title-others">OTROS</h4>
            <a href="<?= base_url() ?>tickets" class="option">
                <i class="fa fa-ticket"></i> Boletos
            </a>
            <a href="<?= base_url() ?>paid" class="option">
                <i class="fa fa-receipt"></i> Pagado
            </a>
            <a href="<?= base_url() ?>pay" class="option">
                <i class="fa fa-credit-card"></i> Tipo pago
            </a>

        </div>
    </div>
</aside>