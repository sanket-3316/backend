import 'mdb-ui-kit/css/mdb.min.css';
import * as mdb from 'mdb-ui-kit';

import tinymce from 'tinymce/tinymce';

// theme
import 'tinymce/themes/silver';

// icons
import 'tinymce/icons/default';

// plugins
import 'tinymce/plugins/image';
import 'tinymce/plugins/code';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';

// make it global (VERY IMPORTANT)
window.mdb = mdb;

/* ===================================
   GLOBAL MODAL HANDLER
=================================== */
window.createModal = function (id, options = {}) {

    let element = document.getElementById(id);

    if (!element) {
        console.error(`Modal #${id} not found`);
        return null;
    }

    return new mdb.Modal(element, {
        backdrop: 'static',
        keyboard: false,
        ...options
    });
};

/* ===================================
   GLOBAL FORM ERROR HANDLER
=================================== */
window.clearFormErrors = function () {
    document.querySelectorAll('.text-danger').forEach(el => el.textContent = '');
    document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
};

/* ===================================
   AUTO CLEAR ON INPUT
=================================== */
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('form-control')) {
        e.target.classList.remove('is-invalid');

        let name = e.target.getAttribute('name');
        let errorEl = document.querySelector('.error-' + name);

        if (errorEl) errorEl.textContent = '';
    }
});


/* ===================================
   GLOBAL DATATABLE SEARCH DELAY
=================================== */
$(document).on('init.dt', function (e, settings) {

    let table = new $.fn.dataTable.Api(settings);
    let input = $(table.table().container()).find('input');

    let delayTimer;

    input.off().on('keyup', function () {

        clearTimeout(delayTimer);

        let value = this.value;

        delayTimer = setTimeout(function () {
            table.search(value).draw();
        }, 2000);
    });

});
/* =========================
   GLOBAL LOADER
========================= */

window.showLoader = function () {
    $("#globalLoader").fadeIn(200);
};

window.hideLoader = function () {
    $("#globalLoader").fadeOut(200);
};


/* ===================================
   GLOBAL MDB TOAST
=================================== */
window.showToast = function (message, type = 'success') {

    let toastEl = document.getElementById('globalToast');
    let toastBody = document.getElementById('toastMessage');

    toastBody.innerText = message;

    // set color
    toastEl.classList.remove('bg-success', 'bg-danger');
    toastEl.classList.add(type === 'success' ? 'bg-success' : 'bg-danger');

    let toast = new mdb.Toast(toastEl);
    toast.show();
};

/* ===================================
   GLOBAL 12-HOUR DATE/TIME FORMATTER
   Site runs on Asia/Kolkata (see config/app.php) — stored timestamps are
   already IST wall-clock, so this only needs to force a 12-hour AM/PM
   display instead of relying on the browser's locale defaults (which can
   silently render 24-hour depending on the admin's OS/browser locale).
=================================== */
window.formatDateTime = function (value) {
    if (!value) return '—';

    let str = String(value).includes('T') ? value : value.replace(' ', 'T');
    let d = new Date(str);

    if (isNaN(d)) return value;

    return d.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

window.formatDate = function (value) {
    if (!value) return '—';

    let str = String(value).includes('T') ? value : value.replace(' ', 'T');
    let d = new Date(str);

    if (isNaN(d)) return value;

    return d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

/* ===================================
   GLOBAL AJAX SETUP (CSRF)
=================================== */
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});