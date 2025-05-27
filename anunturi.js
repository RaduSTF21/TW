document.addEventListener('DOMContentLoaded', () => {
  const container     = document.getElementById('announcements-container');
  const countDisplay  = document.getElementById('results-count');
  const applyBtn      = document.getElementById('apply-filters');
  const sortSelect    = document.getElementById('sort-by');
  const pagination    = document.getElementById('pagination');

  // Build filter parameters object
  function readFilters() {
    return {
      transaction:  document.getElementById('transaction-type').value,
      property_type: document.getElementById('property-type').value,
      rooms:         document.getElementById('rooms').value,
      area:          document.getElementById('area').value,
      price_min:     document.getElementById('price-min').value,
      price_max:     document.getElementById('price-max').value,
      surface_min:   document.getElementById('surface-min').value,
      surface_max:   document.getElementById('surface-max').value,
      year:          document.getElementById('year').value,
      partitioning:  document.getElementById('partitioning').value,
      floor:         document.getElementById('floor').value,
      sort_by:       sortSelect.value,
      page:          1    // you can wire pagination next
    };
  }

  // Fetch + render listings
  async function loadAnnouncements(filters) {
    container.innerHTML = '<p>Loading…</p>';
    const qs = new URLSearchParams(filters).toString();
    try {
      const res  = await fetch(`api/properties.php?${qs}`);
      const data = await res.json();
      renderAnnouncements(data);
    } catch(err) {
      console.error(err);
      container.innerHTML = '<p>Eroare la încărcare.</p>';
    }
  }

  // Convert a property to an .announcement div
  function renderAnnouncements(data) {
    container.innerHTML = '';
    countDisplay.textContent = `${data.length} anunțuri găsite`;
    if (!data.length) return;

    data.forEach(p => {
      const ann = document.createElement('div');
      ann.className = 'announcement';

      // Header
      const hdr = document.createElement('div');
      hdr.className = 'announcement-header';

      // Image
      const imgWrap = document.createElement('div');
      imgWrap.className = 'announcement-image';
      const img = document.createElement('img');
      img.src = p.image_url || 'placeholder.jpg';
      img.alt = p.title;
      imgWrap.append(img);

      // Count (you’d fetch count from p.image_count if you provide it)
      if (p.image_count) {
        const cnt = document.createElement('div');
        cnt.className = 'image-count';
        cnt.textContent = `${p.image_count} fotografii`;
        imgWrap.append(cnt);
      }

      // Main info
      const info = document.createElement('div');
      info.className = 'announcement-main-info';

      info.innerHTML = `
        <div class="announcement-title">${p.title}</div>
        <div class="announcement-price">${p.price} € / ${p.transaction_type || 'lună'}</div>
        <div class="announcement-details">
          <div class="detail-item"><span>🛏️</span><span>${p.rooms} camere</span></div>
          <div class="detail-item"><span>📏</span><span>${p.surface || '--'} mp</span></div>
          <div class="detail-item"><span>🏢</span><span>Etaj ${p.floor || '-'}</span></div>
          <div class="detail-item"><span>🔄</span><span>${p.partitioning || ''}</span></div>
        </div>
        <div class="announcement-location"><span>📍</span> ${p.area || ''}, Iași</div>
      `;

      hdr.append(imgWrap, info);
      ann.append(hdr);

      // Description preview
      const desc = document.createElement('div');
      desc.className = 'announcement-description';
      const preview = document.createElement('div');
      preview.className = 'description-preview';
      preview.textContent = p.description;
      desc.append(preview);
      ann.append(desc);

      // Footer
      const foot = document.createElement('div');
      foot.className = 'announcement-footer';
      foot.innerHTML = `
        <div class="announcement-date">Publicat: ${new Date(p.created_at).toLocaleDateString('ro-RO')}</div>
        <div class="announcement-actions">
          <button class="action-button favorite-button">❤️ Salvează</button>
          <button class="action-button contact-button">Detalii</button>
        </div>
      `;
      // “Detalii” should redirect:
      foot.querySelector('.contact-button')
          .addEventListener('click', ()=> location.href = `detail.html?id=${p.id}`);
      ann.append(foot);

      container.append(ann);
    });
  }

  // Wire the Apply Filters button
  applyBtn.addEventListener('click', () => {
    const f = readFilters();
    loadAnnouncements(f);
  });

  // Initial load
  loadAnnouncements(readFilters());
});
