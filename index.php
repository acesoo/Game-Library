<?php
/**
 * index.php - Game listing (Read + Delete)
 * Steam-inspired dark UI with Bootstrap 5 + PDO MySQL
 */

require_once 'db.php';

$pdo = getDBConnection();

// --- Handle DELETE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);
    if ($deleteId) {
        $s = $pdo->prepare('SELECT cover_path FROM games WHERE id = :id');
        $s->execute([':id' => $deleteId]);
        $row = $s->fetch();
        if ($row && !empty($row['cover_path']) && file_exists($row['cover_path'])) {
            unlink($row['cover_path']);
        }
        $pdo->prepare('DELETE FROM games WHERE id = :id')->execute([':id' => $deleteId]);
        header('Location: index.php?deleted=1');
        exit;
    }
}

// --- Search / Filter ---
$search = trim($_GET['search'] ?? '');
$genre  = trim($_GET['genre']  ?? '');

$where  = [];
$params = [];

if (!empty($search)) {
    $where[]           = '(title LIKE :search OR developer LIKE :search2)';
    $params[':search']  = '%' . $search . '%';
    $params[':search2'] = '%' . $search . '%';
}
if (!empty($genre)) {
    $where[]         = 'genre = :genre';
    $params[':genre'] = $genre;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt     = $pdo->prepare("SELECT * FROM games $whereSQL ORDER BY created_at DESC");
$stmt->execute($params);
$games = $stmt->fetchAll();

// Genres for filter dropdown
$genreStmt = $pdo->query('SELECT DISTINCT genre FROM games ORDER BY genre');
$genres    = $genreStmt->fetchAll(PDO::FETCH_COLUMN);

// Stats
$statsStmt = $pdo->query('SELECT COUNT(*) as total, AVG(price) as avg_price, AVG(rating) as avg_rating FROM games');
$stats     = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GameDB — Your Game Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Barlow:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <style>
        :root {
            --steam-bg:       #0a0e1a;
            --steam-surface:  #111827;
            --steam-card:     #1a2235;
            --steam-border:   #1e2d45;
            --steam-blue:     #1b9aef;
            --steam-blue2:    #0d7bc4;
            --steam-teal:     #66c0f4;
            --steam-green:    #a4d007;
            --steam-muted:    #5a7a9a;
            --steam-text:     #c6d4df;
            --steam-bright:   #e8f0f7;
            --steam-red:      #e84545;
            --steam-gold:     #f5c518;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--steam-bg);
            color: var(--steam-text);
            min-height: 100vh;
        }

        /* Subtle noise texture overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
            opacity: .4;
        }

        /* ── Header ── */
        .site-header {
            background: linear-gradient(180deg, #0d1526 0%, var(--steam-surface) 100%);
            border-bottom: 1px solid var(--steam-border);
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        .logo {
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--steam-bright);
            text-decoration: none;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .logo .logo-icon {
            width: 28px; height: 28px;
            background: var(--steam-blue);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: .9rem;
        }
        .logo span { color: var(--steam-teal); }

        .btn-add-game {
            background: linear-gradient(180deg, #4c9bcb 0%, var(--steam-blue2) 100%);
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: .45rem 1.2rem;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            font-size: .95rem;
            letter-spacing: .5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            transition: filter .2s;
        }
        .btn-add-game:hover { filter: brightness(1.15); color: #fff; }

        /* ── Hero Banner ── */
        .hero-banner {
            background: linear-gradient(135deg, #0d1a2e 0%, #0a1628 40%, #071020 100%);
            border-bottom: 1px solid var(--steam-border);
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .hero-banner::after {
            content: '';
            position: absolute;
            right: -100px; top: -100px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(27,154,239,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-inner { max-width: 1400px; margin: 0 auto; position: relative; z-index: 1; }
        .hero-banner h1 {
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            color: var(--steam-bright);
            letter-spacing: 1px;
            margin-bottom: .3rem;
        }
        .hero-banner p { color: var(--steam-muted); font-size: 1rem; }

        /* ── Stats Bar ── */
        .stats-bar {
            display: flex;
            gap: 2rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        .stat-item { display: flex; flex-direction: column; gap: .1rem; }
        .stat-value {
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--steam-teal);
            line-height: 1;
        }
        .stat-label { font-size: .75rem; color: var(--steam-muted); text-transform: uppercase; letter-spacing: .08em; }

        /* ── Content ── */
        .content-wrap { max-width: 1400px; margin: 0 auto; padding: 1.5rem 2rem; }

        /* ── Alerts ── */
        .alert-success {
            background: rgba(164,208,7,.08);
            border: 1px solid rgba(164,208,7,.25);
            color: var(--steam-green);
            border-radius: 4px;
            padding: .7rem 1rem;
            font-size: .88rem;
            display: flex; align-items: center; gap: .5rem;
            margin-bottom: 1rem;
        }

        /* ── Toolbar ── */
        .toolbar {
            display: flex;
            gap: .75rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }
        .search-box {
            background: var(--steam-card);
            border: 1px solid var(--steam-border);
            color: var(--steam-text);
            border-radius: 4px;
            padding: .5rem .9rem .5rem 2.4rem;
            font-family: 'Barlow', sans-serif;
            font-size: .9rem;
            width: 260px;
            position: relative;
            transition: border-color .2s;
        }
        .search-box:focus { outline: none; border-color: var(--steam-blue); color: var(--steam-bright); background: var(--steam-card); }
        .search-wrap { position: relative; }
        .search-wrap .bi { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: var(--steam-muted); font-size: .85rem; pointer-events: none; }

        .filter-select {
            background: var(--steam-card);
            border: 1px solid var(--steam-border);
            color: var(--steam-text);
            border-radius: 4px;
            padding: .5rem .9rem;
            font-family: 'Barlow', sans-serif;
            font-size: .9rem;
            cursor: pointer;
        }
        .filter-select:focus { outline: none; border-color: var(--steam-blue); }
        .filter-select option { background: var(--steam-surface); }

        .result-count {
            font-size: .85rem;
            color: var(--steam-muted);
            margin-left: auto;
        }
        .result-count strong { color: var(--steam-text); }

        /* ── Game Cards Grid ── */
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .game-card {
            background: var(--steam-card);
            border: 1px solid var(--steam-border);
            border-radius: 8px;
            overflow: hidden;
            transition: border-color .2s, transform .2s, box-shadow .2s;
            display: flex;
            flex-direction: column;
        }
        .game-card:hover {
            border-color: var(--steam-blue);
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(27,154,239,.15);
        }

        .game-cover {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
        }
        .game-cover-placeholder {
            width: 100%;
            height: 140px;
            background: linear-gradient(135deg, #0d1a2e, #1a2a4a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--steam-border);
        }

        .game-body { padding: .9rem 1rem; flex: 1; display: flex; flex-direction: column; gap: .5rem; }

        .game-title {
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--steam-bright);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .game-meta {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .genre-tag {
            background: rgba(27,154,239,.15);
            color: var(--steam-teal);
            border: 1px solid rgba(27,154,239,.25);
            border-radius: 3px;
            padding: .15rem .5rem;
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            white-space: nowrap;
        }
        .platform-tag {
            background: rgba(255,255,255,.05);
            color: var(--steam-muted);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 3px;
            padding: .15rem .5rem;
            font-size: .72rem;
            white-space: nowrap;
        }

        .game-dev { font-size: .82rem; color: var(--steam-muted); }
        .game-dev strong { color: var(--steam-text); font-weight: 500; }

        .game-footer {
            padding: .75rem 1rem;
            border-top: 1px solid var(--steam-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .game-price-wrap { display: flex; align-items: baseline; gap: .4rem; }
        .game-price {
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--steam-green);
        }
        .game-price.free { color: var(--steam-teal); }

        .star-rating {
            display: flex;
            align-items: center;
            gap: .2rem;
            font-size: .8rem;
        }
        .star-fill { color: var(--steam-gold); }
        .star-empty { color: var(--steam-border); }
        .rating-num { font-size: .78rem; color: var(--steam-muted); margin-left: .2rem; }

        .release-date { font-size: .78rem; color: var(--steam-muted); }

        /* ── Card Actions ── */
        .card-actions { display: flex; gap: .4rem; }
        .btn-card {
            border: none; border-radius: 4px; padding: .3rem .65rem;
            font-size: .78rem; font-weight: 600; cursor: pointer;
            transition: all .15s; text-decoration: none;
            display: inline-flex; align-items: center; gap: .25rem;
            font-family: 'Barlow', sans-serif;
        }
        .btn-card-edit { background: rgba(255,255,255,.07); color: var(--steam-text); }
        .btn-card-edit:hover { background: rgba(255,255,255,.14); color: var(--steam-bright); }
        .btn-card-del { background: rgba(232,69,69,.1); color: var(--steam-red); }
        .btn-card-del:hover { background: rgba(232,69,69,.22); }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            grid-column: 1 / -1;
        }
        .empty-state .icon { font-size: 4rem; opacity: .2; margin-bottom: 1rem; }
        .empty-state h3 { font-family: 'Rajdhani', sans-serif; font-size: 1.4rem; color: var(--steam-muted); }
        .empty-state p { color: var(--steam-muted); font-size: .9rem; margin-top: .4rem; }

        /* ── Modal ── */
        .modal-content {
            background: var(--steam-surface);
            border: 1px solid var(--steam-border);
            color: var(--steam-text);
            border-radius: 8px;
        }
        .modal-header { border-bottom: 1px solid var(--steam-border); }
        .modal-footer { border-top: 1px solid var(--steam-border); }
        .btn-close { filter: invert(.6); }
    </style>
</head>
<body>

<!-- ── Header ── -->
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="logo">
            <div class="logo-icon">🎮</div>
            Game<span>DB</span>
        </a>
        <a href="create.php" class="btn-add-game">
            <i class="bi bi-plus-lg"></i> Add Game
        </a>
    </div>
</header>

<!-- ── Hero ── -->
<div class="hero-banner">
    <div class="hero-inner">
        <h1>🎮 Game Library</h1>
        <p>Browse, manage, and track your video game collection.</p>
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-value"><?= number_format($stats['total']) ?></span>
                <span class="stat-label">Total Games</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">$<?= number_format($stats['avg_price'] ?? 0, 2) ?></span>
                <span class="stat-label">Avg. Price</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= number_format($stats['avg_rating'] ?? 0, 1) ?>/10</span>
                <span class="stat-label">Avg. Rating</span>
            </div>
        </div>
    </div>
</div>

<!-- ── Content ── -->
<div class="content-wrap">

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert-success"><i class="bi bi-check-circle-fill"></i> Game deleted successfully.</div>
    <?php endif; ?>
    <?php if (isset($_GET['created'])): ?>
        <div class="alert-success"><i class="bi bi-check-circle-fill"></i> Game added to the library!</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert-success"><i class="bi bi-check-circle-fill"></i> Game record updated.</div>
    <?php endif; ?>

    <!-- Toolbar -->
    <form method="GET" action="index.php">
        <div class="toolbar">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="search" class="search-box"
                    placeholder="Search games or developers…"
                    value="<?= htmlspecialchars($search) ?>" />
            </div>
            <select name="genre" class="filter-select" onchange="this.form.submit()">
                <option value="">All Genres</option>
                <?php foreach ($genres as $g): ?>
                <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-add-game" style="padding:.45rem 1rem;">
                <i class="bi bi-funnel-fill"></i> Filter
            </button>
            <?php if ($search || $genre): ?>
            <a href="index.php" class="btn-card btn-card-edit" style="padding:.45rem .9rem;">
                <i class="bi bi-x-lg"></i> Clear
            </a>
            <?php endif; ?>
            <span class="result-count"><strong><?= count($games) ?></strong> result<?= count($games) !== 1 ? 's' : '' ?></span>
        </div>
    </form>

    <!-- Games Grid -->
    <div class="games-grid">
        <?php if (empty($games)): ?>
            <div class="empty-state">
                <div class="icon">🎮</div>
                <h3>No games found</h3>
                <p><?= ($search || $genre) ? 'Try adjusting your search or filter.' : 'Click "Add Game" to start building your library.' ?></p>
            </div>
        <?php endif; ?>

        <?php foreach ($games as $game): ?>
        <div class="game-card">
            <!-- Cover -->
            <?php if (!empty($game['cover_path'])): ?>
                <img src="<?= htmlspecialchars($game['cover_path']) ?>" class="game-cover" alt="<?= htmlspecialchars($game['title']) ?>" />
            <?php else: ?>
                <div class="game-cover-placeholder">🎮</div>
            <?php endif; ?>

            <div class="game-body">
                <div class="game-title" title="<?= htmlspecialchars($game['title']) ?>">
                    <?= htmlspecialchars($game['title']) ?>
                </div>
                <div class="game-meta">
                    <span class="genre-tag"><?= htmlspecialchars($game['genre']) ?></span>
                    <span class="platform-tag"><?= htmlspecialchars($game['platform']) ?></span>
                </div>
                <div class="game-dev">by <strong><?= htmlspecialchars($game['developer']) ?></strong></div>
                <?php if (!empty($game['description'])): ?>
                <div style="font-size:.82rem;color:var(--steam-muted);line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    <?= htmlspecialchars($game['description']) ?>
                </div>
                <?php endif; ?>

                <!-- Star rating -->
                <div class="star-rating">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <?php if ($i <= 2): // Show 5 stars representing 10 scale — every 2 points = 1 star ?>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php
                    $stars = round($game['rating'] / 2); // convert /10 to /5
                    for ($i = 1; $i <= 5; $i++):
                    ?>
                        <i class="bi bi-star-fill <?= $i <= $stars ? 'star-fill' : 'star-empty' ?>"></i>
                    <?php endfor; ?>
                    <span class="rating-num"><?= $game['rating'] ?>/10</span>
                </div>
            </div>

            <div class="game-footer">
                <div>
                    <div class="game-price <?= $game['price'] == 0 ? 'free' : '' ?>">
                        <?= $game['price'] == 0 ? 'FREE' : '$' . number_format($game['price'], 2) ?>
                    </div>
                    <div class="release-date">
                        <i class="bi bi-calendar3"></i>
                        <?= date('M j, Y', strtotime($game['release_date'])) ?>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="edit.php?id=<?= $game['id'] ?>" class="btn-card btn-card-edit">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>
                    <button class="btn-card btn-card-del"
                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                        data-id="<?= $game['id'] ?>"
                        data-title="<?= htmlspecialchars($game['title']) ?>">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-family:'Rajdhani',sans-serif;font-weight:700;color:var(--steam-bright);">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Remove Game
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:.95rem;">
                Remove <strong id="gameTitleDisplay" style="color:var(--steam-bright);"></strong> from the library?
                This cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="index.php" style="display:inline;">
                    <input type="hidden" name="delete_id" id="deleteIdInput" />
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash3-fill me-1"></i>Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('deleteIdInput').value = btn.dataset.id;
        document.getElementById('gameTitleDisplay').textContent = btn.dataset.title;
    });
</script>
</body>
</html>
