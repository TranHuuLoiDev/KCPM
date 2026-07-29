<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\MovieController;

$controller = new MovieController();
$actionResult = $controller->handleRequest();

$success_msg = '';
$error_msg = '';

if ($actionResult) {
    if ($actionResult['status'] === 'success') {
        $success_msg = $actionResult['message'];
    } else {
        $error_msg = $actionResult['message'];
    }
}

// lấy thông tin phim nếu sửa
$edit_movie = null;
if (isset($_GET['edit_id'])) {
    $edit_movie = $controller->getMovieById($_GET['edit_id']);
}

$genres_list = $controller->getAllGenres();
$movies_result = $controller->getAllMovies();

function getAdminPosterSrc($poster) {
    $poster = trim($poster ?? '');
    if ($poster === '') {
        return 'https://via.placeholder.com/300x450?text=No+Image';
    }
    if (preg_match('/^https?:\/\//i', $poster)) {
        return $poster;
    }
    return '../' . ltrim($poster, '/');
}
?>

<div class="container-fluid">
    <div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-0 text-white fw-bold">Quản lý phim</h1>
            <p class="mb-0 mt-2 text-muted">Theo dõi danh mục phim, thông tin phát hành, thể loại và trạng thái chiếu.</p>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <script>window.alert('<?= addslashes($success_msg) ?>');</script>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <script>window.alert('Lỗi: <?= addslashes($error_msg) ?>');</script>
    <?php endif; ?>

    <div class="admin-card mb-4">
        <h5 class="mb-3 text-white"><i class="bi bi-camera-reels me-2"></i><?= $edit_movie ? 'Cập nhật phim' : 'Thêm phim mới' ?></h5>
        <form action="manage_movies.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_movie ? 'edit' : 'add' ?>">
            <?php if ($edit_movie): ?>
                <input type="hidden" name="id" value="<?= $edit_movie['id'] ?>">
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="mb-3">
                        <label class="form-label">Tên phim <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required value="<?= htmlspecialchars($edit_movie['title'] ?? '') ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Đạo diễn</label>
                            <input type="text" class="form-control" name="director" value="<?= htmlspecialchars($edit_movie['director'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới hạn tuổi</label>
                            <input type="number" class="form-control" name="age_restriction" min="0" value="<?= htmlspecialchars($edit_movie['age_restriction'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diễn viên</label>
                        <input type="text" class="form-control" name="cast" placeholder="Cách nhau bằng dấu phẩy" value="<?= htmlspecialchars($edit_movie['cast'] ?? '') ?>">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Quốc gia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="country" required value="<?= htmlspecialchars($edit_movie['country'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Thời lượng (phút) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="duration" required min="1" value="<?= htmlspecialchars($edit_movie['duration'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ngày khởi chiếu <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="screening_date" required value="<?= htmlspecialchars($edit_movie['screening_date'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($edit_movie['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="mb-3">
                        <label class="form-label">Poster (JPG, PNG)</label>
                        <input type="file" class="form-control" name="poster" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                        <?php if ($edit_movie && !empty($edit_movie['poster'])): ?>
                            <small class="text-muted d-block mt-1">Đang dùng poster hiện tại. Chọn file mới nếu muốn thay đổi.</small>
                        <?php endif; ?>
                        <small class="text-muted">Chỉ nhận file JPG hoặc PNG. Khi cập nhật phim, bỏ trống nếu muốn giữ poster hiện tại.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trailer (URL)</label>
                        <input type="text" class="form-control" name="trailer_url" placeholder="https://youtube.com/..." value="<?= htmlspecialchars($edit_movie['trailer_url'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="coming" <?= ($edit_movie['status'] ?? '') == 'coming' ? 'selected' : '' ?>>Sắp chiếu</option>
                            <option value="now_showing" <?= ($edit_movie['status'] ?? 'now_showing') == 'now_showing' ? 'selected' : '' ?>>Đang chiếu</option>
                            <option value="ended" <?= ($edit_movie['status'] ?? '') == 'ended' ? 'selected' : '' ?>>Ngừng chiếu</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block border-bottom border-secondary pb-2">Thể loại phim</label>
                        <div class="row g-2 mt-1">
                            <?php
                            $selected_genres = [];
                            if ($edit_movie && !empty($edit_movie['genre_ids'])) {
                                $selected_genres = explode(',', $edit_movie['genre_ids']);
                            }
                            foreach ($genres_list as $g):
                                $isChecked = in_array($g['id'], $selected_genres) ? 'checked' : '';
                            ?>
                                <div class="col-6 form-check">
                                    <input class="form-check-input" type="checkbox" name="genres[]"
                                           value="<?= $g['id'] ?>" id="g_<?= $g['id'] ?>" <?= $isChecked ?>>
                                    <label class="form-check-label" for="g_<?= $g['id'] ?>">
                                        <?= htmlspecialchars($g['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-end">
                <?php if ($edit_movie): ?>
                    <a href="manage_movies.php" class="btn btn-admin-secondary me-2">Hủy</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-netflix-red">
                    <?= $edit_movie ? 'Lưu thay đổi' : 'Thêm phim' ?>
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h5 class="mb-3 text-white"><i class="bi bi-camera-reels me-2"></i>Danh sách phim</h5>
        <div class="table-responsive">
            <table class="table table-hover admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên phim</th>
                        <th>Thể loại</th>
                        <th>Thời lượng</th>
                        <th>Khởi chiếu</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movies_result)): ?>
                        <?php foreach ($movies_result as $movie): ?>
                            <tr>
                                <td><?= $movie['id'] ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars(getAdminPosterSrc($movie['poster'])) ?>"
                                         alt="<?= htmlspecialchars($movie['title']) ?>" style="width: 48px; height: 68px; object-fit: cover; border-radius: 6px;"
                                         onerror="this.src='https://via.placeholder.com/300x450?text=No+Image';">
                                </td>
                                <td>
                                    <div class="fw-bold text-white"><?= htmlspecialchars($movie['title']) ?></div>
                                    <span class="badge bg-danger mt-1"><?= $movie['age_restriction'] ?>+</span>
                                </td>
                                <td>
                                    <span class="text-muted"><?= htmlspecialchars($movie['genre_names'] ?? 'Chưa cập nhật') ?></span>
                                </td>
                                <td><?= $movie['duration'] ?> phút</td>
                                <td>
                                    <?= !empty($movie['screening_date']) ? date('d/m/Y', strtotime($movie['screening_date'])) : 'Chưa cập nhật' ?>
                                </td>
                                <td>
                                    <?php if ($movie['status'] == 'now_showing'): ?>
                                        <span class="badge bg-success">Đang chiếu</span>
                                    <?php elseif ($movie['status'] == 'coming'): ?>
                                        <span class="badge bg-warning text-dark">Sắp chiếu</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ngừng chiếu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="manage_movies.php?edit_id=<?= $movie['id'] ?>" class="btn btn-sm btn-outline-info admin-icon-btn" title="Sửa phim">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form action="" method="POST" class="d-inline" onsubmit="return confirm('CẢNH BÁO: Xóa phim này sẽ xóa toàn bộ suất chiếu, vé và đánh giá liên quan. Bạn có chắc chắn không?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $movie['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Xóa phim" >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-camera-reels me-2"></i>Chưa có phim nào. Hãy thêm phim đầu tiên.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
