<?php
require_once '../config.php';
require_once '../app/init.php';
require_once 'admin_header.php';
require_once 'admin_sidebar.php';

use App\Controllers\GenreController;

$controller = new GenreController();
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

// lấy thông tin thể loại nếu sửa
$edit_genre = null;
if (isset($_GET['edit_id'])) {
    $edit_genre = $controller->getGenreById($_GET['edit_id']);
}

$genres_list = $controller->getAllGenres();
?>

<div class="container-fluid">
    <div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="mb-0 text-white fw-bold">Quản lý thể loại</h1>
            <p class="mb-0 mt-2 text-muted">Tổ chức danh mục thể loại để phim dễ lọc, dễ tìm và hiển thị nhất quán.</p>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <script>window.alert('<?= addslashes($success_msg) ?>');</script>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <script>window.alert('Lỗi: <?= addslashes($error_msg) ?>');</script>
    <?php endif; ?>

    <div class="admin-card mb-4">
        <h5 class="mb-3 text-white"><i class="bi bi-tags me-2"></i><?= $edit_genre ? 'Cập nhật thể loại' : 'Thêm thể loại mới' ?></h5>
        <form action="manage_genres.php" method="POST">
            <input type="hidden" name="action" value="<?= $edit_genre ? 'edit' : 'add' ?>">
            <?php if ($edit_genre): ?>
                <input type="hidden" name="id" value="<?= $edit_genre['id'] ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Tên thể loại <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" required placeholder="VD: Hành động, Hài hước..." value="<?= htmlspecialchars($edit_genre['name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả (tùy chọn)</label>
                <textarea class="form-control" name="description" rows="3" placeholder="Mô tả ngắn gọn về thể loại này..."><?= htmlspecialchars($edit_genre['description'] ?? '') ?></textarea>
            </div>
            <div class="mt-3 text-end">
                <?php if ($edit_genre): ?>
                    <a href="manage_genres.php" class="btn btn-admin-secondary me-2">Hủy</a>
                <?php endif; ?>
                <button type="submit" class="btn btn-netflix-red">
                    <?= $edit_genre ? 'Lưu thay đổi' : 'Thêm mới' ?>
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h5 class="mb-3 text-white"><i class="bi bi-tags me-2"></i>Danh sách thể loại</h5>
        <div class="table-responsive">
            <table class="table table-hover admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên thể loại</th>
                        <th>Mô tả</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($genres_list)): ?>
                        <?php foreach ($genres_list as $genre): ?>
                            <tr>
                                <td><?= $genre['id'] ?></td>
                                <td><strong><?= htmlspecialchars($genre['name']) ?></strong></td>
                                <td><?= htmlspecialchars($genre['description'] ?? 'Không có mô tả') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($genre['created_at'])) ?></td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="manage_genres.php?edit_id=<?= $genre['id'] ?>" class="btn btn-sm btn-outline-info admin-icon-btn" title="Sửa thể loại">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <form action="" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thể loại này? Phim thuộc thể loại này sẽ bị gỡ thẻ.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $genre['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger admin-icon-btn" title="Xóa thể loại" aria-label="Xóa thể loại">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-tags me-2"></i>Chưa có thể loại nào. Hãy thêm mới.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
