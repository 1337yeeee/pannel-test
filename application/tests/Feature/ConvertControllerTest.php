<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConvertControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful convert
     */
    public function test_store_converts_currency_successfully()
    {
        $currencyServiceMock = Mockery::mock(CurrencyService::class);
        $currencyServiceMock->shouldReceive('convert')
            ->once()
            ->with('USD', 'EUR')
            ->andReturn(0.85);
        $currencyServiceMock->shouldReceive('roundConvertedValue')
            ->once()
            ->with(85.0, 'USD', 'EUR')
            ->andReturn(85.0);

        $this->instance(CurrencyService::class, $currencyServiceMock);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1?method=convert', [
            'currency_from' => 'USD',
            'currency_to' => 'EUR',
            'value' => 100.0,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'currency_from' => 'USD',
                'currency_to' => 'EUR',
                'value' => 100.0,
                'converted_value' => 85.0,
                'rate' => 0.85,
            ]);
    }

    /**
     * Test validation error
     */
    public function test_store_fails_when_currency_from_is_missing()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1?method=convert', [
            'currency_to' => 'EUR',
            'value' => 100.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency_from']);
    }

    /**
     * Test validation error if value is not numeric
     */
    public function test_store_fails_when_value_is_not_numeric()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1?method=convert', [
            'currency_from' => 'USD',
            'currency_to' => 'EUR',
            'value' => 'invalid_value',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }

    /**
     * Test validations error if value less then 0.01
     */
    public function test_store_fails_when_value_is_less_than_minimum()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1?method=convert', [
            'currency_from' => 'USD',
            'currency_to' => 'EUR',
            'value' => 0.001,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    }
}
