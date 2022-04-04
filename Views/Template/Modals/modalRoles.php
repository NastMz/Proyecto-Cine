<!-- The Modal -->
<div id="modal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <div class="close-button">
            <div class="form-title">
                <h5>Nuevo Rol</h5>
                <p>Ingresa la informacion del nuevo rol</p>
            </div>
            <span class="close" onclick="closeModal()">
                <i class="fa-regular fa-circle-xmark"></i>
            </span>
        </div>
        <div class="form">
            <div class="form-group">
                <form id="formRole" name="formRole">
                    <div class="field">
                        <label for="txtRoleName" class="label">
                            Nombre<span class="alert">*</span>:
                        </label>
                        <input type="text" name="txtRoleName" id="txtRoleName" placeholder="Nombre del rol"
                               class="input" required/>
                    </div>
                    <div class="field">
                        <label for="txtRoleDescription" class="label">
                            Descripción<span class="alert">*</span>:
                        </label>
                        <textarea name="txtRoleDescription" id="txtRoleDescription" placeholder="Descripción del rol"
                                  rows="2" class="input" required></textarea>
                    </div>
                    <div class="field">
                        <label for="status" class="label">
                            Estado<span class="alert">*</span>:
                        </label>
                        <select name="status" id="status" class="input" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <span class="alert">
                        *Todos los campos son obligatorios
                    </span>
                    <div class="form-button">
                        <button id="btnActionForm" type="submit" class="btn btn-submit">
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