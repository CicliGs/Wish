<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Requests\StoreWishRequest;
use App\Http\Requests\UpdateWishRequest;
use App\Support\MoneyHelper;
use Illuminate\Support\Facades\Validator;

class WishRequestTest extends TestCase
{
    /** @test */
    public function store_wish_request_validates_basic_data()
    {
        $data = [
            'title' => 'Test Wish',
            'url' => 'https://example.com',
            'price' => '29.99',
        ];

        $request = new StoreWishRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function store_wish_request_validates_currency()
    {
        $data = [
            'title' => 'Test Wish',
            'currency' => 'USD',
            'price' => '29.99',
        ];

        $request = new StoreWishRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function store_wish_request_rejects_invalid_currency()
    {
        $data = [
            'title' => 'Test Wish',
            'currency' => 'INVALID',
            'price' => '29.99',
        ];

        $request = new StoreWishRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('currency', $validator->errors()->toArray());
    }

    /** @test */
    public function store_wish_request_requires_title()
    {
        $data = [
            'url' => 'https://example.com',
            'price' => '29.99',
        ];

        $request = new StoreWishRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function store_wish_request_validates_price_format()
    {
        $data = [
            'title' => 'Test Wish',
            'price' => 'not-a-number',
        ];

        $request = new StoreWishRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
    }

    /** @test */
    public function update_wish_request_validates_basic_data()
    {
        $data = [
            'title' => 'Updated Wish',
            'url' => 'https://updated-example.com',
            'price' => '39.99',
        ];

        $request = new UpdateWishRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function update_wish_request_validates_currency()
    {
        $data = [
            'title' => 'Updated Wish',
            'currency' => 'EUR',
            'price' => '39.99',
        ];

        $request = new UpdateWishRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function money_helper_supports_request_currencies()
    {
        $supportedCurrencies = array_keys(MoneyHelper::getSupportedCurrencies());
        
        foreach (['USD', 'EUR', 'GBP', 'RUB'] as $currency) {
            $this->assertContains($currency, $supportedCurrencies);
            
            $data = [
                'title' => 'Test Wish',
                'currency' => $currency,
                'price' => '29.99',
            ];

            $request = new StoreWishRequest();
            $validator = Validator::make($data, $request->rules());

            $this->assertTrue($validator->passes(), "Currency $currency should be valid");
        }
    }

    /** @test */
    public function request_can_get_wish_data()
    {
        $originalData = [
            'title' => 'Test Wish',
            'url' => 'https://example.com',
            'image' => '/storage/test.jpg',
            'price' => '29.99',
        ];

        // Create a mock request with validated data
        $request = $this->getMockBuilder(StoreWishRequest::class)
            ->onlyMethods(['validated'])
            ->getMock();

        $request->expects($this->exactly(4))
            ->method('validated')
            ->willReturnMap([
                ['title', $originalData['title']],
                ['url', $originalData['url']],
                ['image', $originalData['image']],
                ['price', (float) $originalData['price']],
            ]);

        $wishData = $request->getWishData();

        $this->assertEquals($originalData['title'], $wishData['title']);
        $this->assertEquals($originalData['url'], $wishData['url']);
        $this->assertEquals($originalData['image'], $wishData['image']);
        $this->assertEquals(29.99, $wishData['price']);
    }
}
