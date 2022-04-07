async function loadUrl(url) {
    let response = await fetch(url, {
        method: "GET", headers: {
            "Content-Type": "application/json",
        },
    });
    return response.text();
}

async function loadJson(controller, method) {
    let responseText = await loadUrl(BASE_URL + `${controller}/${method}`);
    return JSON.parse(responseText);
}

function sendRequest(e) {
    if (btnForm.id === "btnActionForm") {
        save(e);
    } else if (btnForm.id === "btnEditForm") {
        update(e);
    }
}

function filterTable() {
    let filter = search.value.toUpperCase();
    let flag = false;
    filterRows = [];
    for (let row of rows) {
        let cells = row.getElementsByTagName("TD");
        for (let cell of cells) {
            if (cell.textContent.toUpperCase().indexOf(filter) > -1) {
                if (filter) {
                    cell.style.backgroundColor = '#f6e561';
                } else {
                    cell.style.backgroundColor = '';
                }

                flag = true;
            } else {
                cell.style.backgroundColor = '';
            }
        }

        if (flag) {
            row.style.display = "";
            filterRows.push(row);
        } else {
            row.style.display = "none";
        }

        flag = false;
    }
    filterRegisters();
}

function Pager(tableName, itemsPerPage) {
    'use strict';

    this.tableName = tableName;
    this.itemsPerPage = itemsPerPage;
    this.currentPage = 1;
    this.pages = 0;
    this.inited = false;

    this.showRecords = function (from, to) {
        let rowsTable = filterRows;

        for (let i = 0; i < rowsTable.length; i++) {
            if (i < from || i > to) {
                rowsTable[i].style.display = 'none';
            } else {
                rowsTable[i].style.display = '';
            }
        }
    };

    this.showPage = function (pageNumber) {
        if (!this.inited) {
            // Not initialized
            return;
        }

        let oldPageAnchor = document.getElementById('pg' + this.currentPage);
        oldPageAnchor.className = 'pg-normal';

        this.currentPage = pageNumber;
        let newPageAnchor = document.getElementById('pg' + this.currentPage);
        newPageAnchor.className = 'pg-selected';

        let from = (pageNumber - 1) * itemsPerPage;
        let to = from + itemsPerPage - 1;
        this.showRecords(from, to);

        let pgNext = document.querySelector('.pg-next');
        let pgPrev = document.querySelector('.pg-prev');

        if (this.currentPage === this.pages) {
            pgNext.style.display = 'none';
        } else {
            pgNext.style.display = '';
        }

        if (this.currentPage === 1) {
            pgPrev.style.display = 'none';
        } else {
            pgPrev.style.display = '';
        }
    };

    this.prev = function () {
        if (this.currentPage > 1) {
            this.showPage(this.currentPage - 1);
        }
    };

    this.next = function () {
        if (this.currentPage < this.pages) {
            this.showPage(this.currentPage + 1);
        }
    };

    this.init = function () {
        let rowsTable = filterRows;
        let records = rowsTable.length;

        this.pages = Math.ceil(records / itemsPerPage);
        this.inited = true;
    };

    this.showPageNav = function (pagerName, positionId) {
        if (!this.inited) {
            // Not initialized
            return;
        }

        let element = document.getElementById(positionId);
        let pagerHtml = '<span onclick="' + pagerName + '.prev();" class="pg-normal pg-prev">&#171;</span>';

        for (let page = 1; page <= this.pages; page++) {
            pagerHtml += '<span id="pg' + page + '" class="pg-normal pg-next" onclick="' + pagerName + '.showPage(' + page + ');">' + page + '</span>';
        }

        pagerHtml += '<span onclick="' + pagerName + '.next();" class="pg-normal pg-next">&#187;</span>';

        element.innerHTML = pagerHtml;
    };
}

const sort_by = (field, reverse, primer) => {

    const key = primer ? function (x) {
        return primer(x[field]);
    } : function (x) {
        return x[field];
    };

    reverse = !reverse ? 1 : -1;

    return function (a, b) {
        return a = key(a), b = key(b), reverse * ((a > b) - (b > a));
    };
};

function clear(element, list) {
    for (let column of tableColumns) {
        if (column.id !== "actions") {
            if (element !== column) {
                let caretU = column.getElementsByClassName('caret')[0].getElementsByClassName('fa-caret-up')[0];
                let caretD = column.getElementsByClassName('caret')[0].getElementsByClassName('fa-caret-down')[0];
                if (caretU.classList.contains('caret-active')) {
                    caretU.classList.remove('caret-active');
                }
                if (caretD.classList.contains('caret-active')) {
                    caretD.classList.remove('caret-active');
                }
                list.sort(sort_by("role_code", false));
                populateTable();
            }
        }
    }
}