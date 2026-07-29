<?php
require_once 'config.php';

use App\Controllers\MovieController;
use App\Controllers\ShowtimeController;
use App\Controllers\ReviewController;

$movieId = (int)($_GET['id'] ?? 0);
if ($movieId <= 0) {
    header('Location: index.php');
    exit;
}

$movieController = new MovieController();
$showtimeController = new ShowtimeController();
$reviewController = new ReviewController();

$movie = $movieController->getMovieById($movieId);
if (!$movie) {
    echo "<script>alert('Phim không tồn tại!'); window.location='index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewResult = $reviewController->handleRequest();
    if ($reviewResult) {
        $_SESSION['review_flash'] = $reviewResult;
    }
    header('Location: movie_details.php?id=' . $movieId);
    exit;
}

$showtimes = $showtimeController->getShowtimesByMovieId($movieId);
$reviews = $reviewController->getReviewsByMovieId($movieId);
$ratingSummary = $reviewController->getRatingSummary($movieId);

$showtimesByDate = [];
foreach ($showtimes as $showtime) {
    $showtimesByDate[$showtime['show_date']][] = $showtime;
}

$poster = !empty($movie['poster']) ? $movie['poster'] : 'https://via.placeholder.com/400x600?text=No+Image';
$genreNames = !empty($movie['genre_names']) ? $movie['genre_names'] : 'Chưa cập nhật';
$averageRating = (float)($ratingSummary['average_rating'] ?? 0);
$totalReviews = (int)($ratingSummary['total_reviews'] ?? 0);
$isLoggedIn = isset($_SESSION['user']) && is_array($_SESSION['user']);
$reviewFlash = $_SESSION['review_flash'] ?? null;
unset($_SESSION['review_flash']);

require_once 'header.php';
?>

<link rel="stylesheet" href="css/movie.css">

<div class="movie-detail-page">
    <div class="container">
        <a href="index.php#movies-list" class="btn btn-outline-light btn-sm mb-4">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách phim
        </a>

        <div class="row g-4 movie-hero-row">
            <div class="col-md-4">
                <div class="movie-detail-poster">
                    <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" onerror="this.src='https://via.placeholder.com/400x600?text=No+Image';">
                </div>
            </div>

            <div class="col-md-8">
                <h1 class="fw-bold mb-4"><?= htmlspecialchars($movie['title']) ?></h1>

                <div class="movie-info-card mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="movie-meta">
                                <i class="bi bi-star-fill movie-rating-icon"></i>
                                <strong>Đánh giá:</strong>
                                <?= number_format($averageRating, 1) ?>/5
                                <span class="text-secondary">(<?= $totalReviews ?> lượt)</span>
                            </p>
                            <p class="movie-meta">
                                <i class="bi bi-clock-fill"></i>
                                <strong>Thời lượng:</strong> <?= (int)$movie['duration'] ?> phút
                            </p>
                            <p class="movie-meta">
                                <i class="bi bi-film"></i>
                                <strong>Thể loại:</strong> <?= htmlspecialchars($genreNames) ?>
                            </p>
                            <p class="movie-meta mb-md-0">
                                <i class="bi bi-shield-fill-check"></i>
                                <strong>Độ tuổi:</strong>
                                <?= (int)$movie['age_restriction'] > 0 ? (int)$movie['age_restriction'] . '+' : 'Phù hợp mọi lứa tuổi' ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="movie-meta">
                                <i class="bi bi-calendar-event-fill"></i>
                                <strong>Khởi chiếu:</strong>
                                <?= !empty($movie['screening_date']) ? date('d/m/Y', strtotime($movie['screening_date'])) : 'Chưa cập nhật' ?>
                            </p>
                            <p class="movie-meta">
                                <i class="bi bi-person-video2"></i>
                                <strong>Đạo diễn:</strong> <?= htmlspecialchars($movie['director'] ?: 'Chưa cập nhật') ?>
                            </p>
                            <p class="movie-meta mb-0">
                                <i class="bi bi-globe2"></i>
                                <strong>Quốc gia:</strong> <?= htmlspecialchars($movie['country'] ?: 'Chưa cập nhật') ?>
                            </p>
                        </div>
                    </div>
                </div>

                <h3 class="section-title">Diễn viên</h3>
                <p class="movie-text mb-4"><?= nl2br(htmlspecialchars($movie['cast'] ?: 'Chưa cập nhật')) ?></p>

                <h3 class="section-title">Nội dung phim</h3>
                <p class="movie-text text-align-justify mb-4"><?= nl2br(htmlspecialchars($movie['description'] ?: 'Chưa cập nhật')) ?></p>

                <?php if (!empty($movie['trailer_url'])): ?>
                    <a href="<?= htmlspecialchars($movie['trailer_url']) ?>" target="_blank" class="btn btn-warning fw-bold">
                        <i class="bi bi-play-fill"></i> Xem Trailer
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <section class="mt-5">
            <h2 class="section-title"><i class="bi bi-calendar3"></i> Lịch Chiếu</h2>

            <?php if (!empty($showtimesByDate)): ?>
                <div class="movie-info-card showtime-scroll-container">
                    <?php foreach ($showtimesByDate as $showDate => $dateShowtimes): ?>
                        <div class="showtime-date-group">
                            <h4 class="showtime-date-title">
                                <i class="bi bi-calendar"></i>
                                <?= date('d/m/Y', strtotime($showDate)) ?>
                            </h4>
                            <div class="row g-3">
                                <?php foreach ($dateShowtimes as $showtime): ?>
                                    <div class="col-12">
                                        <div class="showtime-card">
                                            <h5 class="mb-2">
                                                <i class="bi bi-geo-alt-fill"></i>
                                                <?= htmlspecialchars($showtime['theatre_name']) ?>
                                            </h5>
                                            <p class="showtime-address small mb-2">
                                                <?= htmlspecialchars(trim(($showtime['theatre_address'] ?? '') . ', ' . ($showtime['theatre_city'] ?? ''), ', ')) ?>
                                            </p>
                                            <div class="showtime-divider">
                                                <span class="showtime-meta me-3">
                                                    <i class="bi bi-clock"></i>
                                                    <?= date('H:i', strtotime($showtime['start_time'])) ?> - <?= date('H:i', strtotime($showtime['end_time'])) ?>
                                                </span>
                                                <span class="showtime-meta">
                                                    <i class="bi bi-display"></i>
                                                    <?= htmlspecialchars($showtime['room_name']) ?>
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                                                <span class="showtime-price">
                                                    <?= number_format((float)$showtime['base_price'], 0, ',', '.') ?>đ
                                                </span>
                                                <?php if ($isLoggedIn): ?>
                                                    <a href="booking.php?showtime_id=<?= (int)$showtime['showtime_id'] ?>" class="btn btn-booking-red">
                                                        <i class="bi bi-ticket-perforated-fill"></i> Đặt vé
                                                    </a>
                                                <?php else: ?>
                                                    <a href="login.php" class="btn btn-warning fw-bold">
                                                        Đăng nhập để đặt vé
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="movie-info-card empty-state">
                    Hiện tại chưa có lịch chiếu cho phim này.
                </div>
            <?php endif; ?>
        </section>

        <section class="mt-5">
            <h2 class="section-title"><i class="bi bi-chat-square-text-fill"></i> Đánh Giá</h2>

            <div class="movie-info-card review-section-card">
                <div class="review-form-card review-list-card">
                <h5 class="text-white mb-3">Viết đánh giá của bạn</h5>
                <?php if ($reviewFlash): ?>
                    <div class="alert <?= $reviewFlash['status'] === 'success' ? 'alert-success' : 'alert-danger' ?> py-2">
                        <?= htmlspecialchars($reviewFlash['message']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($isLoggedIn): ?>
                    <form method="POST" action="movie_details.php?id=<?= (int)$movieId ?>">
                        <input type="hidden" name="action" value="add_review">
                        <input type="hidden" name="movie_id" value="<?= (int)$movieId ?>">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Chọn số sao</label>
                            <div class="rating-stars">
                                <input type="radio" name="rating" id="rating_5" value="5" required>
                                <label for="rating_5"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" id="rating_4" value="4">
                                <label for="rating_4"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" id="rating_3" value="3">
                                <label for="rating_3"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" id="rating_2" value="2">
                                <label for="rating_2"><i class="bi bi-star-fill"></i></label>
                                <input type="radio" name="rating" id="rating_1" value="1">
                                <label for="rating_1"><i class="bi bi-star-fill"></i></label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control bg-dark text-white border-secondary" name="comment" rows="4" placeholder="Chia sẻ cảm nhận của bạn về phim..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-booking-red">
                            <i class="bi bi-send-fill"></i> Gửi đánh giá
                        </button>
                    </form>
                <?php else: ?>
                    <p class="movie-text mb-3">Bạn cần đăng nhập để viết đánh giá cho phim này.</p>
                    <a href="login.php" class="btn btn-warning fw-bold">Đăng nhập</a>
                <?php endif; ?>
                </div>

                <div class="review-list-card">
                    <h4 class="text-white mb-3">Đánh giá từ khán giả</h4>
                    <?php if (!empty($reviews)): ?>
                        <div class="review-scroll-container">
                    <div class="row g-3">
                        <?php foreach ($reviews as $review): ?>
                            <div class="col-md-6">
                                <div class="review-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                        <div>
                                            <h5 class="mb-1 text-white">
                                                <?= htmlspecialchars(trim($review['first_name'] . ' ' . $review['last_name'])) ?>
                                            </h5>
                                            <div class="review-date small">
                                                <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                                            </div>
                                        </div>
                                        <div class="review-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi <?= $i <= (int)$review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="movie-text mb-0"><?= nl2br(htmlspecialchars($review['comment'] ?: 'Không có bình luận.')) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            Phim này chưa có đánh giá nào.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once 'footer.php'; ?>
