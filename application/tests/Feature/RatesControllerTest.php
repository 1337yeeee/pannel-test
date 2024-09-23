<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Testing\Fluent\AssertableJson;

class RatesControllerTest extends TestCase
{
    /**
     * Test getting all the rates
     */
    public function test_index_returns_all_rates()
    {
        $currencyServiceMock = Mockery::mock(CurrencyService::class);
        $currencyServiceMock->shouldReceive('rates')
            ->once()
            ->andReturn([
                'USD' => 1.0,
                'EUR' => 0.85,
                'GBP' => 0.75
            ]);

        $this->instance(CurrencyService::class, $currencyServiceMock);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1?method=rates');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success')
                     ->where('code', 200)
                     ->has('data.USD')
                     ->has('data.EUR')
                     ->has('data.GBP');
            });
    }

    /**
     * Test getting rates specific currency
     */
    public function test_index_returns_specific_currency_rate()
    {
        $currencyServiceMock = Mockery::mock(CurrencyService::class);
        $currencyServiceMock->shouldReceive('rates')
            ->once()
            ->andReturn([
                'USD' => 1.0,
                'EUR' => 0.85,
                'GBP' => 0.75
            ]);

        $this->instance(CurrencyService::class, $currencyServiceMock);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1?method=rates&currency=USD');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success')
                     ->where('code', 200)
                     ->where('currency', 'USD')
                     ->has('data.USD');
            });
    }

    /**
     * Test when currency not found
     */
    public function test_index_returns_all_rates_when_currency_not_found()
    {
        $currencyServiceMock = Mockery::mock(CurrencyService::class);
        $currencyServiceMock->shouldReceive('rates')
            ->once()
            ->andReturn([
                'USD' => 1.0,
                'EUR' => 0.85,
                'GBP' => 0.75
            ]);

        $this->instance(CurrencyService::class, $currencyServiceMock);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1?method=rates&currency=ABC');

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->where('status', 'success')
                     ->where('code', 200)
                     ->missing('currency')
                     ->has('data.USD')
                     ->has('data.EUR')
                     ->has('data.GBP');
            });
    }
}
