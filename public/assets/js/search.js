document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('serviceSearch');
  const serviceCards = document.querySelectorAll('.service-card');

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const query = searchInput.value.toLowerCase();

      serviceCards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        if (title.includes(query)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }
});

document.addEventListener("DOMContentLoaded", function() {
  const searchInput = document.getElementById("serviceSearch");
  const minPriceInput = document.getElementById("min_price");
  const maxPriceInput = document.getElementById("max_price");
  const categorySelect = document.getElementById("category_filter");
  const serviceCards = document.querySelectorAll(".service-card");

  function applyFilters() {
    const searchValue = searchInput.value.toLowerCase();
    const minPrice = parseFloat(minPriceInput.value) || 0;
    const maxPrice = parseFloat(maxPriceInput.value) || Infinity;
    const selectedCategory = categorySelect.value;

    serviceCards.forEach(card => {
      const title = card.dataset.title.toLowerCase();
      const price = parseFloat(card.dataset.price);
      const category = card.dataset.category;

      const matchesSearch = title.includes(searchValue);
      const matchesPrice = price >= minPrice && price <= maxPrice;
      const matchesCategory = selectedCategory === "" || category === selectedCategory;

      if (matchesSearch && matchesPrice && matchesCategory) {
        card.style.display = "block";
      } else {
        card.style.display = "none";
      }
    });
  }

  [searchInput, minPriceInput, maxPriceInput, categorySelect].forEach(input => {
    input.addEventListener("input", applyFilters);
    input.addEventListener("change", applyFilters);
  });
});

document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("serviceSearch");
    const serviceCards = document.querySelectorAll(".service-card");
    const minPriceInput = document.getElementById("min_price");
    const maxPriceInput = document.getElementById("max_price");
    const orderSelect = document.getElementById("order");
    const categorySelect = document.getElementById("category");
    const filterForm = document.getElementById("filterForm");

    function filterServices() {
        const searchTerm = searchInput.value.toLowerCase();
        const minPrice = parseFloat(minPriceInput.value) || 0;
        const maxPrice = parseFloat(maxPriceInput.value) || Infinity;
        const selectedCategory = categorySelect.value;

        let servicesArray = Array.from(serviceCards);

        servicesArray.forEach(card => {
            const title = card.dataset.title.toLowerCase();
            const price = parseFloat(card.dataset.price);
            const category = card.dataset.category;

            const matchesSearch = title.includes(searchTerm);
            const matchesPrice = price >= minPrice && price <= maxPrice;
            const matchesCategory = !selectedCategory || category === selectedCategory;

            if (matchesSearch && matchesPrice && matchesCategory) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        });

        if (orderSelect.value) {
            servicesArray.sort((a, b) => {
                const priceA = parseFloat(a.dataset.price);
                const priceB = parseFloat(b.dataset.price);
                return orderSelect.value === "asc" ? priceA - priceB : priceB - priceA;
            });

            const grid = document.querySelector(".services-grid");
            servicesArray.forEach(card => grid.appendChild(card));
        }
    }

    searchInput.addEventListener("input", filterServices);
    minPriceInput.addEventListener("input", filterServices);
    maxPriceInput.addEventListener("input", filterServices);
    orderSelect.addEventListener("change", filterServices);
    categorySelect.addEventListener("change", filterServices);
    filterForm.addEventListener("submit", e => e.preventDefault());
});
