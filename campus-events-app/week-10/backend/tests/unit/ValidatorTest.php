<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../utils/Validator.php';

/**
 * The simplest kind of test: call a function, check the result.
 * Arrange – Act – Assert.
 */
class ValidatorTest extends TestCase
{
    public function test_required_passes_when_all_fields_are_present()
    {
        Validator::required(['title' => 'Welcome Concert', 'club_id' => 1], ['title', 'club_id']);
        $this->assertTrue(true);    // no exception = test passed
    }

    public function test_required_fails_for_an_empty_field()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Field 'title' is required.");

        Validator::required(['title' => '   '], ['title']);
    }

    public function test_email_rejects_an_invalid_address()
    {
        $this->expectException(ValidationException::class);
        Validator::email('not-an-email');
    }

    public function test_datetime_accepts_the_mysql_format()
    {
        Validator::datetime('2026-11-20 18:00:00', 'starts_at');
        $this->assertTrue(true);
    }

    public function test_datetime_rejects_other_formats()
    {
        $this->expectException(ValidationException::class);
        Validator::datetime('20.11.2026 18:00', 'starts_at');
    }

    public function test_integer_checks_the_range()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Field 'rating' must be at most 5.");
        Validator::integer(7, 'rating', 1, 5);
    }

    public function test_password_needs_a_letter_and_a_number()
    {
        $this->expectException(ValidationException::class);
        Validator::password('onlyletters');
    }

    public function test_date_rejects_impossible_dates()
    {
        $this->expectException(ValidationException::class);
        Validator::date('2026-02-30', 'date');
    }

    public function test_errors_map_to_http_400()
    {
        try {
            Validator::required([], ['name']);
            $this->fail('Exception expected');
        } catch (ValidationException $e) {
            $this->assertSame(400, $e->get_status());
        }
    }
}
