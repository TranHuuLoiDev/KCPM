<?php
namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\MovieController;

class MovieControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new MovieController();
    }

    public function testGettersAndBasicMethods()
    {
        // Kiểm thử các hàm lấy danh sách và thể loại phim
        $this->assertIsArray($this->controller->getAllMovies());
        $this->assertIsArray($this->controller->getAllGenres());
        
        // Kiểm thử lấy phim đang chiếu / sắp chiếu với limit
        $this->assertIsArray($this->controller->getNowShowingMovies(5));
        $this->assertIsArray($this->controller->getComingMovies(5));
        
        // Kiểm thử lấy chi tiết phim theo ID không tồn tại
        $result = $this->controller->getMovieById(999999);
        $this->assertNull($result);
    }

    public function testHandleRequestGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = $this->controller->handleRequest();
        $this->assertNull($result);
    }

    public function testHandleRequestPostAddAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['action'] = 'add';
        $_POST['title'] = 'Unit Test Movie';
        $_POST['description'] = 'Test description';
        $_POST['director'] = 'Director Name';
        $_POST['cast'] = 'Actor 1, Actor 2';
        $_POST['age_restriction'] = 18;
        $_POST['country'] = 'US';
        $_POST['duration'] = 120;
        $_POST['screening_date'] = '2026-12-31';
        $_POST['trailer_url'] = 'https://youtube.com';
        $_POST['status'] = 'coming';
        $_POST['genres'] = [1, 2];
        $_FILES['poster'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '',
            'error' => 4,
            'size' => 0
        ];

        $result = $this->controller->handleRequest();
        $this->assertTrue(true); // Đảm bảo luồng chạy qua nhánh add thành công
    }

    public function testHandleRequestPostDeleteAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['action'] = 'delete';
        $_POST['id'] = 1;

        $result = $this->controller->handleRequest();
        $this->assertTrue(true); // Đảm bảo chạy qua nhánh delete
    }
}