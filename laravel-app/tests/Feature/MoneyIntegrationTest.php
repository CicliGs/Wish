<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Support\MoneyHelper;

class MoneyIntegrationTest extends TestCase
{

    /** @test */
    public function money_helper_validates_currencies()
    {
        $this->assertTrue(MoneyHelper::isValidCurrency('USD'));
        $this->assertTrue(MoneyHelper::isValidCurrency('EUR'));
        $this->assertTrue(MoneyHelper::isValidCurrency('GBP'));
        $this->assertTrue(MoneyHelper::isValidCurrency('RUB'));
        $this->assertFalse(MoneyHelper::isValidCurrency('INVALID'));
        $this->assertFalse(MoneyHelper::isValidCurrency('NOTREAL'));
    }

    /** @test */
    public function money_helper_creates_money_objects()
    {
        $money = MoneyHelper::create(10.99, 'USD');
        
        $this->assertEquals('USD', $money->getCurrency()->getCode());
        
        $formatted = MoneyHelper::format($money);
        $this->assertStringContainsString('10.99', $formatted);
        $this->assertStringContainsString('$', $formatted);
    }

    /** @test */
    public function models_use_money_helper_for_supported_currencies()
    {
        $moneyCurrencies = array_keys(MoneyHelper::getSupportedCurrencies());

        $this->assertContains('USD', $moneyCurrencies);
        $this->assertContains('EUR', $moneyCurrencies);
        $this->assertContains('GBP', $moneyCurrencies);
        $this->assertGreaterThan(3, count($moneyCurrencies));
    }
}
