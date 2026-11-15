(function () {
  const { request, toast, esc } = window.Ameed;
  // القائمة الجانبية للجوال
  const sb = document.querySelector('.sidebar');
  document.querySelectorAll('[data-toggle-sidebar]').forEach(b => b.addEventListener('click', () => sb.classList.toggle('open')));
  document.querySelector('.sidebar-backdrop')?.addEventListener('click', () => sb.classList.remove('open'));

  // البحث السريع الشامل
  const gs = document.getElementById('globalSearch');
  if (gs) {
    const box = document.getElementById('globalResults');
    let timer, idx = -1;
    gs.addEventListener('input', () => {
      clearTimeout(timer);
      const q = gs.value.trim();
      if (q.length < 2) { box.style.display = 'none'; return; }
      timer = setTimeout(async () => {
        try {
          const d = await request(gs.dataset.url + '?q=' + encodeURIComponent(q));
          idx = -1;
          box.innerHTML = d.results.length ? d.results.map(r => `<a href="${esc(r.url)}"><i class="bi ${esc(r.icon)}"></i><div class="flex-grow-1"><div class="fw-bold">${esc(r.title)}</div><small class="text-muted">${esc(r.sub)}</small></div><span class="badge bg-light text-dark">${esc(r.type)}</span></a>`).join('')
            : '<div class="p-3 text-muted text-center">لا توجد نتائج</div>';
          box.style.display = 'block';
        } catch (e) { }
      }, 250);
    });
    gs.addEventListener('keydown', e => {
      const items = box.querySelectorAll('a');
      if (!items.length) return;
      if (e.key === 'ArrowDown') { idx = Math.min(idx + 1, items.length - 1); e.preventDefault(); }
      else if (e.key === 'ArrowUp') { idx = Math.max(idx - 1, 0); e.preventDefault(); }
      else if (e.key === 'Enter' && idx >= 0) { location.href = items[idx].href; e.preventDefault(); return; }
      else return;
      items.forEach((a, i) => a.classList.toggle('active', i === idx));
    });
    document.addEventListener('click', e => { if (!e.target.closest('.global-search')) box.style.display = 'none'; });
    document.addEventListener('keydown', e => { if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) { e.preventDefault(); gs.focus(); } });
  }

  // الإشعارات
  const nb = document.getElementById('notifBtn');
  async function loadNotifs() {
    if (!nb) return;
    try {
      const d = await request(nb.dataset.url);
      const badge = document.getElementById('notifCount');
      badge.textContent = d.unread > 99 ? '99+' : d.unread;
      badge.classList.toggle('d-none', !d.unread);
      document.querySelectorAll('[data-notif-count]').forEach(el => { el.textContent = d.unread; el.classList.toggle('d-none', !d.unread); });
      document.getElementById('notifList').innerHTML = d.latest.length ? d.latest.map(n => `<a class="item ${n.unread ? 'unread' : ''}" href="${esc(n.url)}"><i class="bi ${esc(n.icon)}"></i><div><div class="fw-bold">${esc(n.title)}</div><div class="text-muted small">${esc(n.body || '')}</div><small class="text-muted">${esc(n.time)}</small></div></a>`).join('')
        : '<div class="p-4 text-center text-muted">لا توجد إشعارات</div>';
    } catch (e) { }
  }
  loadNotifs(); setInterval(loadNotifs, 60000);

  // تحديث إحصائيات لوحة التحكم تلقائيًا
  const statsEl = document.querySelector('[data-stats-url]');
  if (statsEl) setInterval(async () => {
    try {
      const d = await request(statsEl.dataset.statsUrl);
      Object.entries(d).forEach(([k, v]) => document.querySelectorAll('[data-stat="' + k + '"]').forEach(el => el.textContent = v));
    } catch (e) { }
  }, 30000);

  // اختيار العميل بالبحث
  document.querySelectorAll('select[data-client-select]').forEach(sel => {
    const ts = new TomSelect(sel, {
      valueField: 'id', labelField: 'text', searchField: ['text'], maxOptions: 20, preload: 'focus', placeholder: 'ابحث باسم العميل أو رقم الهاتف أو رقم العميل...',
      load: (q, cb) => request(sel.dataset.url + '?q=' + encodeURIComponent(q)).then(cb).catch(() => cb()),
      render: { no_results: () => '<div class="no-results p-2">لا يوجد عميل مطابق</div>' },
      onChange: v => {
        const opt = ts.options[v];
        const out = document.getElementById(sel.dataset.clientNoTarget || '');
        if (out) out.textContent = opt ? opt.client_no : '—';
        sel.dispatchEvent(new CustomEvent('client-changed', { detail: v }));
      }
    });
  });

  // نموذج الدفعة: تحميل سيارات العميل وأرصدتها
  const pc = document.getElementById('paymentClient');
  const pv = document.getElementById('paymentVehicle');
  if (pc && pv) {
    async function loadVehicles(clientId, selected) {
      pv.innerHTML = '<option value="">جارٍ التحميل...</option>';
      document.getElementById('vehicleBalance').classList.add('d-none');
      if (!clientId) { pv.innerHTML = '<option value="">اختر العميل أولًا</option>'; return; }
      const list = await request(pv.dataset.url.replace('__ID__', clientId));
      pv._list = list;
      pv.innerHTML = '<option value="">اختر السيارة</option>' + list.map(v => `<option value="${v.id}" ${String(v.id) === String(selected) ? 'selected' : ''}>${esc(v.text)}</option>`).join('');
      if (!list.length) pv.innerHTML = '<option value="">لا توجد سيارات لهذا العميل</option>';
      showBalance();
    }
    function showBalance() {
      const v = (pv._list || []).find(x => String(x.id) === pv.value);
      const b = document.getElementById('vehicleBalance');
      if (!v) { b.classList.add('d-none'); return; }
      b.classList.remove('d-none');
      b.querySelector('[data-b=due]').textContent = v.due; b.querySelector('[data-b=paid]').textContent = v.paid; b.querySelector('[data-b=remaining]').textContent = v.remaining;
      const amt = document.getElementById('amount'); if (amt && !amt.value && parseFloat(v.remaining_raw) > 0) amt.placeholder = v.remaining_raw;
    }
    pc.addEventListener('client-changed', e => loadVehicles(e.detail));
    pv.addEventListener('change', showBalance);
    if (pc.value) loadVehicles(pc.value, pv.dataset.selected);
  }

  // ترتيب الصور والمراحل بالسحب
  document.querySelectorAll('[data-sortable]').forEach(el => {
    Sortable.create(el, {
      animation: 160, handle: el.dataset.handle || undefined, onEnd: async () => {
        const order = [...el.children].map(c => c.dataset.id).filter(Boolean);
        try { const d = await request(el.dataset.sortable, { method: 'POST', body: { order } }); toast(d.message); } catch (e) { toast(e.message, 'error'); }
      }
    });
  });

  // بنود التكاليف الديناميكية
  const ci = document.getElementById('costItems');
  if (ci) {
    const tpl = document.getElementById('costItemTpl').innerHTML;
    let i = ci.querySelectorAll('.cost-row').length + 100;
    document.getElementById('addCostItem').addEventListener('click', () => { ci.insertAdjacentHTML('beforeend', tpl.replaceAll('__I__', i++)); });
    ci.addEventListener('click', e => { if (e.target.closest('.rm-cost')) e.target.closest('.cost-row').remove(); });
  }

  // معاينة الصور قبل الرفع
  document.querySelectorAll('input[type=file][data-preview]').forEach(inp => inp.addEventListener('change', () => {
    const box = document.getElementById(inp.dataset.preview); box.innerHTML = '';
    [...inp.files].forEach(f => { if (!f.type.startsWith('image/')) return; const img = document.createElement('img'); img.src = URL.createObjectURL(f); img.className = 'thumb me-1 mb-1'; box.appendChild(img); });
  }));
})();
