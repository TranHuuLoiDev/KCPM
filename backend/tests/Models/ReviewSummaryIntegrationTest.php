<?php
namespace Tests\Models;

use App\Models\ReviewModel;
use App\Services\ReviewService;
use Tests\Support\AssignmentCases;
use Tests\Support\IsolatedServiceTestCase;

/** A dedicated connection with a TEMPORARY table never changes persistent reviews. */
class ReviewSummaryIntegrationTest extends IsolatedServiceTestCase
{
    private ?\mysqli $connection = null;

    public static function cases(): array
    {
        return array_filter(\Tests\Services\ReviewAssignmentTest::cases(), static fn($case)=>$case[0]==='getRatingSummary');
    }

    protected function tearDown(): void
    {
        if ($this->connection) $this->connection->close(); // also drops the temporary table
        parent::tearDown();
    }

    /** @dataProvider cases */
    public function testSqlSummary(string $method, string $id, array $row): void
    {
        if (getenv('RUN_DB_ASSIGNMENT_TESTS') !== '1') $this->markTestSkipped('Set RUN_DB_ASSIGNMENT_TESTS=1 to verify MySQL AVG/ROUND/COUNT.');
        $this->connection = new \mysqli(getenv('DB_HOST') ?: '127.0.0.1', getenv('DB_USER') ?: 'root',
            getenv('DB_PASSWORD') ?: '', getenv('DB_NAME') ?: 'movie_ticket_booking', (int)(getenv('DB_PORT') ?: 3306));
        $this->connection->query('CREATE TEMPORARY TABLE reviews (id INT PRIMARY KEY, movie_id INT, rating INT)');
        $this->connection->query('INSERT INTO reviews VALUES (10,2,2)'); // another movie must not affect the result
        if ($id==='EP-03') $this->connection->query('INSERT INTO reviews VALUES (1,1,1),(2,1,5),(3,1,5)');
        $model=$this->service(ReviewModel::class,['conn'=>$this->connection]);
        $service=$this->service(ReviewService::class,['model'=>$model]);
        $this->assertSame($id==='EP-03' ? ['average_rating'=>3.7,'total_reviews'=>3] : ['average_rating'=>0,'total_reviews'=>0],
            $service->getRatingSummary(AssignmentCases::value($row['movieId'])));
    }
}
