<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FlatpickrThemeTest extends TestCase
{
    /**
     * The plugin ships this file with a full copy of an older Filament CSS. It loads after the
     * panel theme on every page with a date picker and breaks newer components, for example it
     * colours every status toggle button. `vendor:publish --tag=flatpickr-assets` brings it back.
     */
    public function test_published_flatpickr_theme_has_no_filament_component_css(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/public/vendor/flatpickr/themes/default.css');

        $this->assertStringContainsString('.flatpickr-calendar', $css);
        $this->assertStringNotContainsString('@layer components', $css);
        $this->assertStringNotContainsString('.fi-btn', $css);
    }
}
