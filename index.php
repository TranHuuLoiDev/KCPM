<?php
require_once 'config.php';

use App\Controllers\MovieController;

$movieController = new MovieController();
$nowShowingMovies = $movieController->getNowShowingMovies(8);
$comingMovies = $movieController->getComingMovies(4);

function getMoviePoster($movie, $fallbackText = 'No Image') {
    return !empty($movie['poster'])
        ? $movie['poster']
        : 'https://via.placeholder.com/300x450?text=' . urlencode($fallbackText);
}

function getMovieGenres($movie) {
    return !empty($movie['genre_names']) ? $movie['genre_names'] : 'Chưa cập nhật';
}

function renderMovieCard($movie, $variant = 'now_showing') {
    $poster = getMoviePoster($movie, $variant === 'coming' ? 'Coming Soon' : 'No Image');
    $detailUrl = 'movie_details.php?id=' . (int)$movie['id'];
    $title = $movie['title'] ?? 'Chưa cập nhật';
    $genres = getMovieGenres($movie);
    $duration = (int)($movie['duration'] ?? 0);
    $screeningDate = !empty($movie['screening_date']) ? date('d/m/Y', strtotime($movie['screening_date'])) : 'Chưa cập nhật';
    $trailerUrl = trim($movie['trailer_url'] ?? '');
    ?>
    <div class="col-6 col-md-4 col-lg-3">
        <article class="movie-card h-100">
            <div class="movie-img-wrap">
                <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($title) ?>" onerror="this.src='https://via.placeholder.com/300x450?text=No+Image';">
                <div class="movie-overlay">
                    <?php if ($variant === 'coming' && $trailerUrl !== ''): ?>
                        <a href="<?= htmlspecialchars($trailerUrl) ?>" target="_blank" class="btn-get-ticket">
                            <i class="bi bi-play-fill"></i>
                            Trailer
                        </a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($detailUrl) ?>" class="btn-get-ticket">
                            <i class="bi bi-info-circle-fill"></i>
                            Chi tiết
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="movie-info">
                <h4><?= htmlspecialchars($title) ?></h4>
                <p>
                    <i class="bi bi-film"></i>
                    <span><?= htmlspecialchars($genres) ?></span>
                </p>
                <?php if ($variant === 'coming'): ?>
                    <p>
                        <i class="bi bi-calendar-event-fill"></i>
                        <span><?= htmlspecialchars($screeningDate) ?></span>
                    </p>
                <?php else: ?>
                    <p>
                        <i class="bi bi-clock-fill"></i>
                        <span><?= $duration ?> phút</span>
                    </p>
                <?php endif; ?>
            </div>
        </article>
    </div>
    <?php
}

require_once 'header.php';
?>

<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Thế giới điện ảnh trong tầm tay</h1>
            <p>Đặt vé xem phim bom tấn mới nhất, chọn ghế nhanh và theo dõi lịch chiếu dễ dàng.</p>
            <a href="#movies-list" class="btn-booking">
                <i class="bi bi-ticket-perforated-fill"></i>
                Đặt vé ngay
            </a>
        </div>
    </div>
</section>

<section class="movies-section" id="movies-list">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-4">
            <h2 class="section-title"><i class="bi bi-camera-reels-fill"></i> Phim Đang Chiếu</h2>
            <a href="index.php#movies-list" class="section-link">Xem tất cả</a>
        </div>

        <div class="row g-4">
            <?php if (!empty($nowShowingMovies)): ?>
                <?php foreach ($nowShowingMovies as $movie): ?>
                    <?php renderMovieCard($movie, 'now_showing'); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-movie-state">Hiện chưa có phim nào đang chiếu.</div>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-5 mb-4">
            <h2 class="section-title"><i class="bi bi-hourglass-split"></i> Phim Sắp Chiếu</h2>
        </div>

        <div class="row g-4">
            <?php if (!empty($comingMovies)): ?>
                <?php foreach ($comingMovies as $movie): ?>
                    <?php renderMovieCard($movie, 'coming'); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-movie-state">Hiện chưa có phim nào sắp chiếu.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
