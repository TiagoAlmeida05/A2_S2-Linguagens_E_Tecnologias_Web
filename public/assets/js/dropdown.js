document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.querySelector(".dropdown-toggle");
  const menu = document.querySelector(".dropdown-menu");

  toggle.addEventListener("click", () => {
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
  });

  window.addEventListener("click", (e) => {
    if (!e.target.matches('.dropdown-toggle')) {
      menu.style.display = "none";
    }
  });

  document.getElementById("apply-filters").addEventListener("click", () => {
    const selected = [];
    document.querySelectorAll(".dropdown-menu input[type=checkbox]:checked").forEach(checkbox => {
      selected.push(checkbox.value);
    });
    console.log("Selected values:", selected);
    menu.style.display = "none";
    // Do something with selected values (like filtering your services)
  });
});