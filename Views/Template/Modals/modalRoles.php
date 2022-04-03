<!-- The Modal -->
<div id="modalRoles" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <div class="close-button">
            <div class="form-title">
                <h5>Nuevo Rol</h5>
                <p>Ingresa la informacion del nuevo rol</p>
            </div>
            <span class="close" onclick="closeModal('modalRoles')">
                <i class="fa-regular fa-circle-xmark"></i>
            </span>
        </div>
        <div class="form">
            <div class="form-group">
                <form id="formRole" name="formRole">
                    <div class="field">
                        <label for="txtRoleName" class="label">
                            Nombre:
                        </label>
                        <input type="text" name="txtRoleName" id="txtRoleName" placeholder="Nombre del rol"  class="input"/>
                    </div>
                    <div class="field">
                        <label for="txtRoleDescription" class="label">
                            Descripción:
                        </label>
                        <textarea name="txtRoleDescription" id="txtRoleDescription" placeholder="Descripción del rol"
                                  rows="2" class="input"></textarea>
                    </div>
                    <div class="field">
                        <label for="status" class="label">
                            Estado:
                        </label>
                        <select name="status" id="status" required  class="input">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="form-button">

                    </div>
                </form>
            </div>
        </div>
        <div class="form-footer">

        </div>
    </div>
</div>