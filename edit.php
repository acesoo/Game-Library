<?php
/**
 * edit.php - Edit an existing game (uses image URL instead of file upload)
 */

require_once 'db.php';

$pdo = getDBConnection();

$id = filter_var($_GET['id'] ?? $_POST['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM games WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$game = $stmt->fetch();
if (!$game) { header('Location: index.php'); exit; }

$errors = [];
$values = [
    'title'        => $game['title'],
    'genre'        => $game['genre'],
    'developer'    => $game['developer'],
    'price'        => $game['price'],
    'release_date' => $game['release_date'],
    'platform'     => $game['platform'],
    'rating'       => $game['rating'],
    'description'  => $game['description'] ?? '',
    'cover_path'   => $game['cover_path'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title        = trim($_POST['title']        ?? '');
    $genre        = trim($_POST['genre']        ?? '');
    $developer    = trim($_POST['developer']    ?? '');
    $price        = trim($_POST['price']        ?? '');
    $release_date = trim($_POST['release_date'] ?? '');
    $platform     = trim($_POST['platform']     ?? '');
    $rating       = trim($_POST['rating']       ?? '');
    $description  = trim($_POST['description']  ?? '');
    $cover_path   = trim($_POST['cover_path']   ?? '');

    // --- Validation using empty() ---
    if (empty($title))        $errors['title']        = 'Game title is required.';
    if (empty($genre))        $errors['genre']        = 'Genre is required.';
    if (empty($developer))    $errors['developer']    = 'Developer is required.';
    if (empty($price) && $price !== '0') $errors['price'] = 'Price is required.';
    elseif (!is_numeric($price) || $price < 0) $errors['price'] = 'Price must be a valid positive number.';
    if (empty($release_date)) $errors['release_date'] = 'Release date is required.';
    if (empty($platform))     $errors['platform']     = 'Platform is required.';
    if (empty($rating))       $errors['rating']       = 'Rating is required.';
    elseif (!is_numeric($rating) || $rating < 0 || $rating > 10) $errors['rating'] = 'Rating must be 0–10.';

    if (!empty($cover_path) && !filter_var($cover_path, FILTER_VALIDATE_URL)) {
        $errors['cover_path'] = 'Please enter a valid image URL.';
    }

    $values = compact('title','genre','developer','price','release_date','platform','rating','description','cover_path');

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'UPDATE games SET title=:title, genre=:genre, developer=:developer,
             price=:price, release_date=:release_date, platform=:platform,
             rating=:rating, description=:description, cover_path=:cover_path
             WHERE id=:id'
        );
        $stmt->execute([
            ':title'        => $title,
            ':genre'        => $genre,
            ':developer'    => $developer,
            ':price'        => (float)$price,
            ':release_date' => $release_date,
            ':platform'     => $platform,
            ':rating'       => (int)$rating,
            ':description'  => $description ?: null,
            ':cover_path'   => $cover_path ?: null,
            ':id'           => $id,
        ]);
        header('Location: index.php?updated=1');
        exit;
    }
}

$genreOptions = ['Action','Adventure','RPG','Action RPG','FPS','Strategy','Simulation','Sports','Horror','Puzzle','Platformer','Fighting','Racing','Metroidvania','Roguelite','Sandbox','Other'];
$platformOptions = ['PC','PS5','PS4','Xbox Series X','Xbox One','Nintendo Switch','Mobile','PC, PS5, Xbox','PC, Switch','Multi-Platform'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Game — GameDB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Barlow:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <style>
        :root { --steam-bg:#0a0e1a; --steam-surface:#111827; --steam-card:#1a2235; --steam-border:#1e2d45; --steam-blue:#1b9aef; --steam-blue2:#0d7bc4; --steam-teal:#66c0f4; --steam-green:#a4d007; --steam-muted:#5a7a9a; --steam-text:#c6d4df; --steam-bright:#e8f0f7; --steam-red:#e84545; --steam-gold:#f5c518; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Barlow',sans-serif; background:var(--steam-bg); color:var(--steam-text); min-height:100vh; }
        .site-header { background:linear-gradient(180deg,#0d1526 0%,var(--steam-surface) 100%); border-bottom:1px solid var(--steam-border); padding:0 2rem; }
        .header-inner { max-width:900px; margin:0 auto; display:flex; align-items:center; height:60px; }
        .logo { font-family:'Rajdhani',sans-serif; font-weight:700; font-size:1.6rem; color:var(--steam-bright); text-decoration:none; letter-spacing:1px; display:flex; align-items:center; gap:.5rem; }
        .logo .logo-icon { width:28px; height:28px; background:var(--steam-blue); border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.9rem; }
        .logo span { color:var(--steam-teal); }
        .content-wrap { max-width:900px; margin:0 auto; padding:2rem; }
        .page-title { font-family:'Rajdhani',sans-serif; font-weight:700; font-size:1.8rem; color:var(--steam-bright); letter-spacing:.5px; margin-bottom:1.5rem; display:flex; align-items:center; gap:.6rem; }
        .form-card { background:var(--steam-card); border:1px solid var(--steam-border); border-radius:8px; overflow:hidden; }
        .form-section { padding:1.5rem; border-bottom:1px solid var(--steam-border); }
        .form-section:last-child { border-bottom:none; }
        .section-title { font-family:'Rajdhani',sans-serif; font-weight:700; font-size:.85rem; color:var(--steam-teal); text-transform:uppercase; letter-spacing:.1em; margin-bottom:1rem; display:flex; align-items:center; gap:.4rem; }
        label { font-weight:500; font-size:.82rem; color:var(--steam-muted); margin-bottom:.35rem; display:block; text-transform:uppercase; letter-spacing:.05em; }
        .form-control, .form-select, textarea.form-control { background:#0d1626; border:1px solid var(--steam-border); color:var(--steam-text); border-radius:4px; padding:.55rem .85rem; font-size:.92rem; font-family:'Barlow',sans-serif; transition:border-color .2s,box-shadow .2s; }
        .form-control:focus, .form-select:focus { background:#0d1626; color:var(--steam-bright); border-color:var(--steam-blue); box-shadow:0 0 0 3px rgba(27,154,239,.15); outline:none; }
        .form-select option { background:#111827; }
        .invalid-feedback { display:block; font-size:.8rem; color:var(--steam-red); margin-top:.3rem; }
        .form-footer { padding:1.2rem 1.5rem; border-top:1px solid var(--steam-border); display:flex; gap:.75rem; flex-wrap:wrap; }
        .btn-submit { background:linear-gradient(180deg,#4c9bcb 0%,var(--steam-blue2) 100%); color:#fff; border:none; border-radius:4px; padding:.6rem 1.8rem; font-family:'Rajdhani',sans-serif; font-weight:700; font-size:1rem; letter-spacing:.5px; cursor:pointer; transition:filter .2s; display:inline-flex; align-items:center; gap:.4rem; }
        .btn-submit:hover { filter:brightness(1.15); }
        .btn-cancel { background:rgba(255,255,255,.06); color:var(--steam-text); border:1px solid var(--steam-border); border-radius:4px; padding:.6rem 1.3rem; font-family:'Rajdhani',sans-serif; font-weight:600; font-size:1rem; text-decoration:none; display:inline-flex; align-items:center; gap:.4rem; transition:background .2s; }
        .btn-cancel:hover { background:rgba(255,255,255,.1); color:var(--steam-bright); }
        .required-star { color:var(--steam-red); }
        .rating-row { display:flex; align-items:center; gap:1rem; }
        #ratingDisplay { font-family:'Rajdhani',sans-serif; font-size:1.4rem; font-weight:700; color:var(--steam-gold); min-width:2.5rem; }
        input[type=range] { accent-color:var(--steam-blue); flex:1; }
        #coverPreview { width:100%; max-height:220px; object-fit:cover; border-radius:6px; margin-top:1rem; display:none; border:1px solid var(--steam-border); }
        .edit-badge { background:rgba(27,154,239,.1); border:1px solid rgba(27,154,239,.2); color:var(--steam-teal); border-radius:4px; padding:.5rem 1rem; font-size:.83rem; display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem; }
        .url-hint { font-size:.78rem; color:var(--steam-muted); margin-top:.35rem; }
    </style>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="logo">
            <div class="logo-icon">🎮</div>
            Game<span>DB</span>
        </a>
    </div>
</header>

<div class="content-wrap">

    <?php if (!empty($errors)): ?>
        <div style="background:rgba(232,69,69,.08);border:1px solid rgba(232,69,69,.25);border-radius:6px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.88rem;color:var(--steam-red);">
            <i class="bi bi-exclamation-circle-fill me-2"></i>Please fix the errors below.
        </div>
    <?php endif; ?>

    <div class="page-title"><i class="bi bi-pencil-fill" style="color:var(--steam-blue);"></i> Edit Game</div>
    <div class="edit-badge"><i class="bi bi-info-circle-fill"></i> Editing: <strong><?= htmlspecialchars($game['title']) ?></strong> — ID #<?= $id ?></div>

    <form method="POST" action="edit.php" novalidate>
    <input type="hidden" name="id" value="<?= $id ?>" />
    <div class="form-card">

        <div class="form-section">
            <div class="section-title"><i class="bi bi-controller"></i> Game Details</div>
            <div class="row g-3">
                <div class="col-12">
                    <label>Game Title <span class="required-star">*</span></label>
                    <input type="text" name="title" class="form-control <?= isset($errors['title'])?'is-invalid':'' ?>"
                        value="<?= htmlspecialchars($values['title']) ?>" />
                    <?php if(isset($errors['title'])): ?><div class="invalid-feedback"><?= $errors['title'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label>Genre <span class="required-star">*</span></label>
                    <select name="genre" class="form-select <?= isset($errors['genre'])?'is-invalid':'' ?>">
                        <?php foreach($genreOptions as $g): ?>
                        <option value="<?= $g ?>" <?= $values['genre']===$g?'selected':'' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(isset($errors['genre'])): ?><div class="invalid-feedback"><?= $errors['genre'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label>Developer <span class="required-star">*</span></label>
                    <input type="text" name="developer" class="form-control <?= isset($errors['developer'])?'is-invalid':'' ?>"
                        value="<?= htmlspecialchars($values['developer']) ?>" />
                    <?php if(isset($errors['developer'])): ?><div class="invalid-feedback"><?= $errors['developer'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label>Platform <span class="required-star">*</span></label>
                    <select name="platform" class="form-select <?= isset($errors['platform'])?'is-invalid':'' ?>">
                        <?php foreach($platformOptions as $p): ?>
                        <option value="<?= $p ?>" <?= $values['platform']===$p?'selected':'' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(isset($errors['platform'])): ?><div class="invalid-feedback"><?= $errors['platform'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label>Release Date <span class="required-star">*</span></label>
                    <input type="date" name="release_date" class="form-control <?= isset($errors['release_date'])?'is-invalid':'' ?>"
                        value="<?= htmlspecialchars($values['release_date']) ?>" />
                    <?php if(isset($errors['release_date'])): ?><div class="invalid-feedback"><?= $errors['release_date'] ?></div><?php endif; ?>
                </div>
                <div class="col-12">
                    <label>Description <span style="color:var(--steam-muted);font-size:.75rem;text-transform:none;">(optional)</span></label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($values['description']) ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title"><i class="bi bi-tag-fill"></i> Pricing & Rating</div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label>Price (USD) <span class="required-star">*</span></label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--steam-muted);">$</span>
                        <input type="number" name="price" class="form-control <?= isset($errors['price'])?'is-invalid':'' ?>"
                            value="<?= htmlspecialchars($values['price']) ?>" min="0" step="0.01" style="padding-left:1.6rem;" />
                    </div>
                    <?php if(isset($errors['price'])): ?><div class="invalid-feedback"><?= $errors['price'] ?></div><?php endif; ?>
                </div>
                <div class="col-md-7">
                    <label>Rating (0–10) <span class="required-star">*</span></label>
                    <div class="rating-row">
                        <span id="ratingDisplay"><?= htmlspecialchars($values['rating']) ?></span>
                        <input type="range" name="rating" id="ratingSlider" min="0" max="10" step="1"
                            value="<?= htmlspecialchars($values['rating']) ?>" />
                    </div>
                    <?php if(isset($errors['rating'])): ?><div class="invalid-feedback"><?= $errors['rating'] ?></div><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title"><i class="bi bi-image-fill"></i> Cover Image</div>
            <label>Image URL <span style="color:var(--steam-muted);font-size:.75rem;text-transform:none;">(optional)</span></label>
            <input type="url" name="cover_path" id="coverUrl"
                class="form-control <?= isset($errors['cover_path'])?'is-invalid':'' ?>"
                value="<?= htmlspecialchars($values['cover_path']) ?>"
                placeholder="https://example.com/game-cover.jpg" />
            <?php if(isset($errors['cover_path'])): ?><div class="invalid-feedback"><?= $errors['cover_path'] ?></div><?php endif; ?>
            <div class="url-hint">💡 Tip: Find a cover on Google → right-click → <strong>Copy image address</strong> → paste here</div>
            <img id="coverPreview" src="" alt="Cover preview" />
        </div>

        <div class="form-footer">
            <button type="submit" class="btn-submit"><i class="bi bi-check-lg"></i> Save Changes</button>
            <a href="index.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const slider = document.getElementById('ratingSlider');
    const display = document.getElementById('ratingDisplay');
    slider.addEventListener('input', () => { display.textContent = slider.value; });

    const coverUrl = document.getElementById('coverUrl');
    const coverPreview = document.getElementById('coverPreview');
    function updatePreview() {
        const url = coverUrl.value.trim();
        if (url) {
            coverPreview.src = url;
            coverPreview.style.display = 'block';
            coverPreview.onerror = () => { coverPreview.style.display = 'none'; };
        } else {
            coverPreview.style.display = 'none';
        }
    }
    coverUrl.addEventListener('input', updatePreview);
    updatePreview();
</script>
</body>
</html>
