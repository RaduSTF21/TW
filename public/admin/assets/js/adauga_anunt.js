// admin/assets/js/adauga_anunt.js

document.addEventListener('DOMContentLoaded', () => {
  const form           = document.getElementById('anuntForm');
  const messageDiv     = document.getElementById('message');
  const ENDPOINT       = '/TW/public/adauga_anunt.php';
  const REDIRECT_URL   = '/TW/public/imob.php';

  // Geolocation elements
  const btnUseLocation = document.getElementById('btnUseLocation');
  const latInput       = document.getElementById('latitude');
  const lngInput       = document.getElementById('longitude');

  async function fetchJSON(url, options = {}) {
    const resp = await fetch(url, options);
    if (!resp.ok) {
      const text = await resp.text();
      throw new Error('HTTP ' + resp.status + ': ' + text);
    }
    return resp.json();
  }

  async function loadCsrfToken() {
    try {
      const data = await fetchJSON(ENDPOINT + '?action=get_csrf');
      document.getElementById('csrf_token').value = data.csrf_token;
    } catch (err) {
      console.error('CSRF load error:', err);
      messageDiv.innerHTML = '<p class="error">Nu s-a putut obține CSRF token.</p>';
    }
  }

  async function loadOptions() {
    try {
      const data = await fetchJSON(ENDPOINT + '?action=options');
      const trSel = document.getElementById('transaction_type');
      trSel.innerHTML = '<option value="">-- alege tip tranzacție --</option>';
      data.transaction_types.forEach(tt => {
        const opt = document.createElement('option');
        opt.value = tt.name;
        opt.textContent = tt.name.charAt(0).toUpperCase() + tt.name.slice(1);
        trSel.appendChild(opt);
      });
      const ptSel = document.getElementById('property_type');
      ptSel.innerHTML = '<option value="">-- alege tip proprietate --</option>';
      data.property_types.forEach(pt => {
        const opt = document.createElement('option');
        opt.value = pt.name;
        opt.textContent = pt.name.charAt(0).toUpperCase() + pt.name.slice(1);
        ptSel.appendChild(opt);
      });
      const amCont = document.getElementById('amenitiesContainer');
      amCont.innerHTML = '';
      data.amenities.forEach(am => {
        const label = document.createElement('label');
        const inp = document.createElement('input');
        inp.type = 'checkbox';
        inp.name = 'amenities[]';
        inp.value = am.id;
        label.appendChild(inp);
        label.append(' ' + am.name);
        amCont.appendChild(label);
      });
      const rkCont = document.getElementById('risksContainer');
      rkCont.innerHTML = '';
      data.risks.forEach(rk => {
        const label = document.createElement('label');
        const inp = document.createElement('input');
        inp.type = 'checkbox';
        inp.name = 'risks[]';
        inp.value = rk.id;
        label.appendChild(inp);
        label.append(' ' + rk.name);
        rkCont.appendChild(label);
      });
    } catch (err) {
      console.error('Options load error:', err);
      messageDiv.innerHTML = '<p class="error">Nu s-au putut încărca opțiunile formularului.</p>';
    }
  }

  // Geolocation button logic
  if (btnUseLocation && navigator.geolocation) {
    btnUseLocation.addEventListener('click', () => {
      messageDiv.textContent = 'Determin poziția…';
      navigator.geolocation.getCurrentPosition(
        pos => {
          latInput.value = pos.coords.latitude.toFixed(6);
          lngInput.value = pos.coords.longitude.toFixed(6);
          messageDiv.innerHTML = '<p class="success">Poziție setată!</p>';
        },
        err => {
          console.error('Geolocation error', err);
          messageDiv.innerHTML = `<p class="error">Eroare poziție: ${err.message}</p>`;
        },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    });
  } else if (btnUseLocation) {
    btnUseLocation.disabled = true;
    btnUseLocation.textContent = 'Geolocation indisponibil';
  }

  if (form) {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      messageDiv.innerHTML = '';
      const formData = new FormData(form);
      try {
        const data = await fetchJSON(ENDPOINT + '?action=submit', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });
        if (data.success) {
          messageDiv.innerHTML = `<p class="success">Anunț adăugat cu succes! ID: ${data.property_id}</p>`;
          setTimeout(() => window.location.href = REDIRECT_URL, 1500);
        } else {
          if (data.errors) {
            data.errors.forEach(err => {
              const p = document.createElement('p');
              p.textContent = err;
              p.className = 'error';
              messageDiv.appendChild(p);
            });
          } else {
            messageDiv.innerHTML = `<p class="error">${data.error || 'Eroare neașteptată'}</p>`;
          }
        }
      } catch (err) {
        messageDiv.innerHTML = `<p class="error">Eroare rețea: ${err.message}</p>`;
      }
    });
  }

  // Initial load
  loadCsrfToken();
  loadOptions();
});
