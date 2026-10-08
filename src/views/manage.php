<?php include VIEWS . '/partials/header.php'; ?>
<div class="page-head">
  <h1>Manage Portfolios</h1>
  <a class="btn" href="/create">+ New Portfolio</a>
</div>
<?php if (!$list): ?>
  <div class="card-box empty">No portfolios yet. <a href="/create">Create your first one!</a></div>
<?php else: ?>
<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Template</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($list as $r): ?>
      <tr>
        <td><?= e($r['full_name']) ?></td>
        <td><?= e($r['email']) ?></td>
        <td>Template <?= (int)$r['template'] ?></td>
        <td><?= e(date('n/j/Y', strtotime($r['created_at']))) ?></td>
        <td class="row-actions">
          <a class="btn btn-sm" href="/portfolio/<?= (int)$r['id'] ?>/preview">View</a>
          <a class="btn btn-sm btn-outline" href="/portfolio/<?= (int)$r['id'] ?>/edit">Edit</a>
          <a class="btn btn-sm btn-outline" href="/portfolio/<?= (int)$r['id'] ?>/templates">Template</a>
          <form method="POST" action="/portfolio/<?= (int)$r['id'] ?>/delete"
                onsubmit="return confirm('Delete this portfolio permanently?')">
            <button class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php include VIEWS . '/partials/footer.php'; ?>
