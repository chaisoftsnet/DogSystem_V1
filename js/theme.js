// 🌗 โหลดธีมจาก localStorage ตอนเปิดหน้า
document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    document.body.classList.add("dark-mode");
    setThemeIcon("moon");
  } else {
    document.body.classList.remove("dark-mode");
    setThemeIcon("sun");
  }
});

// 🔄 ฟังก์ชันสลับโหมด
function toggleDarkMode() {
  const body = document.body;
  const isDark = body.classList.toggle("dark-mode");
  localStorage.setItem("theme", isDark ? "dark" : "light");
  setThemeIcon(isDark ? "moon" : "sun");
}

// 🌓 เปลี่ยนไอคอนปุ่มตามโหมด
function setThemeIcon(mode) {
  const btn = document.querySelector(".toggle-theme i");
  if (btn) {
    btn.className = mode === "moon" ? "fa fa-sun" : "fa fa-moon";
  }
}
