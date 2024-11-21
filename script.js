function toggleForm(openBtnId, closeBtnId, formId) {
  const openFormBtn = document.getElementById(openBtnId);
  const closeFormBtn = document.getElementById(closeBtnId);
  const form = document.getElementById(formId);

  openFormBtn.addEventListener("click", function () {
    form.classList.add("active");
  });

  closeFormBtn.addEventListener("click", function () {
    form.classList.remove("active");
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const currentUrl = window.location.href;
  const menuLinks = document.querySelectorAll(".menu a");

  menuLinks.forEach((link) => {
    if (currentUrl.includes(link.getAttribute("href"))) {
      link.classList.add("active");
    }
  });
});
