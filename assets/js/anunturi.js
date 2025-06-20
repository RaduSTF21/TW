document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('announcements-container');
  const countDisplay = document.getElementById('results-count');
  const applyBtn = document.getElementById('apply-filters');

  const API_PATH = 'api/properties.php';  // Relative to public/

  async function loadAnnouncements(filters = {}) {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([k, v]) => {
      if (v) params.append(k, v);
    });
    const url = `${API_PATH}?${params.toString()}`;

    try {
      const resp = await fetch(url, { credentials: 'same-origin' });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();
      renderAnnouncements(data);
      countDisplay.textContent = data.length;
    } catch (err) {
      container.innerHTML = `<p class="error">Eroare la încărcarea anunțurilor: ${err.message}</p>`;
    }
  }

  function renderAnnouncements(listings) {
    container.innerHTML = '';
    listings.forEach(p => {
      const ann = document.createElement('div');
      ann.className = 'announcement';

      const img = document.createElement('img');
      img.src = p.image_url;
      img.alt = p.title;
      ann.appendChild(img);

      const info = document.createElement('div');
      info.className = 'announcement-info';

      const title = document.createElement('h3');
      title.textContent = p.title;
      info.appendChild(title);

      const price = document.createElement('p');
      price.textContent = `Preț: ${p.price}`;
      info.appendChild(price);

      if (p.rooms) {
        const rooms = document.createElement('p');
        rooms.textContent = `Camere: ${p.rooms}`;
        info.appendChild(rooms);
      }

      ann.appendChild(info);
      container.appendChild(ann);
    });
  }

  applyBtn.addEventListener('click', () => {
    const filters = {
      transaction:  document.getElementById('transaction-type').value,
      property_type: document.getElementById('property-type').value,
      rooms:         document.getElementById('rooms').value,
      price_max:     document.getElementById('price-max').value
    };
    loadAnnouncements(filters);
  });

  // Initial load
  loadAnnouncements({});
});
