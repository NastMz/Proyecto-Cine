<?php
headerHome($data);
?>
<main>
    <section class="error">
        <div class="messaje">
            <h1 class="section_title">404</h1>
            <p>No hemos encontrado la página que estás buscando.</p>
            <ul class="links-list">
                <li>
                    <a href="<?= base_url() ?>">Página de inicio</a>
                </li>
            </ul>
        </div>
    </section>
</main>
<?php
footerHome($data);
?>
