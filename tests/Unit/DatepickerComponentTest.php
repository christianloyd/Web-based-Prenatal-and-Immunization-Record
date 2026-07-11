<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;

class DatepickerComponentTest extends TestCase
{
    use InteractsWithViews;

    public function test_datepicker_renders_with_min_and_max_dates()
    {
        $view = $this->blade(
            '<x-datepicker name="test_date" min-date="2026-07-01" max-date="2026-07-31" />'
        );

        $view->assertSee('datepicker-min-date="2026-07-01"', false);
        $view->assertSee('datepicker-max-date="2026-07-31"', false);
    }

    public function test_datepicker_renders_without_min_and_max_dates()
    {
        $view = $this->blade(
            '<x-datepicker name="test_date" />'
        );

        $view->assertDontSee('datepicker-min-date', false);
        $view->assertDontSee('datepicker-max-date', false);
    }
}
