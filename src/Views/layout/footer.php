<?php
// src/Views/layout/footer.php
?>
</div> <!-- /.container -->

<footer class="site-footer mt-auto">
  <div class="container">
    <div class="row align-items-center gy-3">
      <div class="col-md-6 text-center text-md-start">
        <div class="footer-brand d-flex justify-content-center justify-content-md-start align-items-center mb-1">
          <i class="bi bi-music-note-beamed me-2"></i>
          <span>MusicStore</span>
        </div>
        <div class="small text-muted">
          &copy; <?= date('Y') ?> MusicStore. Все права защищены.
        </div>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <ul class="list-inline mb-0 footer-links">
          <li class="list-inline-item">
            <a href="/about">О нас</a>
          </li>
          <li class="list-inline-item">
            <span class="text-muted">•</span>
          </li>
          <li class="list-inline-item">
            <a href="/contact">Контакты</a>
          </li>
          <li class="list-inline-item">
            <span class="text-muted">•</span>
          </li>
          <li class="list-inline-item">
            <a href="/privacy">Политика конфиденциальности</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</footer>

<!-- Скрипты -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-…" crossorigin="anonymous"></script>

<!-- Плавающая корзина -->
<div id="floating-cart">
  <a href="/cart" title="Перейти в корзину">
    <img src="/assets/images/cart.svg" alt="Cart">
  </a>
</div>

<script>
// Skeleton-лoадер: показываем пока не загрузятся все ресурсы
document.addEventListener('DOMContentLoaded', () => {
  const firstRow = document.querySelector('.row');
  if (firstRow) {
    firstRow.classList.add('skeleton-loading');
  }
});
window.addEventListener('load', () => {
  const firstRow = document.querySelector('.row');
  if (firstRow) {
    firstRow.classList.remove('skeleton-loading');
  }
});

// Тема: читаем из localStorage, переключаем класс и иконку
(() => {
  const btn = document.getElementById('theme-toggle');
  if (!btn) return;
  const body = document.body;
  const current = localStorage.getItem('theme') || 'light';
  if (current === 'dark') body.classList.add('dark-theme');
  btn.textContent = body.classList.contains('dark-theme') ? '☀️' : '🌙';
  btn.onclick = () => {
    body.classList.toggle('dark-theme');
    const theme = body.classList.contains('dark-theme') ? 'dark' : 'light';
    localStorage.setItem('theme', theme);
    btn.textContent = theme === 'dark' ? '☀️' : '🌙';
  };
})();

// Анимация «улетающей» картинки в корзину
document.querySelectorAll('form[action^="/cart/add"]').forEach(form => {
  form.addEventListener('submit', e => {
    e.preventDefault();
    const card = form.closest('.card-product');
    const img  = card ? card.querySelector('.card-img-top') : null;
    if (!img) {
      form.submit();
      return;
    }
    const fly = img.cloneNode();
    const r = img.getBoundingClientRect();
    Object.assign(fly.style, {
      position: 'fixed', top: r.top+'px', left: r.left+'px',
      width: r.width+'px', height: r.height+'px',
      transition: 'all .6s ease-in-out', zIndex: 2001
    });
    document.body.appendChild(fly);
    const cart = document.getElementById('floating-cart').getBoundingClientRect();
    requestAnimationFrame(() => {
      Object.assign(fly.style, {
        top: (cart.top + cart.height/2 - r.height/4)+'px',
        left: (cart.left + cart.width/2 - r.width/4)+'px',
        width: (r.width/2)+'px', height: (r.height/2)+'px',
        opacity: '0.5'
      });
    });
    fly.addEventListener('transitionend', () => {
      fly.remove();
      form.submit();
    }, { once: true });
  });
});
</script>

</body>
</html>
