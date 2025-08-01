<?php
require_once 'server.php';
include 'header.php';

// קבלת מזהה הפוסטר
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) {
    echo "<p>❌ פוסטר לא קיים</p>";
    include 'footer.php';
    exit;
}

$stmt = $conn->prepare("SELECT * FROM posters WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<p>❌ פוסטר לא קיים</p>";
    include 'footer.php';
    exit;
}
$row = $result->fetch_assoc();
$stmt->close();

// פונקציה קצרה להצגת ערך (אם ריק - "לא זמין")
function show($val) {
    return $val !== null && $val !== '' ? htmlspecialchars($val, ENT_QUOTES, 'UTF-8') : '<span style="color:#aaa;">לא זמין</span>';
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= show($row['title_he'] ?: $row['title_en']) ?> | מידע מלא</title>
    <style>
        .poster-wrap { display: flex; gap: 35px; align-items: flex-start; margin: 32px; direction: rtl; }
        .poster-image { border-radius: 13px; box-shadow: 0 2px 14px #0002; width: 255px; min-width: 180px; background: #eee; }
        .meta-basic { background: #f6f9fc; border-radius: 10px; margin-top: 15px; padding: 10px 15px; box-shadow: 0 1px 6px #0001; }
        .poster-details { flex: 1; min-width: 300px; }
        .field-label { color: #0b5bb8; font-weight: bold; display: inline-block; width: 128px; }
        .basic-row { margin-bottom: 10px; }
        .meta-basic .basic-row { border-bottom: 1px solid #eee; padding: 3px 0 4px 0; }
        .poster-title { font-size: 2.0em; font-weight: bold; margin: 0 0 7px 0; }
        .aka-title { font-size: 1.1em; color: #666; }
        .poster-actions { margin-top: 15px; }
        .label-badge { display: inline-block; padding: 1px 10px; border-radius: 10px; background: #f2f5fa; margin-left: 6px; font-size: 0.93em; }
        .network-logo { vertical-align: middle; margin-left: 7px; border-radius: 4px; box-shadow: 0 1px 4px #0002; background: #fff; }
        .sub-dub-badge { background: #e0eaff; color: #0b5bb8; margin-left: 6px; font-weight: bold; }
        .data-table td { padding: 6px 10px; }
        .data-table { border-spacing: 0; }
    </style>
</head>
<body>

<div class="poster-wrap">
    <!-- Poster image & meta-basic -->
    <div>
        <img class="poster-image" src="<?= show($row['image_url']) ?>" alt="פוסטר" onerror="this.src='noposter.jpg';">
        <div class="meta-basic">
            <div class="basic-row">
                <span class="field-label">אהבתי:</span> <?= show($row['imdb_rating']) ?>
            </div>
            <div class="basic-row">
                <span class="field-label">לא אהבתי:</span> <!-- כאן אפשר להוסיף future שדה -->
            </div>
            <div class="basic-row">
                <span class="field-label">שנה:</span> <?= show($row['year']) ?>
            </div>
            <div class="basic-row">
                <span class="field-label">סוג:</span> <?= show($row['type_id']) ?>
            </div>
            <div class="basic-row">
                <span class="field-label">IMDb ID:</span>
                <a href="https://www.imdb.com/title/<?= show($row['imdb_id']) ?>" target="_blank"><?= show($row['imdb_id']) ?></a>
            </div>
        </div>
    </div>

    <!-- Poster details -->
    <div class="poster-details">
        <div class="poster-title"><?= show($row['title_he'] ?: $row['title_en']) ?></div>
        <?php if ($row['title_en'] && $row['title_he'] && $row['title_en'] !== $row['title_he']): ?>
            <div class="aka-title"><?= show($row['title_en']) ?></div>
        <?php endif; ?>

        <table class="data-table">
            <tr>
                <td class="field-label">רשת:</td>
                <td>
                    <?= show($row['network']) ?>
                    <?php if ($row['network_logo']): ?>
                        <img class="network-logo" src="<?= show($row['network_logo']) ?>" height="24">
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="field-label">עונות:</td>
                <td><?= show($row['seasons_count']) ?></td>
            </tr>
            <tr>
                <td class="field-label">פרקים:</td>
                <td><?= show($row['episodes_count']) ?></td>
            </tr>
            <tr>
                <td class="field-label">שפות:</td>
                <td><?= show($row['languages']) ?></td>
            </tr>
            <tr>
                <td class="field-label">מדינות:</td>
                <td><?= show($row['countries']) ?></td>
            </tr>
            <tr>
                <td class="field-label">ז'אנר:</td>
                <td><?= show($row['genre']) ?></td>
            </tr>
            <tr>
                <td class="field-label">שחקנים:</td>
                <td><?= show($row['actors']) ?></td>
            </tr>
            <tr>
                <td class="field-label">במאים:</td>
                <td><?= show($row['directors']) ?></td>
            </tr>
            <tr>
                <td class="field-label">תסריטאים:</td>
                <td><?= show($row['writers']) ?></td>
            </tr>
            <tr>
                <td class="field-label">מפיקים:</td>
                <td><?= show($row['producers']) ?></td>
            </tr>
            <tr>
                <td class="field-label">צלמים:</td>
                <td><?= show($row['cinematographers']) ?></td>
            </tr>
            <tr>
                <td class="field-label">מלחינים:</td>
                <td><?= show($row['composers']) ?></td>
            </tr>
            <tr>
                <td class="field-label">אורך:</td>
                <td><?= show($row['runtime']) ?> דקות</td>
            </tr>
            <tr>
                <td class="field-label">קולקציית TMDB:</td>
                <td><?= show($row['tmdb_collection_id']) ?></td>
            </tr>
            <tr>
                <td class="field-label">יש כתוביות?</td>
                <td>
                    <?= $row['has_subtitles'] ? '<span class="sub-dub-badge">✔️ כן</span>' : '<span style="color:#aaa;">לא</span>' ?>
                </td>
            </tr>
            <tr>
                <td class="field-label">מדובב?</td>
                <td>
                    <?= $row['is_dubbed'] ? '<span class="sub-dub-badge">✔️ כן</span>' : '<span style="color:#aaa;">לא</span>' ?>
                </td>
            </tr>
            <tr>
                <td class="field-label">TVDB ID:</td>
                <td><?= show($row['tvdb_id']) ?></td>
            </tr>
            <tr>
                <td class="field-label">טריילר יוטיוב:</td>
                <td>
                    <?php if ($row['youtube_trailer']): ?>
                        <a href="https://youtu.be/<?= show($row['youtube_trailer']) ?>" target="_blank">צפה</a>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="field-label">תקציר:</td>
                <td><?= show($row['plot']) ?></td>
            </tr>
            <tr>
                <td class="field-label">תקציר (עברית):</td>
                <td><?= show($row['plot_he']) ?></td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
<?php include 'footer.php'; ?>
