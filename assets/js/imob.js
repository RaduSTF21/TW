document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('properties-container');
  const messageDiv = document.getElementById('message');
  const applyBtn = document.getElementById('btnApplyFilters');
  const locateBtn = document.getElementById('btnLocate');
  const API_PATH = '/TW/public/api/properties.php';

  async function fetchProperties(filters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, val]) => {
      if (val !== undefined && val !== null && val !== '') {
        params.append(key, val);
      }
    });

    const url = `${API_PATH}?${params.toString()}`;
    messageDiv.textContent = 'Se încarcă...';
    try {
      const resp = await fetch(url, { credentials: 'same-origin' });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();
      renderProperties(data);
      messageDiv.textContent = '';
    } catch (err) {
      messageDiv.innerHTML = `<p class="error">Eroare: ${err.message}</p>`;
    }
  }

  function renderProperties(listings) {
    container.innerHTML = '';
    if (!Array.isArray(listings) || listings.length === 0) {
      container.innerHTML = '<p>Nu s-au găsit proprietăți.</p>';
      return;
    }
    listings.forEach(p => {
      const card = document.createElement('div');
      card.className = 'property-card';

      if (p.image_url) {
        const img = document.createElement('img');
        img.src = `/TW/public/image.php?id=${encodeURIComponent(p.id)}`;
        img.alt = p.title || 'Proprietate';
        img.loading = 'lazy';
        card.appendChild(img);
      }

      const title = document.createElement('h3');
      title.textContent = p.title || 'Fără titlu';
      card.appendChild(title);

      const price = document.createElement('p');
      price.textContent = `Preț: ${p.price ? p.price + ' €' : 'N/A'}`;
      card.appendChild(price);

      if (p.rooms) {
        const rooms = document.createElement('p');
        rooms.textContent = `Camere: ${p.rooms}`;
        card.appendChild(rooms);
      }

      const link = document.createElement('a');
      link.href = `/TW/public/detail.php?id=${encodeURIComponent(p.id)}`;
      link.textContent = 'Vezi detaliu';
      card.appendChild(link);

      container.appendChild(card);
    });
  }

  applyBtn.addEventListener('click', () => {
    const filters = {
      transaction: document.getElementById('filterTransaction').value,
      price_max: document.getElementById('filterPriceMax').value,
      rooms_min: document.getElementById('filterRoomsMin').value
    };
    fetchProperties(filters);
  });

  locateBtn.addEventListener('click', () => {
    if (!navigator.geolocation) {
      alert('Geolocation nu e suportat de browser');
      return;
    }
    navigator.geolocation.getCurrentPosition(pos => {
      const filters = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
        radius: 5
      };
      messageDiv.textContent = 'Căut proprietăți în apropiere...';
      fetchProperties(filters);
    }, err => alert('Eroare geolocație: ' + err.message));
  });

  // Initial load
  fetchProperties({});
});