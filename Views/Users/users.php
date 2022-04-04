<?php
headerAdmin($data);
getModal("modalUsers", $data);
?>

    <main>
        <div class="container-dashboard">
            <div class="title-page">
                <h1>
                    <i class="fa fa-user-group"></i> <?= $data['page_title'] ?>
                </h1>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url() ?>dashboard">
                            <i class="fa-solid fa-house"></i>
                        </a>
                        <span>\<span>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= base_url() ?>users">
                            <?= $data['page_title'] ?>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="content">
                <div class="head-bar">
                    <!-- Trigger/Open The Modal -->
                    <button id="open-modal" class="btn btn-primary" onclick="openModal()">
                        <i class="fa fa-plus"></i> Nuevo
                    </button>
                </div>

                <div class="table-data">
                    <table id="tableUsers">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE</th>
                            <th>APELLIDO</th>
                            <th>EMAIL</th>
                            <th>TELEFONO</th>
                            <th>ROL</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-label="userCode">1007590327</td>
                            <td data-label="userName">Kevin</td>
                            <td data-label="userLastname">Martinez</td>
                            <td data-label="userEmail">ksmartinez@gmail.com</td>
                            <td data-label="userPhone">31458956</td>
                            <td data-label="userRole">Cliente</td>
                            <td data-label="status" ><span class="status status-active">Activo</span></td>
                            <td>
                                <button id="open-modal" class="btn btn-edit" onclick="openModal('modalUsers')">
                                    <i class="fa-regular fa-pen-to-square"></i> Editar
                                </button>
                                <button id="open-modal" class="btn btn-delete">
                                    <i class="fa-regular fa-trash-can"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="8"></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </main>

<?php
footerAdmin($data);
?>