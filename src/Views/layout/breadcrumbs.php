<?php if (!empty($breadcrumbs)): ?>
  <div class="app-breadcrumbs mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <?php foreach ($breadcrumbs as $i => $bc): ?>
          <?php
            $isLast = ($i + 1 === count($breadcrumbs));
            $label  = htmlspecialchars($bc['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $url    = $bc['url'] ?? null;
          ?>
          <?php if ($isLast || !$url): ?>
            <li class="breadcrumb-item active" aria-current="page">
              <?= $label ?>
            </li>
          <?php else: ?>
            <li class="breadcrumb-item">
              <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($i === 0 && ($url === '/' || $url === '/products')): ?>
                  <i class="bi bi-house-door me-1"></i>
                <?php endif; ?>
                <?= $label ?>
              </a>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ol>
    </nav>
  </div>
<?php endif; ?>
