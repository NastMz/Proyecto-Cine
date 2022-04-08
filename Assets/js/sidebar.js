let sidebar = document.getElementById('sidebar-nav');

function openNav() {
    sidebar.style.display = "block";
    setTimeout(function () {
        document.getElementById("mySidenav").style.width = "350px";
    }, 100);
}

/* Set the width of the side navigation to 0 */
function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
    setTimeout(function () {
        sidebar.style.display = "none";
    }, 500);
}

sidebar.addEventListener('click', function (e) {
    if (e.target === sidebar) {
        closeNav();
    }
});