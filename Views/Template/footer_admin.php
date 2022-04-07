
<footer>
    <div class="container">
        <div class="columns-footer">
            <div class="logo-blue">
                <img src="<?= media() ?>images/logo_cineco_blue.svg" alt="cinecolombia logo"
                     class="logo-blue-image"/>
            </div>
            <div class="info">
                <ul class="footer_nav">
                    <li>
                        <a href="#">Información Legal</a>
                    </li>
                    <li>
                        <span>|</span>
                    </li>
                    <li>
                        <a href="#">Acerca de Cineco</a>
                    </li>
                    <li>
                        <span>|</span>
                    </li>
                    <li>
                        <a href="#">Contáctanos PQRS</a>
                    </li>
                    <li>
                        <span>|</span>
                    </li>
                    <li>
                        <a href="#">Preguntas Frecuentes</a>
                    </li>
                </ul>
            </div>
            <div class="social">
                <p>Síguenos en redes sociales</p>
                <ul class="footer_social">
                    <li>
                        <a href="https://www.facebook.com/cinecolombiaoficial">
                            <img src="<?= media() ?>images/logo-facebook.svg" alt="facebook icon"
                                 class="icon"/>
                        </a>
                    </li>
                    <li>
                        <a href="https://twitter.com/Cine_Colombia">
                            <img src="<?= media() ?>images/logo-twitter.svg" alt="user icon" class="icon"/>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.instagram.com/cinecolombia">
                            <img src="<?= media() ?>images/logo-instagram.svg" alt="user icon" class="icon"/>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer_legal">
            <p>&copy; 2021 Cine Colombia - Proyecto Bases de Datos</p>
            <p>Kevin Santiago Martinez - Joshep Mateo Granda</p>
        </div>
    </div>
</footer>
<script src="<?= media() ?>js/sidebar.js"></script>
<script src="<?= media() ?>js/dropdown.js"></script>
<script src="<?= media() ?>js/helpers.js"></script>
<script src="<?= media() ?>js/font_awesome.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const BASE_URL = "<?=base_url()?>";
</script>
<?php
if ($data['page_name'] == 'users'){
    echo '<script src="'.media().'js/functions_users.js"></script>';
}else if ($data['page_name'] == 'roles'){
    echo '<script src="'.media().'js/functions_roles.js"></script>';
}
?>
<script src="<?= media() ?>js/functions_admin.js"></script>

</body>
</html>
