<!-- The Modal -->
<div id="modal" class="modal">
    <!-- Modal content -->
    <div id="modalContent" class="modal-content">
        <div class="close-button">
            <div class="form-title">
                <h5 id="formTitle">Nuevo Rol</h5>
                <p id="formMessage">Ingresa la informacion del nuevo rol</p>
            </div>
            <span class="close" onclick="closeModal()">
                <i class="fa-regular fa-circle-xmark"></i>
            </span>
        </div>
        <div class="form">
            <div class="form-group">
                <form id="formRole" name="formRole" method="post">
                    <div class="field">
                        <input type="hidden" name="txtRoleCode" id="txtRoleCode" placeholder="Codigo del rol"
                               class="input"/>
                    </div>
                    <div class="field">
                        <label for="txtRoleName" class="label">
                            Nombre<span class="alert">*</span>:
                        </label>
                        <input type="text" name="txtRoleName" id="txtRoleName" placeholder="Nombre del rol"
                               class="input"/>
                    </div>
                    <div class="field">
                        <label for="txtDescription" class="label">
                            Descripcion<span class="alert">*</span>:
                        </label>
                        <textarea name="txtDescription" id="txtDescription" placeholder="Descripcion del rol"
                               class="input"></textarea>
                    </div>
                    <div class="field">
                        <label for="status" class="label">
                            Estado<span class="alert">*</span>:
                        </label>
                        <select name="status" id="status" class="input">
                            <option value="1">Activo</option>
                            <option value="2">Inactivo</option>
                        </select>
                    </div>
                    <span class="alert">
                        *Todos los campos son obligatorios
                    </span>
                    <div class="form-button">
                        <button id="btnActionForm" type="submit" class="btn btn-submit" onclick="sendRequest(event)">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="form-footer">

        </div>
    </div>
</div>