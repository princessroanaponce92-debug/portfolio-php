
<?php
include VIEWS . '/partials/header.php';

$list = $list ?? [];
$favoriteIds = array_map('intval', $favoriteIds ?? []);
$isFavoritesPage = !empty($favoritesPage);
?>

<div class="page-head">
    <div>
        <h1><?= $isFavoritesPage ? 'Favorite Portfolios' : 'Manage Portfolios' ?></h1>

        <?php if (!$isFavoritesPage): ?>
            <p>Welcome, <?= e($_SESSION['user']['username'] ?? 'User') ?>!</p>
        <?php endif; ?>
    </div>

    <div class="manage-head-actions">
        <a class="btn btn-outline" href="/manage">My Portfolios</a>
        <a class="btn btn-outline" href="/favorites">♥ Favorites</a>
        <a class="btn" href="/create">+ New Portfolio</a>
    </div>
</div>

<?php if (empty($list)): ?>
    <div class="card-box empty">
        <h2><?= $isFavoritesPage ? 'No favorites yet' : 'No portfolios yet' ?></h2>

        <p>
            <?= $isFavoritesPage
                ? 'Add a portfolio to your favorites using the heart button.'
                : 'Create your first portfolio to get started.' ?>
        </p>

        <?php if (!$isFavoritesPage): ?>
            <a class="btn" href="/create">Create Portfolio</a>
        <?php else: ?>
            <a class="btn" href="/manage">Browse My Portfolios</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Template</th>
                    <th>Created</th>
                    <th>Favorite</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($list as $r): ?>
                <?php
                $id = (int) $r['id'];
                $isFavorite = in_array($id, $favoriteIds, true);
                $template = (int) ($r['template'] ?? 1);
                ?>
                <tr>
                    <td><?= e($r['full_name']) ?></td>

                    <td><?= e($r['email']) ?></td>

                    <!-- Display only the template number -->
                    <td><?= in_array($template, [1, 2, 3], true) ? $template : '—' ?></td>

                    <td>
                        <?= !empty($r['created_at'])
                            ? e(date('n/j/Y', strtotime($r['created_at'])))
                            : '—' ?>
                    </td>

                    <td>
                        <form method="POST"
                              action="/portfolio/<?= $id ?>/favorite">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= e(csrf_token()) ?>"
                            >

                            <input
                                type="hidden"
                                name="return_to"
                                value="<?= $isFavoritesPage ? '/favorites' : '/manage' ?>"
                            >

                            <button
                                type="submit"
                                class="btn btn-sm btn-outline favorite-button"
                                aria-label="<?= $isFavorite ? 'Remove from favorites' : 'Add to favorites' ?>"
                                title="<?= $isFavorite ? 'Remove from favorites' : 'Add to favorites' ?>"
                            >
                                <?= $isFavorite ? '♥' : '♡' ?>
                            </button>
                        </form>
                    </td>

                    <td class="row-actions">
                        <!-- View portfolio -->
                        <a
                            class="action-icon view-icon"
                            href="/portfolio/<?= $id ?>/preview"
                            title="View portfolio"
                            aria-label="View portfolio"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>

                        <!-- Edit portfolio -->
                        <a
                            class="action-icon edit-icon"
                            href="/portfolio/<?= $id ?>/edit"
                            title="Edit portfolio"
                            aria-label="Edit portfolio"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                            </svg>
                        </a>

                        <!-- Change template -->
                        <a
                            class="action-icon template-icon"
                            href="/portfolio/<?= $id ?>/templates"
                            title="Choose template"
                            aria-label="Choose template"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1"/>
                                <rect x="14" y="3" width="7" height="7" rx="1"/>
                                <rect x="3" y="14" width="7" height="7" rx="1"/>
                                <rect x="14" y="14" width="7" height="7" rx="1"/>
                            </svg>
                        </a>

                        <!-- Delete portfolio: hidden on Favorites page -->
                        <?php if (!$isFavoritesPage): ?>
                            <form
                                method="POST"
                                action="/portfolio/<?= $id ?>/delete"
                                onsubmit="return confirm('Are you sure you want to permanently delete this portfolio? This action cannot be undone.');"
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e(csrf_token()) ?>"
                                >

                                <button
                                    type="submit"
                                    class="action-icon delete-icon"
                                    title="Delete portfolio"
                                    aria-label="Delete portfolio"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="m19 6-1 14H6L5 6"/>
                                        <path d="M10 11v5M14 11v5"/>
                                    </svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
.manage-head-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.row-actions {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    flex-wrap: nowrap;
    gap: 8px;
    white-space: nowrap;
}

.row-actions form,
td form {
    margin: 0;
}

.action-icon {
    width: 36px;
    height: 36px;
    padding: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 36px;
    border: 1px solid #dfe3eb;
    border-radius: 8px;
    background: #fff;
    color: #394150;
    cursor: pointer;
    text-decoration: none;
    transition:
        background .18s ease,
        color .18s ease,
        border-color .18s ease,
        transform .18s ease;
}

.action-icon svg {
    display: block;
    width: 19px;
    height: 19px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.action-icon:hover {
    background: #f0f3f9;
    border-color: #b8c2d4;
    transform: translateY(-1px);
}

.action-icon:focus-visible {
    outline: 3px solid #9db7ff;
    outline-offset: 2px;
}

.view-icon:hover {
    color: #2457d6;
}

.edit-icon:hover {
    color: #8a5a00;
}

.template-icon:hover {
    color: #6b45c6;
}

.delete-icon {
    color: #c62828;
}

.delete-icon:hover {
    background: #fff0f0;
    border-color: #e8aaaa;
    color: #a51616;
}

.favorite-button {
    min-width: 36px;
    font-size: 20px;
    line-height: 1;
}

@media (max-width: 700px) {
    .page-head {
        align-items: flex-start;
        gap: 15px;
        flex-wrap: wrap;
    }

    .manage-head-actions {
        gap: 8px;
    }

    .table-wrap {
        overflow-x: auto;
    }

    table {
        min-width: 750px;
    }

    .row-actions {
        gap: 6px;
    }
}
</style>

<?php include VIEWS . '/partials/footer.php'; ?>
