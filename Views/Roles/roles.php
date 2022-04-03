<?php
headerAdmin($data);
getModal("modalRoles", $data);
?>

    <main>
        <div class="container-dashboard">
            <div class="title-page">
                <h1>
                    <i class="fa fa-id-badge"></i> <?= $data['page_title'] ?>
                </h1>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url() ?>dashboard">
                            <i class="fa-solid fa-house"></i>
                        </a>
                        <span>\<span>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= base_url() ?>roles">
                            <?= $data['page_title'] ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="content">
                <div class="head-bar">
                    <!-- Trigger/Open The Modal -->
                    <button id="open-modal" class="btn-primary" onclick="openModal('modalRoles')">
                        <i class="fa fa-plus"></i> Nuevo
                    </button>
                </div>

                <div class="table" id="">

                </div>
            </div>
        </div>
    </main>

<?php
footerAdmin($data);
?>