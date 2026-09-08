<?php

namespace Tests\Services;

use App\Models\MovieModel;
use App\Services\ReviewService;
use PHPUnit\Framework\TestCase;

class ReviewServiceTest extends TestCase
{
    private ReviewService $service;
    private int $validMovieId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ReviewService();

        $movieModel = new MovieModel();
        $movies = $movieModel->getAllMovies();

        if (empty($movies)) {
            $this->markTestSkipped(
                'Database cần có ít nhất một movie để chạy ReviewServiceTest.'
            );
        }

        $this->validMovieId = (int)$movies[0]['id'];
    }

    // ==========================
    // BVA - Review Rating
    // Valid range: 1 -> 5
    // ==========================

    public function testRatingBelowMinimum(): void
    {
        $result = $this->service->validateReviewInput([
            'rating' => 0
        ]);

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng chọn số sao từ 1 đến 5!',
            $result['message']
        );
    }

    public function testRatingAtMinimum(): void
    {
        $result = $this->service->validateReviewInput([
            'rating' => 1
        ]);

        $this->assertSame('success', $result['status']);
    }

    public function testRatingMinPlusOne(): void
    {
        $result = $this->service->validateReviewInput([
            'rating' => 2
        ]);

        $this->assertSame('success', $result['status']);
    }

    public function testRatingMaxMinusOne(): void
    {
        $result = $this->service->validateReviewInput([
            'rating' => 4
        ]);

        $this->assertSame('success', $result['status']);
    }

    public function testRatingAtMaximum(): void
    {
        $result = $this->service->validateReviewInput([
            'rating' => 5
        ]);

        $this->assertSame('success', $result['status']);
    }

    public function testRatingAboveMaximum(): void
    {
        $result = $this->service->validateReviewInput([
            'rating' => 6
        ]);

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng chọn số sao từ 1 đến 5!',
            $result['message']
        );
    }

    // ==========================
    // Shared validation coverage
    // ==========================

    public function testAddReviewUsesSameRatingValidation(): void
    {
        $result = $this->service->addReview(
            1,
            $this->validMovieId,
            0,
            'BVA Review Test'
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng chọn số sao từ 1 đến 5!',
            $result['message']
        );
    }

    public function testValidRatingContinuesToCommentValidation(): void
    {
        $result = $this->service->addReview(
            1,
            $this->validMovieId,
            3,
            ''
        );

        $this->assertSame('error', $result['status']);
        $this->assertSame(
            'Vui lòng nhập nội dung đánh giá!',
            $result['message']
        );
    }
}