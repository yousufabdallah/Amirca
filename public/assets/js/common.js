/* أدوات مشتركة */
window.Ameed = (function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  function toast(msg, type = 'success') {
    let c = document.querySelector('.toast-container');
    if (!c) { c = document.createElement('div'); c.className = 'toast-container position-fixed bottom-0 start-0 p-3'; document.body.appendChild(c); }
    const el = document.createElement('div');
    el.className = 'toast align-items-center border-0 text-bg-' + (type === 'error' ? 'danger' : type);
    el.setAttribute('role', 'alert');
    el.innerHTML = '<div class="d-flex"><div class="toast-body fw-semibold"></div><button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>';
    el.querySelector('.toast-body').textContent = msg;
    c.appendChild(el);
    const t = new bootstrap.Toast(el, { delay: 3500 }); t.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
  }
  async function request(url, opts = {}) {
    const headers = Object.assign({ 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, opts.headers || {});
    let body = opts.body;
    if (body && !(body instanceof FormData) && typeof body === 'object') { body = JSON.stringify(body); headers['Content-Type'] = 'application/json'; }
    const res = await fetch(url, { method: opts.method || 'GET', headers, body, credentials: 'same-origin' });
    let data = {};
    try { data = await res.json(); } catch (e) { }
    if (!res.ok) {
      const msg = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'حدث خطأ، حاول مرة أخرى.');
      throw new Error(msg);
    }
    return data;
  }
  function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

  document.addEventListener('submit', function (e) {
    const f = e.target;
    if (f.dataset.confirm && !confirm(f.dataset.confirm)) { e.preventDefault(); return; }
    if (f.dataset.ajax !== undefined) {
      e.preventDefault();
      const btn = f.querySelector('[type=submit]'); if (btn) btn.disabled = true;
      request(f.action, { method: 'POST', body: new FormData(f) })
        .then(d => { toast(d.message || 'تم الحفظ'); if (f.dataset.reload !== undefined) setTimeout(() => location.reload(), 600); })
        .catch(err => toast(err.message, 'error'))
        .finally(() => { if (btn) btn.disabled = false; });
      return;
    }
    const btn = f.querySelector('button[type=submit]:not([data-no-lock])');
    if (btn && !f.dataset.noLock) { setTimeout(() => { btn.disabled = true; btn.dataset.orig = btn.innerHTML; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جارٍ الحفظ...'; }, 0); }
  });
  window.addEventListener('pageshow', () => document.querySelectorAll('button[data-orig]').forEach(b => { b.disabled = false; b.innerHTML = b.dataset.orig; }));
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
  return { toast, request, esc, csrf };
})();
