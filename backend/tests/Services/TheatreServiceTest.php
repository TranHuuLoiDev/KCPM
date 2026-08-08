<?php

namespace Tests\Services;

use App\Services\TheatreService;
use PHPUnit\Framework\TestCase;

class TheatreServiceTest extends TestCase
{
    private TheatreService $service;
    private array $insertedIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TheatreService();
    }

    protected function tearDown(): void
    {
        foreach ($this->insertedIds as $id) {
            $this->service->deleteTheatre($id);
        }
        parent::tearDown();
    }

    private function sampleData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rap Test ' . uniqid(),
            'address' => '123 Duong Test',
            'city' => 'Ho Chi Minh',
            'phone' => '0900000000',
            'total_screens' => 5,
        ], $overrides);
    }

    private function insertAndTrack(array $data): int
    {
        $this->service->addTheatre($data);
        $theatres = $this->service->getAllTheatres();
        $found = array_filter($theatres, fn($t) => $t['name'] === $data['name']);
        $theatre = array_values($found)[0];
        $this->insertedIds[] = (int)$theatre['id'];
        return (int)$theatre['id'];
    }

    // ---- Validate ----

    public function testAddTheatreFailsWhenNameEmpty(): void
    {
        $result = $this->service->addTheatre($this->sampleData(['name' => '']));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Tên rạp không được để trống!', $result['message']);
    }

    public function testAddTheatreFailsWhenTotalScreensLessThanOne(): void
    {
        $result = $this->service->addTheatre($this->sampleData(['total_screens' => 0]));

        $this->assertSame('error', $result['status']);
        $this->assertSame('Số phòng chiếu phải lớn hơn 0!', $result['message']);
    }

    // ---- Add ----

    public function testAddTheatreSucceedsWithValidData(): void
    {
        $data = $this->sampleData();
        $result = $this->service->addTheatre($data);

        $this->assertSame('success', $result['status']);

        // dọn dẹp: tìm id vừa tạo để tearDown xoá
        $theatres = $this->service->getAllTheatres();
        $found = array_filter($theatres, fn($t) => $t['name'] === $data['name']);
        $theatre = array_values($found)[0];
        $this->insertedIds[] = (int)$theatre['id'];
    }

    // ---- Update ----

    public function testUpdateTheatreFailsWithInvalidId(): void
    {
        $result = $this->service->updateTheatre(0, $this->sampleData());

        $this->assertSame('error', $result['status']);
        $this->assertSame('ID rạp không hợp lệ!', $result['message']);
    }

    public function testUpdateTheatreFailsWhenTheatreDoesNotExist(): void
    {
        $result = $this->service->updateTheatre(999999, $this->sampleData());

        $this->assertSame('error', $result['status']);
        $this->assertSame('Rạp không tồn tại!', $result['message']);
    }

    public function testUpdateTheatreSucceedsAndPersistsChanges(): void
    {
        $id = $this->insertAndTrack($this->sampleData(['name' => 'Ten Cu']));

        $updated = $this->sampleData(['name' => 'Ten Moi']);
        $result = $this->service->updateTheatre($id, $updated);

        $this->assertSame('success', $result['status']);

        $theatre = $this->service->getTheatreById($id);
        $this->assertSame('Ten Moi', $theatre['name']);
    }

    // ---- Delete ----

    public function testDeleteTheatreFailsWithInvalidId(): void
    {
        $result = $this->service->deleteTheatre(0);

        $this->assertSame('error', $result['status']);
        $this->assertSame('ID rạp không hợp lệ!', $result['message']);
    }

    public function testDeleteTheatreFailsWhenTheatreDoesNotExist(): void
    {
        $result = $this->service->deleteTheatre(999999);

        $this->assertSame('error', $result['status']);
        $this->assertSame('Rạp không tồn tại!', $result['message']);
    }

    public function testDeleteTheatreSucceeds(): void
    {
        $id = $this->insertAndTrack($this->sampleData());

        $result = $this->service->deleteTheatre($id);

        $this->assertSame('success', $result['status']);
        $this->assertNull($this->service->getTheatreById($id));

        // đã xoá thật, không cần tearDown xoá lại
        $this->insertedIds = array_diff($this->insertedIds, [$id]);
    }

    // ---- Get ----

    public function testGetTheatreByIdReturnsNullForInvalidId(): void
    {
        $this->assertNull($this->service->getTheatreById(0));
    }

    public function testGetTheatreByIdReturnsTheatre(): void
    {
        $id = $this->insertAndTrack($this->sampleData(['name' => 'Rap Kiem Tra']));

        $theatre = $this->service->getTheatreById($id);

        $this->assertNotNull($theatre);
        $this->assertSame('Rap Kiem Tra', $theatre['name']);
    }

    public function testGetAllTheatresReturnsArray(): void
    {
        $theatres = $this->service->getAllTheatres();

        $this->assertIsArray($theatres);
    }
}