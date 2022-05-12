<?php
headerAdmin($data);
getModal("modalRoles", $data);
?>

    <main>
        <div class="main">
            <div id="loader-body"></div>
            <div class="container-dashboard animate-bottom" id="content">
                <div class="title-page">
                    <div class="title">
                        <h1>
                            <i class="fa fa-user-group"></i> <?= $data['page_title'] ?>
                        </h1>
                        <button id="open-modal" class="btn btn-primary" onclick="openModal('430px')">
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
                            <select id="numRegisters" name="numRegisters" class="input">
                                <option value="10" selected>10</option>
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
                        <table id="tableRoles">
                            <thead>
                            <tr>
                                <th>
                                <span id="role_id" class="table-column">
                                    <span class="thead">
                                        ID
                                    </span>
                                    <span class="caret">
                                        <i class="fa fa-caret-up"></i>
                                        <i class="fa fa-caret-down"></i>
                                    </span>
                                </span>
                                </th>
                                <th>
                                <span id="role_name" class="table-column">
                                    <span class="thead">
                                        NOMBRE
                                    </span>
                                    <span class="caret">
                                        <i class="fa fa-caret-up"></i>
                                        <i class="fa fa-caret-down"></i>
                                    </span>
                                </span>
                                </th>
                                <th>
                                <span id="role_name" class="table-column">
                                    <span class="thead">
                                        DESCRIPCION
                                    </span>
                                    <span class="caret">
                                        <i class="fa fa-caret-up"></i>
                                        <i class="fa fa-caret-down"></i>
                                    </span>
                                </span>
                                </th>
                                <th>
                                <span id="status" class="table-column">
                                    <span class="thead">
                                        ESTADO
                                    </span>
                                    <span class="caret">
                                        <i class="fa fa-caret-up"></i>
                                        <i class="fa fa-caret-down"></i>
                                    </span>
                                </span>
                                </th>
                                <th>
                                <span id="actions" class="table-column">
                                    <span class="thead">
                                        ACCIONES
                                    </span>
                                </span>
                                </th>
                            </tr>
                            </thead>
                            <tbody id="tableRoleBody">

                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="8"></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="pagination">
                        <div id="pageNavPosition" class="pager-nav"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php
footerAdmin($data);
?>