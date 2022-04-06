document.addEventListener("DOMContentLoaded", returnRows);
document.addEventListener("DOMContentLoaded", loadRoles);

let numRegisters = document.getElementById("numRegisters");

numRegisters.addEventListener("input", filter);
// Get the modal
let modal = document.getElementById("modal");

// Get the <span> element that closes the modal
let span = document.getElementsByClassName("close")[0];

let userName = document.getElementById("txtUserName");
let userLastame = document.getElementById("txtUserLastname");
let userId = document.getElementById("txtUserId");
let userPhone = document.getElementById("txtPhone");
let userEmail = document.getElementById("txtEmail");
let password = document.getElementById("txtPassword");
let roleCode = document.getElementById("role");
let status = document.getElementById("status");
let btnForm = document.getElementById("btnActionForm");

function resetForm() {
    userName.value = "";
    userLastame.value = "";
    userId.value = "";
    userPhone.value = "";
    userEmail.value = "";
    password.value = "";
    roleCode.value = 1;
    status.value = 1;
    btnForm.id = "btnActionForm";
    userId.disabled = false;
}

// When the user clicks on the button, open the modal
function openModal() {
    resetForm();
    modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
function closeModal() {
    resetForm();
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function (event) {
    if (event.target === modal) {
        resetForm();
        modal.style.display = "none";
    }
}

async function cargarUrl(url) {
    let response = await fetch(url, {
        method: "GET", headers: {
            "Content-Type": "application/json",
        },
    });
    return response.text();
}

async function cargarJson(controller, method) {
    let responseText = await cargarUrl(BASE_URL + `${controller}/${method}`);
    return JSON.parse(responseText);
}

async function loadRoles() {
    let roleList = await cargarJson("roles", `findAll`);
    let options = [];
    roleList.map((role) => {
        options.push(`<option value="${role.role_code}">${role.role_name}</option>`);
    });
    for (let option of options) {
        roleCode.innerHTML += option;
    }
}

let table = document.getElementById("tableUsersBody");

let rows = [];

async function returnRows() {
    table.innerHTML = "";
    rows = [];
    let usersList = await cargarJson("users", "findAll");
    usersList.map((user) => {
        let statusUser = (user.status === "1") ? "Activo" : "Inactivo";
        let statusClass = (statusUser === "Activo") ? "status-active" : "status-inactive";
        rows.push(` <tr>
                        <td data-label="userId">${user.user_id}</td>
                        <td data-label="userName">${user.user_name + " "}${user.user_lastname}</td>
                        <td data-label="userEmail">${user.email}</td>
                        <td data-label="userPhone">${user.phone}</td>
                        <td data-label="userRole">${user.role_code}</td>
                        <td data-label="status" ><span class="status ${statusClass}">${statusUser}</span></td>
                        <td>
                            <button id="open-modal" class="btn btn-edit" onclick="modalEdit('${user.user_id}')">
                                <i class="fa-regular fa-pen-to-square"></i> Editar
                            </button>
                            <button class="btn btn-delete" onclick="deleteUser('${user.user_id}')">
                                <i class="fa-regular fa-trash-can"></i> Eliminar
                            </button>
                        </td>
                  </tr>
            `);
    });
    let registers = rows.slice(0, numRegisters.value);
    for (let register of registers) {
        table.innerHTML += register;
    }
}

function filter() {
    table.innerHTML = "";
    let registers = rows.slice(0, numRegisters.value);
    for (let register of registers) {
        table.innerHTML += register;
    }
}

async function modalEdit(id) {
    let user = await cargarJson("users", `find/${id}`);

    userName.value = user.user_name;
    userLastame.value = user.user_lastname;
    userId.value = user.user_id;
    userPhone.value = user.phone;
    userEmail.value = user.email;
    password.value = user.password;
    roleCode.value = user.role_code;
    status.value = parseInt(user.status);
    btnForm.id = "btnEditForm";

    userId.disabled = true;

    modal.style.display = "block";

}

// Metodos del crud
function sendRequest() {
    if (btnForm.id === "btnActionForm") {
        save();
    } else if (btnForm.id === "btnEditForm") {

    }
}


async function save() {
    let formData = new FormData();
    formData.append("user_id", userId.value);
    formData.append("user_name", userName.value);
    formData.append("user_lastname", userLastame.value);
    formData.append("role_code", roleCode.value);
    formData.append("status", status.value.toString());
    formData.append("email", userEmail.value);
    formData.append("password", password.value);
    formData.append("phone", userPhone.value);
    let url = BASE_URL + "users/save";
    let response = await fetch(url, {
        method: "POST", body: formData,
    });
    let json = JSON.parse(await response.text());
    alert(json);
    await returnRows();
}

async function deleteUser(id) {
    let url = BASE_URL + `users/delete/${id}`;
    let response = await fetch(url, {
        method: "DELETE", headers: {
            "Content-Type": "application/json",
        },
    });
    let json = JSON.parse(await response.text());
    alert(json);
    await returnRows();
}