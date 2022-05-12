const dropdownMenu = document.getElementById("myDropdown");
const dropdownBtn = document.getElementById("dropbtn");
const dropdownImage = document.getElementById("user-image");
const dropdownBackground = document.getElementById("dropdown-background");


function openDropdown() {
    if (dropdownBackground.classList.contains('toggle-background')) {
        dropdownBtn.parentElement.classList.toggle('toggle-span');
        dropdownImage.parentElement.classList.toggle('toggle-user');
        dropdownMenu.classList.toggle('show');
        dropdownBtn.classList.toggle('dropdown-transform');
        setTimeout(function () {
            dropdownBackground.classList.toggle('toggle-background');
        }, 500);
    } else {
        dropdownBackground.classList.toggle('toggle-background');
        setTimeout(function () {
            dropdownBtn.parentElement.classList.toggle('toggle-span');
            dropdownImage.parentElement.classList.toggle('toggle-user');
            dropdownMenu.classList.toggle('show');
            dropdownBtn.classList.toggle('dropdown-transform');
        }, 500);
    }
}

function closeDropdown() {
    if (dropdownMenu.classList.contains('show')) {
        dropdownMenu.classList.remove('show');
        dropdownBtn.classList.remove('dropdown-transform');
        dropdownBtn.parentElement.classList.remove('toggle-span');
        dropdownImage.parentElement.classList.remove('toggle-user');
        setTimeout(function () {
            dropdownBackground.classList.remove('toggle-background');
        }, 500);
    }
}

dropdownBackground.addEventListener('click', closeDropdown);

