let dropdownMenu = document.getElementById("myDropdown");
let dropdownBtn = document.getElementById("dropbtn");
let dropdownImage = document.getElementById("user-image");

function openDropdown() {
    dropdownBtn.parentElement.classList.toggle('toggle-span');
    dropdownImage.parentElement.classList.toggle('toggle-user');
    dropdownMenu.classList.toggle('show');
    dropdownBtn.classList.toggle('dropdown-transform');

}

function closeDropdown() {
    if (dropdownMenu.classList.contains('show')){
        dropdownMenu.classList.remove('show');
        dropdownBtn.classList.remove('dropdown-transform');
        dropdownBtn.parentElement.classList.remove('toggle-span');
        dropdownImage.parentElement.classList.remove('toggle-user');
    }
}

window.onclick = function(event) {
    if (event.target !== dropdownBtn && event.target !== dropdownImage && event.target !== dropdownImage.parentElement) {
        closeDropdown();
    }
}

