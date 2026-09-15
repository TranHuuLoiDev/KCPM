<?php
namespace Tests\Services;

use App\Models\ReviewModel;
use App\Models\MovieModel;
use App\Services\ReviewService;
use Tests\Support\AssignmentCases;
use Tests\Support\IsolatedServiceTestCase;

class ReviewAssignmentTest extends IsolatedServiceTestCase
{
    public static function cases(): array
    {
        return AssignmentCases::read('Review', [
            'validateReviewInput'=>['BVA'=>5,'EP'=>3],
            'addReview'=>['BVA'=>5,'EP'=>8],
            'getRatingSummary'=>['EP'=>4],
        ]);
    }

    /** @dataProvider cases */
    public function testAssignment(string $method, string $id, array $row): void
    {
        $review = $this->createMock(ReviewModel::class);
        $movie = $this->createMock(MovieModel::class);
        $service = $this->service(ReviewService::class, ['model'=>$review,'movieModel'=>$movie]);
        if ($method === 'getRatingSummary') {
            $movieId = AssignmentCases::value($row['movieId']);
            // SQL aggregation is verified separately in ReviewSummaryIntegrationTest.
            $expected = $id === 'EP-03' ? ['average_rating'=>3.7,'total_reviews'=>3] : ['average_rating'=>0,'total_reviews'=>0];
            $review->expects($this->exactly($movieId > 0 ? 1 : 0))->method('getRatingSummary')->with($movieId)->willReturn($expected);
            $review->expects($this->never())->method('create');
            $this->assertSame($expected, $service->getRatingSummary($movieId));
            return;
        }
        $status = AssignmentCases::status($row);
        $movie->expects($method === 'validateReviewInput' ? $this->never() : $this->any())->method('getMovieByIdWithGenres')
            ->willReturnCallback(static fn($id) => $id === 1 ? ['id'=>1] : null);
        $created = [];
        $review->expects($this->exactly($method === 'addReview' && $status === 'success' ? 1 : 0))->method('create')
            ->willReturnCallback(static function ($data) use (&$created) { $created[]=$data; return true; });
        if ($method === 'validateReviewInput') {
            $result = $service->validateReviewInput(['rating'=>AssignmentCases::value($row['rating'])]);
            $this->assertSame([], $created);
        } else {
            $args = AssignmentCases::inputs($row, ['userId','movieId','rating','comment']);
            $result = $service->addReview(...$args);
            $this->assertSame($status === 'success' ? [['user_id'=>$args[0],'movie_id'=>$args[1],'rating'=>$args[2],'comment'=>trim($args[3])]] : [], $created);
        }
        $this->assertSame(['status'=>$status,'message'=>$status === 'success'
            ? ($method === 'addReview' ? 'Gửi đánh giá thành công!' : 'Dữ liệu đánh giá hợp lệ!') : AssignmentCases::error($row)], $result);
    }
}
