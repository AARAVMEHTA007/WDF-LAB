(function () {
  "use strict";

  const THEME_KEY = "student_hub_theme";
  function applyTheme(theme, notify = false) {
    document.documentElement.setAttribute("data-theme", theme);
    if (document.body) {
      document.body.classList.toggle("dark-mode", theme === "dark");
    }
    localStorage.setItem(THEME_KEY, theme);
    document.querySelectorAll(".theme-btn-light, #theme-light-btn")
      .forEach(btn => btn.classList.toggle("active", theme === "light"));

    document.querySelectorAll(".theme-btn-dark, #theme-dark-btn")
      .forEach(btn => btn.classList.toggle("active", theme === "dark"));

    if (notify) {
      showToast(
        theme === "dark" ? "Switched to Dark Mode" : "Switched to Light Mode",
        theme === "dark" ? "fa-moon" : "fa-sun"
      );
    }
  }
  function showToast(message, icon = "fa-info-circle") {
    let box = document.querySelector(".toast-container");

    if (!box) {
      box = document.createElement("div");
      box.className = "toast-container";
      document.body.appendChild(box);
    }

    let toast = document.createElement("div");
    toast.className = "toast";
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    box.appendChild(toast);

    setTimeout(() => toast.classList.add("show"), 10);

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        if (toast.parentNode) {
          toast.remove();
        }
      }, 300);
    }, 3000);
  }
  window.showToast = showToast;

  let savedTheme = localStorage.getItem(THEME_KEY) || "light";
  document.documentElement.setAttribute("data-theme", savedTheme);

  document.addEventListener("DOMContentLoaded", function () {
    let theme = localStorage.getItem(THEME_KEY) || "light";
    applyTheme(theme);

    // Theme button click handlers
    document.querySelectorAll(".theme-btn-light, #theme-light-btn")
      .forEach(btn => {
        btn.onclick = (e) => {
          e.preventDefault();
          applyTheme("light", true);
        };
      });

    document.querySelectorAll(".theme-btn-dark, #theme-dark-btn")
      .forEach(btn => {
        btn.onclick = (e) => {
          e.preventDefault();
          applyTheme("dark", true);
        };
      });

    document.querySelectorAll("#theme-toggle, .theme-toggle-btn")
      .forEach(btn => {
        btn.onclick = (e) => {
          e.preventDefault();
          let next = localStorage.getItem(THEME_KEY) === "dark" ? "light" : "dark";
          applyTheme(next, true);
        };
      });

    document.querySelectorAll(".faq-question").forEach(question => {
      question.onclick = function () {
        this.classList.toggle("expanded");
        let answer = this.nextElementSibling;
        if (answer) {
          answer.classList.toggle("open");
        }
      };
    });
    let currentPage = location.pathname.split("/").pop() || "index.html";
    document.querySelectorAll("nav a").forEach(link => {
      let href = link.getAttribute("href");
      if (href && href.split("/").pop() === currentPage) {
        link.classList.add("nav-active");
      }
    });
    document.querySelectorAll("form").forEach(form => {
      form.onsubmit = function (e) {
        e.preventDefault();
        showToast("Form submitted successfully!", "fa-check-circle");
        this.reset();
      };
    });
  });
})();
