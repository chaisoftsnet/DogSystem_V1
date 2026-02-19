// โหลดโหมดจาก localStorage
(function() {
    const saved = localStorage.getItem('themeMode');

    // Default = Dark Mode
    if (saved === 'light') {
        document.body.classList.add('light-mode');
        document.querySelector('#themeToggle i').classList.remove('fa-moon');
        document.querySelector('#themeToggle i').classList.add('fa-sun');
    }
})();

// ปุ่มสลับโหมด 🌙 → ☀
document.getElementById('themeToggle').onclick = function() {
    const icon = this.querySelector('i');
    const isLight = document.body.classList.toggle('light-mode');

    if (isLight) {
        // เปลี่ยนเป็น Light Mode
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        localStorage.setItem('themeMode', 'light');
    } else {
        // เปลี่ยนกลับ Dark Mode
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        localStorage.setItem('themeMode', 'dark');
    }
};
