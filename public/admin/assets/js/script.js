// admin/assets/js/script.js

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('property-container');
  const searchBtn = document.getElementById('search-btn');

  // Turn the container into a grid
  container.classList.add('properties-grid');

  // Gather filter controls
  const transactionSelect = document.getElementById('transaction-type');
  const propertyTypeSelect = document.getElementById('property-type');
  const roomsSelect = document.getElementById('rooms');
  const priceInput = document.getElementById('price');

  // Your API endpoint
  const API_URL = '/TW/public/api/properties.php';

  // Fetch & render with current filters
  async function loadProperties() {
    const params = new URLSearchParams();
    if (transactionSelect.value)   params.append('transaction',   transactionSelect.value);
    if (propertyTypeSelect.value)  params.append('property_type', propertyTypeSelect.value);
    if (roomsSelect.value)         params.append('rooms_min',      roomsSelect.value);
    if (priceInput.value)          params.append('price_max',      priceInput.value);

    try {
      const res = await fetch(`${API_URL}?${params}`, { credentials: 'same-origin' });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const listings = await res.json();
      renderProperties(listings);
    } catch (err) {
      console.error('Error loading properties:', err);
      container.innerHTML = '<p class="error">Nu s-au putut încărca proprietățile.</p>';
    }
  }

  function renderProperties(listings) {
    container.innerHTML = '';
    if (!Array.isArray(listings) || listings.length === 0) {
      container.innerHTML = '<p>Nu există proprietăți de afișat.</p>';
      return;
    }

    listings.forEach(item => {
      // Card wrapper
      const card = document.createElement('div');
      card.className = 'property-card';

      // 1) Image section
      const imgWrap = document.createElement('div');
      imgWrap.className = 'property-image';
      if (item.image) {
        const img = document.createElement('img');
        img.src     = `/TW/public/uploads/${encodeURIComponent(item.image)}`;
        img.alt     = item.title;
        img.loading = 'lazy';
        imgWrap.appendChild(img);
      } else {
        imgWrap.textContent = 'Fără imagine';
      }
      card.appendChild(imgWrap);

      // 2) Info section
      const info = document.createElement('div');
      info.className = 'property-info';

      // Title
      const h3 = document.createElement('h3');
      h3.textContent = item.title;
      info.appendChild(h3);

      // Price
      const pPrice = document.createElement('p');
      pPrice.className = 'price';
      pPrice.textContent = `€${formatPrice(item.price)}`;
      info.appendChild(pPrice);

      // Snippet (first 100 chars of description)
      const pSnip = document.createElement('p');
      pSnip.className = 'snippet';
      pSnip.textContent = item.description
        ? item.description.slice(0, 100) + (item.description.length > 100 ? '…' : '')
        : '';
      info.appendChild(pSnip);

      // Details button
      const detailsLink = document.createElement('a');
      detailsLink.className = 'btn-details';
      detailsLink.href      = `detail.php?id=${encodeURIComponent(item.id)}`;
      detailsLink.textContent = 'Vezi detalii';
      info.appendChild(detailsLink);

      card.appendChild(info);

      container.appendChild(card);
    });
  }

  function formatPrice(val) {
    const num = parseFloat(val);
    if (isNaN(num)) return val;
    return num.toLocaleString('ro-RO', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  // Search button
  searchBtn.addEventListener('click', e => {
    e.preventDefault();
    loadProperties();
  });

  // Initial load
  loadProperties();
});
