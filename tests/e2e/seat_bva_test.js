// UI smoke only; assignment coverage: backend/tests/Services/SeatAssignmentTest.php
Feature('Smoke - Quản lý ghế');

Before(({ I }) => {
  I.amOnPage('/frontend/login.php');

  I.fillField('.sign-in input[name="email"]', 'admin@example.com');
  I.fillField('.sign-in input[name="password"]', 'password');

  I.click('.sign-in button[type="submit"]');
  I.wait(1);
});

Scenario('Mở được trang quản lý ghế', ({ I }) => {
  I.amOnPage('/frontend/admin/manage_seats.php');

  I.see('Quản lý ghế');
});
