<?php
headerAdmin($data);
getModal("modalUsers", $data);
?>

    <main>
        <div class="container-dashboard">
            <div class="title-page">
                <div class="title">
                    <h1>
                        <i class="fa fa-user-group"></i> <?= $data['page_title'] ?>
                    </h1>
                    <button id="open-modal" class="btn btn-primary" onclick="openModal()">
                        <i class="fa fa-plus"></i> Nuevo
                    </button>
                </div>
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
                    <div class="registers">
                        <label for="numRegisters" class="label">Mostrar:</label>
                        <select id="numRegisters" name="numRegisters" class="input" >
                            <option value="10">10</option>
                            <option value="20">25</option>
                            <option value="30">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="search-bar">
                        <label for="search" class="label">Buscar:</label>
                        <input id="search" name="search" type="text" class="input"/>
                    </div>
                </div>

                <div class="table-data">
                    <table id="tableUsers">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE</th>
                            <th>EMAIL</th>
                            <th>TELEFONO</th>
                            <th>ROL</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                        </thead>
                        <tbody id="tableUsersBody">

                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="8"></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="pagination">
                    <a href="#">&laquo;</a>
                    <a class="active" href="#">1</a>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">4</a>
                    <a href="#">5</a>
                    <a href="#">6</a>
                    <a href="#">&raquo;</a>
                </div>
            </div>
        </div>
    </main>

<?php
footerAdmin($data);
?>