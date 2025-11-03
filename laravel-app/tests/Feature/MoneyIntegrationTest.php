<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Support\MoneyService;

class MoneyIntegrationTest extends TestCase
{

    /** @test */
    public function money_service_validates_currencies()
    {
        $this->assertTrue(MoneyService::isValidCurrency('USD'));
        $this->assertTrue(MoneyService::isValidCurrency('EUR'));
        $this->assertTrue(MoneyService::isValidCurrency('GBP'));
        $this->assertTrue(MoneyService::isValidCurrency('RUB'));
        $this->assertFalse(MoneyService::isValidCurrency('INVALID'));
        $this->assertFalse(MoneyService::isValidCurrency('NOTREAL'));
    }

    /** @test */
    public function money_service_creates_money_objects()
    {
        $money = MoneyService::create(10.99, 'USD');
        
        $this->assertEquals('USD', $money->getCurrency()->getCode());
        
        $formatted = MoneyService::format($money);
        $this->assertStringContainsString('10.99', $formatted);
        $this->assertStringContainsString('$', $formatted);
    }

    /** @test */
    public function models_use_money_service_for_supported_currencies()
    {
        $moneyCurrencies = array_keys(MoneyService::getSupportedCurrencies());

        $this->assertContains('USD', $moneyCurrencies);
        $this->assertContains('EUR', $moneyCurrencies);
        $this->assertContains('GBP', $moneyCurrencies);
        $this->assertGreaterThan(3, count($moneyCurrencies));
    }
}
