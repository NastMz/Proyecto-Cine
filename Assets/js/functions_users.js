document.addEventListener("DOMContentLoaded", load);

// Get the modal
let modal = document.getElementById("modal");
let modalContent = document.getElementById("modalContent");

// Get the <span> element that closes the modal
let span = document.getElementsByClassName("close")[0];

// When the role clicks on the button, open the modal
function openModal() {
    resetForm();
    modal.style.display = "block";
    setTimeout(function () {
        modalContent.style.maxHeight = "700px";
    }, 100);
}

// When the role clicks on <span> (x), close the modal
function closeModal() {
    populateTable();
    modalContent.style.maxHeight = "0";
    setTimeout(function () {
        modal.style.display = "none";
        resetForm();
    }, 500);
}

// When the role clicks anywhere outside of the modal, close it
modal.addEventListener('click', function (e) {
    if (e.target === modalContent.parentElement) {
        closeModal();
    }
});

let userName = document.getElementById("txtUserName");
let userLastname = document.getElementById("txtUserLastname");
let userId = document.getElementById("txtUserId");
let userPhone = document.getElementById("txtPhone");
let userEmail = document.getElementById("txtEmail");
let password = document.getElementById("txtPassword");
let roleId = document.getElementById("role");

let btnForm = document.getElementById("btnActionForm");
let status = document.getElementById("status");
let tableColumns = document.getElementsByClassName('table-column');

let table = document.getElementById("tableUsersBody");
let search = document.getElementById('search');
let numRegisters = document.getElementById("numRegisters");

let rows = [];
let filterRows = [];

search.addEventListener('keyup', function (event) {
    filterTable();
});

numRegisters.addEventListener('input', filterRegisters);

for (let column of tableColumns) {
    if (column.id !== "actions") {
        column.addEventListener('click', function (event) {
            toggleArrow(event);
        });
    }
}

function resetForm() {
    document.getElementById("formTitle").innerHTML = "Nuevo Usuario";
    document.getElementById("formMessage").innerHTML = "Ingresa la informacion del nuevo usuario";
    userName.value = "";
    userLastname.value = "";
    userId.value = "";
    userPhone.value = "";
    userEmail.value = "";
    password.value = "";
    roleId.value = 1;
    status.value = 1;
    btnForm.id = "btnActionForm";
    userId.disabled = false;
}

let pager = new Pager('tableUsers', parseInt(numRegisters.value));

let usersList = [];
let roleList = [];

async function load() {
    roleList = await loadJson("roles", `findAll`);
    let roleOptions = [];
    roleList.map((role) => {
        roleOptions.push(`<option value="${role.role_id}">${role.role_name}</option>`);
    });
    for (let option of roleOptions) {
        roleId.innerHTML += option;
    }
    usersList = await loadJson("users", "findAll");
    usersList.sort(sort_by("user_id", false));
    populateTable();
    filterRows = rows;

    pager.init();
    pager.showPageNav('pager', 'pageNavPosition');
    if (usersList.length > 0) {
        pager.showPage(1);
    }
}

async function modalEdit(id) {
    let user = usersList.find(findUser => findUser.user_id === id);

    document.getElementById("formTitle").innerHTML = "Editar Usuario";
    document.getElementById("formMessage").innerHTML = "Ingresa la nueva informacion del usuario";

    userName.value = user.user_name;
    userLastname.value = user.user_lastname;
    userId.value = user.user_id;
    userPhone.value = user.phone;
    userEmail.value = user.email;
    password.value = "";
    roleId.value = user.role_id;
    status.value = user.status;
    btnForm.id = "btnEditForm";

    userId.disabled = true;

    modal.style.display = "block";
    setTimeout(function () {
        modalContent.style.maxHeight = "700px";
    }, 100);

}

async function alertModal(json) {

    let fields = [];
    if (json.code === 400) {
        let empty = json.fields;
        empty.map((field) => {
            switch (field) {
                case "user_id":
                    fields.push("Documento");
                    break;
                case "user_name":
                    fields.push("Nombre");
                    break;
                case "user_lastname":
                    fields.push("Apellido");
                    break;
                case "role_id":
                    fields.push("Rol");
                    break;
                case "status":
                    fields.push("Estado");
                    break;
                case "email":
                    fields.push("Correo");
                    break;
                case "password":
                    fields.push("Contraseña");
                    break;
                case "phone":
                    fields.push("Telefono");
                    break;
            }
        });
    }

    let string = fields.toString();
    string = string.replaceAll(",", ", ");

    switch (json.code) {
        case 200:
            Swal.fire({
                title: 'Buen trabajo!', text: `${json.message}`, icon: 'success', confirmButtonText: 'Confirmar',
                confirmButtonColor: '#1c508d'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    usersList = await loadJson("users", "findAll");
                    usersList.sort(sort_by("user_id", false));
                    closeModal();
                }
            });
            break;
        case 400:
            Swal.fire({
                title: 'Cuidado!',
                text: `${json.message + "\n" + string}`,
                icon: 'warning',
                confirmButtonText: 'Confirmar',
                confirmButtonColor: '#1c508d'
            });
            break;
        case 500:
            Swal.fire({
                title: 'Algo salio mal!', text: `${json.message}`, icon: 'error', confirmButtonText: 'Confirmar',
                confirmButtonColor: '#1c508d'
            });
            break;
        default:
            Swal.fire({
                title: 'Parece que algo no va bien!',
                text: 'Estamos trabajando para solucionarlo',
                icon: 'info',
                confirmButtonText: 'Confirmar',
                confirmButtonColor: '#1c508d'
            });
            break;
    }
}

// Metodos del crud
async function save(e) {
    e.preventDefault();
    let formData = new FormData();
    formData.append("user_id", userId.value);
    formData.append("user_name", userName.value);
    formData.append("user_lastname", userLastname.value);
    formData.append("email", userEmail.value);
    formData.append("phone", userPhone.value);
    formData.append("password", password.value);
    formData.append("status", status.value.toString());
    formData.append("role_id", roleId.value);
    let url = BASE_URL + "users/save";
    let response = await fetch(url, {
        method: "POST", body: formData,
    });

    let json = JSON.parse(await response.text());

    await alertModal(json);

}

async function deleteUser(id) {
    Swal.fire({
        title: 'Ateción!',
        showCancelButton: true,
        text: 'Eliminar este registro podria causar perdida de información, considera cambiar su estado a "Inactivo"',
        icon: 'warning',
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#1c508d'
    }).then(async (result) => {
        if (result.isConfirmed) {
            let url = BASE_URL + `users/delete/${id}`;
            let response = await fetch(url, {
                method: "DELETE", headers: {
                    "Content-Type": "application/json",
                },
            });
            let json = JSON.parse(await response.text());
            await alertModal(json);
        }
    });
}

async function update(e) {
    e.preventDefault();
    let formData = new FormData();
    formData.append("user_id", userId.value);
    formData.append("user_name", userName.value);
    formData.append("user_lastname", userLastname.value);
    formData.append("role_id", roleId.value);
    formData.append("status", status.value.toString());
    formData.append("email", userEmail.value);
    formData.append("password", password.value);
    formData.append("phone", userPhone.value);
    let url = BASE_URL + "users/update";
    let response = await fetch(url, {
        method: "POST", body: formData,
    });

    let json = JSON.parse(await response.text());

    await alertModal(json);

}

// Metodos tabla

function toggleArrow(event) {

    let element = event.target.parentElement;
    let field, reverse;

    clear(element, usersList);

    field = element.id;

    let caretUp = element.getElementsByClassName('fa-caret-up')[0];
    let caretDown = element.getElementsByClassName('fa-caret-down')[0];

    if (!caretUp.classList.contains('caret-active') && !caretDown.classList.contains('caret-active')) {
        caretDown.classList.add('caret-active');
        reverse = true;
    } else if (!caretUp.classList.contains('caret-active') && caretDown.classList.contains('caret-active')) {
        caretUp.classList.add('caret-active');
        caretDown.classList.remove('caret-active');
        reverse = false;
    } else {
        caretUp.classList.remove('caret-active');
        caretDown.classList.remove('caret-active');
        field = "user_id";
    }

    usersList.sort(sort_by(field, reverse));
    populateTable();
}

function populateTable() {
    table.innerHTML = '';
    if (usersList.length > 0) {
        rows = table.getElementsByTagName("TR");
        for (let data of usersList) {
            let row = table.insertRow(-1);

            let user_id = row.insertCell(0);
            user_id.innerHTML = data.user_id;

            let user_name = row.insertCell(1);
            user_name.innerHTML = data.user_name;

            let user_lastname = row.insertCell(2);
            user_lastname.innerHTML = data.user_lastname;

            let email = row.insertCell(3);
            email.innerHTML = data.email;

            let phone = row.insertCell(4);
            phone.innerHTML = data.phone;

            let statusName = (data.status === "1") ? "Activo" : "Inactivo";
            let statusClass = (statusName === "Activo") ? "status-active" : "status-inactive";
            let roleName = roleList.find(role => role.role_id === data.role_id);

            let roleUser = row.insertCell(5);
            roleUser.innerHTML = roleName.role_name;

            let statusUser = row.insertCell(6);
            statusUser.innerHTML = `<span class="status ${statusClass}">${statusName}</span>`;

            let actions = row.insertCell(7);
            actions.innerHTML = `
                            <div class="table-buttons">
                                <button id="open-modal" class="btn btn-edit" onclick="modalEdit('${data.user_id}')">
                                    <i class="fa-regular fa-pen-to-square"></i> Editar
                                </button>
                                <button class="btn btn-delete" onclick="deleteUser('${data.user_id}')">
                                    <i class="fa-regular fa-trash-can"></i> Eliminar
                                </button>
                            </div>
                            `;
        }

        filterTable();
    }
}

function filterRegisters(){
    pager = "";
    pager = new Pager('tableUsers', parseInt(numRegisters.value));
    pager.init();
    pager.showPageNav('pager', 'pageNavPosition');
    pager.showPage(1);
}

