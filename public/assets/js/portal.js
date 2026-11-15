(function () {
  const { request } = window.Ameed;
  // معرض الصور + العرض بحجم كبير
  const g = document.querySelector('[data-gallery]');
  if (g) {
    const imgs = JSON.parse(g.dataset.gallery);
    let i = 0;
    const main = g.querySelector('.gallery-main img');
    const count = g.querySelector('.gallery-main .count');
    const thumbs = g.querySelectorAll('.gallery-thumbs img');
    const lb = document.getElementById('lightbox');
    const lbImg = lb.querySelector('img');
    const lbCount = lb.querySelector('.lb-count');
    function show(n) {
      i = (n + imgs.length) % imgs.length;
      main.src = imgs[i]; lbImg.src = imgs[i];
      if (count) count.textContent = (i + 1) + ' / ' + imgs.length;
      lbCount.textContent = (i + 1) + ' / ' + imgs.length;
      thumbs.forEach((t, k) => t.classList.toggle('active', k === i));
      thumbs[i]?.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
    }
    g.querySelector('.prev')?.addEventListener('click', e => { e.stopPropagation(); show(i - 1); });
    g.querySelector('.next')?.addEventListener('click', e => { e.stopPropagation(); show(i + 1); });
    thumbs.forEach((t, k) => t.addEventListener('click', () => show(k)));
    g.querySelector('.gallery-main').addEventListener('click', () => { lb.classList.add('open'); document.body.style.overflow = 'hidden'; });
    const close = () => { lb.classList.remove('open'); document.body.style.overflow = ''; };
    lb.querySelector('.lb-close').addEventListener('click', close);
    lb.querySelector('.lb-prev').addEventListener('click', () => show(i - 1));
    lb.querySelector('.lb-next').addEventListener('click', () => show(i + 1));
    lb.addEventListener('click', e => { if (e.target === lb) close(); });
    document.addEventListener('keydown', e => {
      if (!lb.classList.contains('open')) return;
      if (e.key === 'Escape') close(); if (e.key === 'ArrowLeft') show(i + 1); if (e.key === 'ArrowRight') show(i - 1);
    });
    let sx = null;
    [g.querySelector('.gallery-main'), lb].forEach(el => {
      el.addEventListener('touchstart', e => sx = e.touches[0].clientX, { passive: true });
      el.addEventListener('touchend', e => { if (sx === null) return; const dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 40) show(dx > 0 ? i + 1 : i - 1); sx = null; });
    });
  }
  // تحديث الأرقام تلقائيًا
  const s = document.querySelector('[data-portal-stats]');
  async function refresh() {
    if (!s) return;
    try {
      const d = await request(s.dataset.portalStats);
      Object.entries(d).forEach(([k, v]) => document.querySelectorAll('[data-stat="' + k + '"]').forEach(el => el.textContent = v));
      document.querySelectorAll('[data-unread]').forEach(el => { el.textContent = d.unread; el.classList.toggle('d-none', !d.unread); });
      document.querySelectorAll('[data-stat-bar]').forEach(el => el.style.width = d.percent + '%');
    } catch (e) { }
  }
  if (s) setInterval(refresh, 45000);
})();
