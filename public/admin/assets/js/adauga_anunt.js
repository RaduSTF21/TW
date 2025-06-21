
// public/js/adauga_anunt.js

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('anuntForm');
  const messageDiv = document.getElementById('message');
  // Endpoint absolut către PHP
  const ENDPOINT = '/TW/public/adauga_anunt.php';
  // URL pentru redirect după succes
  const REDIRECT_URL = '/TW/public/imob.php';

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
      if (data.csrf_token) {
        const inp = document.getElementById('csrf_token');
        if (inp) inp.value = data.csrf_token;
      } else {
        throw new Error('Token lipsă');
      }
    } catch (err) {
      console.error('CSRF load error:', err);
      messageDiv.innerHTML = '<p class="error">Nu s-a putut obține CSRF token.</p>';
    }
  }

  async function loadOptions() {
    try {
      const data = await fetchJSON(ENDPOINT + '?action=options');
      // transaction_types
      const trSel = document.getElementById('transaction_type');
      if (trSel) {
        trSel.innerHTML = '<option value="">-- alege tip tranzacție --</option>';
        data.transaction_types.forEach(tt => {
          const opt = document.createElement('option');
          opt.value = tt.name;
          opt.textContent = tt.name.charAt(0).toUpperCase() + tt.name.slice(1);
          trSel.appendChild(opt);
        });
      }
      // property_types
      const ptSel = document.getElementById('property_type');
      if (ptSel) {
        ptSel.innerHTML = '<option value="">-- alege tip proprietate --</option>';
        data.property_types.forEach(pt => {
          const opt = document.createElement('option');
          opt.value = pt.name;
          opt.textContent = pt.name.charAt(0).toUpperCase() + pt.name.slice(1);
          ptSel.appendChild(opt);
        });
      }
      // amenities
      const amCont = document.getElementById('amenitiesContainer');
      if (amCont) {
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
      }
      // risks
      const rkCont = document.getElementById('risksContainer');
      if (rkCont) {
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
      }
    } catch (err) {
      console.error('Options load error:', err);
      messageDiv.innerHTML = '<p class="error">Nu s-au putut încărca opțiunile formularului.</p>';
    }
  }

  if (form) {
    form.addEventListener('submit', async function(e) {
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
          // Afișează mesaj de succes
          messageDiv.innerHTML = '<p class="success">Anunț adăugat cu succes! ID: ' + data.property_id + '</p>';
          // Redirect către imob.php după 1.5s:
          setTimeout(() => {
            window.location.href = REDIRECT_URL;
          }, 1500);
        } else {
          if (data.errors) {
            data.errors.forEach(err => {
              const p = document.createElement('p');
              p.textContent = err;
              p.className = 'error';
              messageDiv.appendChild(p);
            });
          } else if (data.error) {
            messageDiv.innerHTML = '<p class="error">' + data.error + '</p>';
          } else {
            messageDiv.innerHTML = '<p class="error">Eroare neașteptată</p>';
          }
        }
      } catch (err) {
        messageDiv.innerHTML = '<p class="error">Eroare rețea: ' + err.message + '</p>';
      }
    });
  }

  // Inițial
  loadCsrfToken();
  loadOptions();
});