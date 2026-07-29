<?php
namespace App\Services;

use App\Models\GenreModel;

class GenreService {
    private $model;

    public function __construct() {
        $this->model = new GenreModel();
    }

    public function addGenre($name, $description) {
        if (empty($name)) {
            return ['status' => 'error', 'message' => 'Tên thể loại không được để trống!'];
        }
        
        $exists = $this->model->findByName($name);
        if ($exists) {
            return ['status' => 'error', 'message' => "Tên thể loại '$name' đã tồn tại!"];
        }

        if ($this->model->insert($name, $description)) {
            return ['status' => 'success', 'message' => 'Thêm thể loại thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi thêm: ' . $this->model->getError()];
    }

    public function updateGenre($id, $name, $description) {
        if ($id <= 0 || empty($name)) {
            return ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ!'];
        }

        $exists = $this->model->findByName($name, $id);
        if ($exists) {
            return ['status' => 'error', 'message' => "Tên thể loại '$name' đã tồn tại ở mục khác!"];
        }

        if ($this->model->update($id, $name, $description)) {
            return ['status' => 'success', 'message' => 'Cập nhật thể loại thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi cập nhật: ' . $this->model->getError()];
    }

    public function deleteGenre($id) {
        if ($id <= 0) {
            return ['status' => 'error', 'message' => 'ID không hợp lệ!'];
        }

        if ($this->model->delete($id)) {
            return ['status' => 'success', 'message' => 'Xóa thể loại thành công!'];
        }
        return ['status' => 'error', 'message' => 'Lỗi khi xóa: ' . $this->model->getError()];
    }

    public function getAllGenres() {
        return $this->model->getAll();
    }

    public function getGenreById($id) {
        if ($id <= 0) return null;
        return $this->model->getById($id);
    }
}
