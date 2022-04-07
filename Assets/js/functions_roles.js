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

let roleName = document.getElementById("txtRoleName");
let roleCode = document.getElementById("txtRoleCode");
let description = document.getElementById("txtDescription");

let btnForm = document.getElementById("btnActionForm");
let status = document.getElementById("status");
let tableColumns = document.getElementsByClassName('table-column');

let table = document.getElementById("tableRoleBody");
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
    document.getElementById("formTitle").innerHTML = "Nuevo Rol";
    document.getElementById("formMessage").innerHTML = "Ingresa la informacion del nuevo rol";
    roleName.value = "";
    roleCode.value = "";
    description.value = "";
    status.value = 1;
    btnForm.id = "btnActionForm";
}

let pager = new Pager('tableRoles', parseInt(numRegisters.value));

let rolesList = [];

async function load() {
    rolesList = await loadJson("roles", "findAll");
    rolesList.sort(sort_by("role_id", false));
    populateTable();
    filterRows = rows;

    pager.init();
    pager.showPageNav('pager', 'pageNavPosition');
    if (rolesList.length > 0) {
        pager.showPage(1);
    }
}

async function modalEdit(id) {
    let role = rolesList.find(findRole => findRole.role_id === id);

    document.getElementById("formTitle").innerHTML = "Editar Rol";
    document.getElementById("formMessage").innerHTML = "Ingresa la nueva informacion del rol";

    roleName.value = role.role_name;
    roleCode.value = role.role_id;
    description.value = role.description;
    status.value = role.status;
    btnForm.id = "btnEditForm";

    roleCode.disabled = true;

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
                case "role_id":
                    fields.push("Codigo");
                    break;
                case "role_name":
                    fields.push("Nombre");
                    break;
                case "description":
                    fields.push("Descripcion");
                    break;
                case "status":
                    fields.push("Estado");
                    break;
            }
        });
    }

    let string = fields.toString();
    string = string.replaceAll(",", ", ");

    switch (json.code) {
        case 200:
            Swal.fire({
                title: 'Buen trabajo!',
                text: `${json.message}`,
                icon: 'success',
                confirmButtonText: 'Confirmar',
                confirmButtonColor: '#1c508d'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    rolesList = await loadJson("roles", "findAll");
                    rolesList.sort(sort_by("role_id", false));
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
                title: 'Algo salio mal!',
                text: `${json.message}`,
                icon: 'error',
                confirmButtonText: 'Confirmar',
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
    formData.append("role_name", roleName.value);
    formData.append("description", description.value);
    formData.append("status", status.value.toString());
    let url = BASE_URL + "roles/save";
    let response = await fetch(url, {
        method: "POST", body: formData,
    });

    let json = JSON.parse(await response.text());

    await alertModal(json);

}

async function deleteRole(id) {
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
            let url = BASE_URL + `roles/delete/${id}`;
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
    formData.append("role_id", roleCode.value);
    formData.append("role_name", roleName.value);
    formData.append("description", description.value);
    formData.append("status", status.value.toString());
    let url = BASE_URL + "roles/update";
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

    clear(element, rolesList);

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
        rolesList.sort(sort_by("role_id", false));
    }

    rolesList.sort(sort_by(field, reverse));
    populateTable();
}

function populateTable() {
    table.innerHTML = '';
    if (rolesList.length > 0) {
        rows = table.getElementsByTagName("TR");
        for (let data of rolesList) {
            let row = table.insertRow(-1);

            let role_id = row.insertCell(0);
            role_id.innerHTML = data.role_id;

            let role_name = row.insertCell(1);
            role_name.innerHTML = data.role_name;

            let role_description = row.insertCell(2);
            role_description.innerHTML = data.description;

            let statusName = (data.status === "1") ? "Activo" : "Inactivo";
            let statusClass = (statusName === "Activo") ? "status-active" : "status-inactive";

            let statusRole = row.insertCell(3);
            statusRole.innerHTML = `<span class="status ${statusClass}">${statusName}</span>`;

            let actions = row.insertCell(4);
            actions.innerHTML = `
                            <div class="table-buttons">
                                <button id="open-modal" class="btn btn-permission" onclick="#">
                                    <i class="fa fa-key"></i> Permisos
                                </button>
                                <button id="open-modal" class="btn btn-edit" onclick="modalEdit(${data.role_id})">
                                    <i class="fa-regular fa-pen-to-square"></i> Editar
                                </button>
                                <button class="btn btn-delete" onclick="deleteRole('${data.role_id}')">
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
    pager = new Pager('tableRoles', parseInt(numRegisters.value));
    pager.init();
    pager.showPageNav('pager', 'pageNavPosition');
    pager.showPage(1);
}

