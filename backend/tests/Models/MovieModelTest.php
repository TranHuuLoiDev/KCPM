<?php

namespace Tests\Models;

use App\Models\MovieModel;
use PHPUnit\Framework\TestCase;

class MovieModelTest extends TestCase
{
    private MovieModel $movieModel;
    private array $insertedIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->movieModel = new MovieModel();
    }

    protected function tearDown(): void
    {
        // Dọn dẹp dữ liệu test đã tạo, tránh ảnh hưởng lần chạy sau
        foreach ($this->insertedIds as $id) {
            $this->movieModel->deleteMovie($id);
        }
        parent::tearDown();
    }

    private function sampleMovieData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Phim Test ' . uniqid(),
            'description' => 'Mô tả test',
            'director' => 'Đạo diễn Test',
            'cast' => 'Diễn viên A, Diễn viên B',
            'age_restriction' => 13,
            'country' => 'Vietnam',
            'duration' => 120,
            'screening_date' => date('Y-m-d'),
            'poster' => 'poster.jpg',
            'trailer_url' => 'https://example.com/trailer',
            'status' => 'now_showing',
        ], $overrides);
    }

    public function testInsertMovieReturnsNewId(): void
    {
        $data = $this->sampleMovieData();

        $id = $this->movieModel->insertMovie($data);
        $this->insertedIds[] = $id;

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function testGetAllMoviesReturnsInsertedMovie(): void
    {
        $data = $this->sampleMovieData(['title' => 'Phim Duy Nhat XYZ']);
        $id = $this->movieModel->insertMovie($data);
        $this->insertedIds[] = $id;

        $movies = $this->movieModel->getAllMovies();

        $titles = array_column($movies, 'title');
        $this->assertContains('Phim Duy Nhat XYZ', $titles);
    }

    public function testUpdateMovieChangesTitle(): void
    {
        $data = $this->sampleMovieData(['title' => 'Tieu De Cu']);
        $id = $this->movieModel->insertMovie($data);
        $this->insertedIds[] = $id;

        $updated = $data;
        $updated['title'] = 'Tieu De Moi';
        $result = $this->movieModel->updateMovie($id, $updated);

        $this->assertTrue($result);

        $movie = $this->movieModel->getMovieByIdWithGenres($id);
        $this->assertSame('Tieu De Moi', $movie['title']);
    }

    public function testDeleteMovieRemovesRecord(): void
    {
        $data = $this->sampleMovieData();
        $id = $this->movieModel->insertMovie($data);

        $result = $this->movieModel->deleteMovie($id);
        $this->assertTrue($result);

        $movie = $this->movieModel->getMovieByIdWithGenres($id);
        $this->assertNull($movie);
    }

    public function testGetTotalMoviesReturnsNumericCount(): void
    {
        $count = $this->movieModel->getTotalMovies();

        $this->assertIsNumeric($count);
        $this->assertGreaterThanOrEqual(0, (int)$count);
    }

    public function testGetNowShowingMoviesOnlyReturnsNowShowingStatus(): void
    {
        $data = $this->sampleMovieData(['status' => 'now_showing']);
        $id = $this->movieModel->insertMovie($data);
        $this->insertedIds[] = $id;

        $movies = $this->movieModel->getNowShowingMovies();

        foreach ($movies as $movie) {
            $this->assertSame('now_showing', $movie['status']);
        }
    }
}