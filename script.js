document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('property-container');
  const searchBtn = document.getElementById('search-btn');

  // Fetch & render listings
  function loadProperties(filters = {}) {
    container.innerHTML = '<p>Loading…</p>';
    const qs = new URLSearchParams(filters).toString();
    fetch(`api/properties.php${qs ? '?' + qs : ''}`)
      .then(res => {
        if (!res.ok) throw new Error(res.status);
        return res.json();
      })
      .then(data => {
        container.innerHTML = '';
        if (!data.length) {
          container.innerHTML = '<p>Niciun rezultat găsit.</p>';
          return;
        }
        data.forEach(p => container.appendChild(createCard(p)));
      })
      .catch(err => {
        console.error('Error loading properties:', err);
        container.innerHTML = '<p style="color:red;">Eroare la încărcarea anunțurilor.</p>';
      });
  }

  // Build a card element
  function createCard(p) {
    const card = document.createElement('div');
    card.className = 'property-card';

    // Image
    const img = document.createElement('img');
    img.className = 'property-image';
    img.src = p.image_url || 'placeholder.jpg';
    img.alt = p.title;

    // Info container
    const info = document.createElement('div');
    info.className = 'property-info';

    const title = document.createElement('h3');
    title.textContent = p.title;

    const price = document.createElement('div');
    price.className = 'price';
    price.textContent = `€${p.price}`;

    const btn = document.createElement('a');
    btn.className = 'details-btn';
    btn.href = `detail.html?id=${p.id}`;
    btn.textContent = 'Detalii';

    info.append(title, price, btn);
    card.append(img, info);
    return card;
  }

  // On click “Caută”
  searchBtn.addEventListener('click', () => {
    const filters = {
      transaction: document.getElementById('transaction-type').value,
      property_type: document.getElementById('property-type').value,
      rooms: document.getElementById('rooms').value,
      price_max: document.getElementById('price').value
    };
    loadProperties(filters);
  });

  // Initial load: all listings
  loadProperties();
});
