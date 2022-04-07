<!-- The Modal -->
<div id="modal" class="modal">
    <!-- Modal content -->
    <div id="modalContent" class="modal-content">
        <div class="close-button">
            <div class="form-title">
                <h5 id="formTitle">Nuevo Usuario</h5>
                <p id="formMessage">Ingresa la informacion del nuevo usuario</p>
            </div>
            <span class="close" onclick="closeModal()">
                <i class="fa-regular fa-circle-xmark"></i>
            </span>
        </div>
        <div class="form">
            <div class="form-group">
                <form id="formUser" name="formUser" method="post">
                    <div class="field-group">
                        <div class="field">
                            <label for="txtUserName" class="label">
                                Nombre<span class="alert">*</span>:
                            </label>
                            <input type="text" name="txtUserName" id="txtUserName" placeholder="Nombre del usuario"
                                   class="input" />
                        </div>
                        <div class="field">
                            <label for="txtUserLastname" class="label">
                                Apellido<span class="alert">*</span>:
                            </label>
                            <input type="text" name="txtUserLastname" id="txtUserLastname"
                                   placeholder="Apellido del usuario" class="input" />
                        </div>
                        <div class="field">
                            <label for="txtUserId" class="label">
                                Documento<span class="alert">*</span>:
                            </label>
                            <input type="text" name="txtUserId" id="txtUserId" placeholder="Documento del usuario"
                                   class="input" />
                        </div>
                        <div class="field">
                            <label for="txtPhone" class="label">
                                Teléfono<span class="alert">*</span>:
                            </label>
                            <input type="text" name="txtPhone" id="txtPhone" placeholder="Teléfono del usuario"
                                   class="input" />
                        </div>
                        <div class="field">
                            <label for="txtEmail" class="label">
                                Correo<span class="alert">*</span>:
                            </label>
                            <input type="text" name="txtEmail" id="txtEmail" placeholder="Correo del usuario"
                                   class="input" />
                        </div>
                        <div class="field">
                            <label for="txtPassword" class="label">
                                Contraseña<span class="alert">*</span>:
                            </label>
                            <input type="password" name="txtPassword" id="txtPassword" placeholder="Contraseña del usuario"
                                   class="input" />
                        </div>
                        <div class="field">
                            <label for="role" class="label">
                                Rol<span class="alert">*</span>:
                            </label>
                            <select name="role" id="role" class="input" >
                            </select>
                        </div>
                        <div class="field">
                            <label for="status" class="label">
                                Estado<span class="alert">*</span>:
                            </label>
                            <select name="status" id="status" class="input" >
                                <option value="1">Activo</option>
                                <option value="2">Inactivo</option>
                            </select>
                        </div>
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