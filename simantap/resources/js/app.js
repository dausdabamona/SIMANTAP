import 'bootstrap';
import Swal from 'sweetalert2';
window.Swal = Swal;

// Dark mode
const savedTheme = localStorage.getItem('simantap-theme') || 'light';
document.documentElement.setAttribute('data-bs-theme', savedTheme);

window.toggleDarkMode = function () {
    const current = document.documentElement.getAttribute('data-bs-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('simantap-theme', next);
    const icon = document.getElementById('darkModeIcon');
    if (icon) {
        icon.className = next === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
};

// SweetAlert2 konfirmasi hapus
window.konfirmasiHapus = function (formId, nama = 'data ini') {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        html: `<span class="text-danger fw-semibold">${nama}</span> akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
};

// Notifikasi flash SweetAlert2
window.addEventListener('DOMContentLoaded', () => {
    const flash = document.getElementById('flash-data');
    if (flash) {
        const type = flash.dataset.type;
        const msg  = flash.dataset.message;
        if (msg) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type || 'success',
                title: msg,
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
            });
        }
    }

    // Sidebar toggle aktif berdasarkan URL
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
            const parent = link.closest('.collapse');
            if (parent) {
                parent.classList.add('show');
                const toggle = document.querySelector(`[data-bs-target="#${parent.id}"]`);
                if (toggle) toggle.setAttribute('aria-expanded', 'true');
            }
        }
    });
});
