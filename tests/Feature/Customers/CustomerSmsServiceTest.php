<?php

namespace Tests\Feature\Customers;

use App\Models\Setting;
use App\Services\Customers\CustomerSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSmsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_template_uses_only_documented_variables(): void
    {
        $documented = array_keys(CustomerSmsService::paymentRequestVariables());

        preg_match_all('/\{[a-z_]+\}/', CustomerSmsService::DEFAULT_PAYMENT_REQUEST_TEMPLATE, $matches);
        $tokensInTemplate = array_unique($matches[0]);

        foreach ($tokensInTemplate as $token) {
            $this->assertContains(
                $token,
                $documented,
                "Default template uses {$token} but it is not in paymentRequestVariables(); the SMS will ship with a literal placeholder."
            );
        }
    }

    public function test_substitution_replaces_every_documented_token(): void
    {
        $vars = [];
        foreach (CustomerSmsService::paymentRequestVariables() as $token => $_label) {
            $vars[$token] = strtoupper(trim($token, '{}'));
        }

        $rendered = strtr(CustomerSmsService::DEFAULT_PAYMENT_REQUEST_TEMPLATE, $vars);

        $this->assertStringNotContainsString('{name}', $rendered);
        $this->assertStringNotContainsString('{city}', $rendered);
        $this->assertStringNotContainsString('{period}', $rendered);
        $this->assertStringNotContainsString('{amount}', $rendered);
        $this->assertStringNotContainsString('{iban}', $rendered);

        $this->assertStringContainsString('NAME', $rendered);
        $this->assertStringContainsString('CITY', $rendered);
        $this->assertStringContainsString('PERIOD', $rendered);
        $this->assertStringContainsString('AMOUNT', $rendered);
        $this->assertStringContainsString('IBAN', $rendered);
    }

    public function test_empty_variable_values_collapse_to_empty_string_not_literal_brace(): void
    {
        $vars = [];
        foreach (CustomerSmsService::paymentRequestVariables() as $token => $_label) {
            $vars[$token] = '';
        }

        $rendered = strtr(CustomerSmsService::DEFAULT_PAYMENT_REQUEST_TEMPLATE, $vars);

        $this->assertStringNotContainsString('{name}', $rendered);
        $this->assertStringNotContainsString('{city}', $rendered);
        $this->assertStringNotContainsString('{period}', $rendered);
        $this->assertStringNotContainsString('{amount}', $rendered);
        $this->assertStringNotContainsString('{iban}', $rendered);
    }

    public function test_default_template_returned_when_no_setting_stored(): void
    {
        $this->assertSame(
            CustomerSmsService::DEFAULT_PAYMENT_REQUEST_TEMPLATE,
            Setting::get(
                CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY,
                CustomerSmsService::DEFAULT_PAYMENT_REQUEST_TEMPLATE,
            ),
        );
    }

    public function test_setting_set_overrides_default_template(): void
    {
        $custom = 'Hello {name}, please pay {amount} EUR to {iban}.';

        Setting::set(CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY, $custom);

        $this->assertSame(
            $custom,
            Setting::get(
                CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY,
                CustomerSmsService::DEFAULT_PAYMENT_REQUEST_TEMPLATE,
            ),
        );
    }

    public function test_setting_set_updates_existing_value(): void
    {
        Setting::set(CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY, 'first');
        Setting::set(CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY, 'second');

        $this->assertSame('second', Setting::get(CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY));
        $this->assertSame(1, Setting::query()->where('key', CustomerSmsService::PAYMENT_REQUEST_TEMPLATE_KEY)->count());
    }

    public function test_variable_list_is_documented_for_every_token_in_default_template(): void
    {
        preg_match_all('/\{[a-z_]+\}/', CustomerSmsService::DEFAULT_PAYMENT_REQUEST_TEMPLATE, $matches);

        $this->assertNotEmpty($matches[0], 'Default template should contain at least one placeholder.');

        foreach (CustomerSmsService::paymentRequestVariables() as $token => $label) {
            $this->assertMatchesRegularExpression('/^\{[a-z_]+\}$/', $token, "Variable token {$token} must use {snake_case} form.");
            $this->assertNotEmpty($label, "Variable {$token} must have a human-readable label.");
        }
    }
}
