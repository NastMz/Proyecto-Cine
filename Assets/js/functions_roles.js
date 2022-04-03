// Get the modal
let modal = "";

// Get the button that opens the modal
let btn = document.getElementById("open-modal");

// Get the <span> element that closes the modal
let span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal
function openModal(modalId) {
    modal = document.getElementById(modalId);
    setTimeout(() => {
        modal.style.display = "block";
    }, 100);
}

// When the user clicks on <span> (x), close the modal
function closeModal(modalId) {
    modal = document.getElementById(modalId);
    setTimeout(() => {
        modal.style.display = "none";
    }, 100);
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target === modal) {
        modal.style.display = "none";
    }
}