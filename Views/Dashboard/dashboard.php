<?php
headerAdmin($data);
?>

<main>
    <div class="container-dashboard">
        <div class="title-page">
            <h1><i class="fa fa-gauge-high" aria-hidden="true"></i> <?= $data['page_title'] ?></h1>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>dashboard">
                        <i class="fa-solid fa-house"></i>
                    </a>
                    <span>\<span>
                </li>
            </ul>
        </div>
        <div class="content">

        </div>
    </div>
</main>

<?php
footerAdmin($data);
?>