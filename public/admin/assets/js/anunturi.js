// assets/js/anunturi.js

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('announcements-container');
  const countDisplay = document.getElementById('results-count');
  const applyBtn = document.getElementById('apply-filters');

  const transactionSelect = document.getElementById('filter-transaction');
  const propertyTypeSelect = document.getElementById('filter-property-type');
  const roomsMinInput = document.getElementById('filter-rooms-min');
  const priceMaxInput = document.getElementById('filter-price-max');

  // Endpoint API absolut
  const API_PATH = '/TW/public/api/properties.php';

  // Încarcă opțiuni pentru filtre
  async function loadFilterOptions() {
    try {
      const resp = await fetch(API_PATH + '?action=filters', { credentials: 'same-origin' });
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      const data = await resp.json();
      // Populate tranzacții
      if (transactionSelect && data.transaction_types) {
        data.transaction_types.forEach(tt => {
          const opt = document.createElement('option');
          opt.value = tt.name;
          opt.textContent = tt.name.charAt(0).toUpperCase() + tt.name.slice(1);
          transactionSelect.appendChild(opt);
        });
      }
      // Populate tipuri proprietate
      if (propertyTypeSelect && data.property_types) {
        data.property_types.forEach(pt => {
          const opt = document.createElement('option');
          opt.value = pt.name;
          opt.textContent = pt.name.charAt(0).toUpperCase() + pt.name.slice(1);
          propertyTypeSelect.appendChild(opt);
        });
      }
    } catch (err) {
      console.error('Error loading filter options:', err);
      // se poate păstra bara de filtre cu doar "Toate"
    }
  }

  // Încarcă anunțuri cu filtre
  async function loadAnnouncements(filters = {}) {
    const params = new URLSearchParams();
    if (filters.transaction) params.append('transaction', filters.transaction);
    if (filters.property_type) params.append('property_type', filters.property_type);
    if (filters.rooms_min) params.append('rooms_min', filters.rooms_min);
    if (filters.price_max) params.append('price_max', filters.price_max);

    const url = API_PATH + '?' + params.toString();
    try {
      const resp = await fetch(url, { credentials: 'same-origin' });
      if (!resp.ok) {
        const text = await resp.text();
        throw new Error('HTTP ' + resp.status + ': ' + text);
      }
      const listings = await resp.json();
      renderAnnouncements(listings);
    } catch (err) {
      console.error('Error loading announcements:', err);
      container.innerHTML = '<p class="error">Nu s-au putut încărca anunțurile.</p>';
      countDisplay.textContent = '';
    }
  }

  function renderAnnouncements(listings) {
    container.innerHTML = '';
    if (!Array.isArray(listings) || listings.length === 0) {
      container.innerHTML = '<p>Nu există anunțuri de afișat.</p>';
      countDisplay.textContent = '0 anunțuri';
      return;
    }
    countDisplay.textContent = `${listings.length} anunțuri găsite`;
    listings.forEach(item => {
      const card = document.createElement('div');
      card.className = 'announcement-card';

      if(item.image){
        const img = document.createElement('img');
        img.src = '/TW/public/uploads/' + encodeURIComponent(item.image);
        img.alt = item.title; 
        img.loading = 'lazy'; // Încărcare lazy pentru performanță
        img.style.width = '100%'; // Asigură că imaginea ocupă lățimea cardului
        img.style.height = 'auto'; // Păstrează proporțiile imaginii
        img.style.display = 'block'; // Asigură că imaginea este afișată ca bloc

        card.appendChild(img);
      }

      const h2 = document.createElement('h2');
      h2.textContent = item.title;
      card.appendChild(h2);

      const pType = document.createElement('p');
      pType.textContent = `Tip: ${capitalize(item.transaction_type)} - ${capitalize(item.property_type)}`;
      card.appendChild(pType);

      const pRooms = document.createElement('p');
      pRooms.textContent = `Camere: ${item.rooms}`;
      card.appendChild(pRooms);

      const pPrice = document.createElement('p');
      pPrice.className = 'price';
      pPrice.textContent = `Preț: ${formatPrice(item.price)}`;
      card.appendChild(pPrice);

      const pDate = document.createElement('p');
      pDate.textContent = `Data: ${new Date(item.created_at).toLocaleDateString('ro-RO')}`;
      card.appendChild(pDate);

      const detailsLink = document.createElement('a');
      detailsLink.href = `detail.php?id=${encodeURIComponent(item.id)}`;
      detailsLink.className = 'details-btn';
      detailsLink.textContent = 'Vezi detalii';
      card.appendChild(detailsLink);

      container.appendChild(card);
    });
  }

  function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
  function formatPrice(val) {
    const num = parseFloat(val);
    if (isNaN(num)) return val;
    return num.toLocaleString('ro-RO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
  }

  if (applyBtn) {
    applyBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const filters = {
        transaction: transactionSelect.value,
        property_type: propertyTypeSelect.value,
        rooms_min: roomsMinInput.value,
        price_max: priceMaxInput.value
      };
      loadAnnouncements(filters);
    });
  }

  // Inițial
  loadFilterOptions().then(() => {
    loadAnnouncements({});
  });
});
