<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Mockery;

class CurrencyServiceTest extends TestCase
{
    /**
     * Test success getting data via method fetch
     */
    public function test_fetch_success()
    {
        Http::fake([
            'https://api.coincap.io/v2/rates' => Http::response([
                'data' => [
                    ["id"=>"united-states-dollar","symbol"=>"USD","currencySymbol"=>"$","type"=>"fiat","rateUsd"=>"1.0000000000000000"],
                    ["id"=>"euro","symbol"=>"EUR","currencySymbol"=>"€","type"=>"fiat","rateUsd"=>"1.1100146632937020"],
                ]
            ], 200),
        ]);

        $service = new CurrencyService();
        $rates = $service->fetch();

        $this->assertIsArray($rates);
        $this->assertCount(2, $rates);
        $this->assertEquals('USD', $rates[0]['symbol']);
        $this->assertEquals('EUR', $rates[1]['symbol']);
    }

    /**
     * Test an error when getting data
     */
    public function test_fetch_fails_with_error()
    {
        Http::fake([
            'https://api.coincap.io/v2/rates' => Http::response([], 500),
        ]);

        $service = new CurrencyService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Couldn\'t fetch currency info');

        $service->fetch();
    }

    /**
     * Test successful getting rates and applying the commission
     */
    public function test_rates_with_commission()
    {
        Http::fake([
            'https://api.coincap.io/v2/rates' => Http::response([
                'data' => [
                    ["id"=>"united-states-dollar","symbol"=>"USD","currencySymbol"=>"$","type"=>"fiat","rateUsd"=>"1.0000000000000000"],
                    ["id"=>"british-pound-sterling","symbol"=>"GBP","currencySymbol"=>"£","type"=>"fiat","rateUsd"=>"1.326"],
                    ["id"=>"euro","symbol"=>"EUR","currencySymbol"=>"€","type"=>"fiat","rateUsd"=>"1.11"],
                ]
            ], 200),
        ]);

        $service = new CurrencyService();
        $rates = $service->rates();

        $this->assertEquals(1.02, $rates['USD']);
        $this->assertEquals(1.1322, $rates['EUR']); // 1.11 * 1.02 = 1.1322
    }

    /**
     * Test a error od there is no currency symbol exists
     */
    public function test_convert_throws_exception_when_currency_not_found()
    {
        $service = Mockery::mock(CurrencyService::class)->makePartial();
        $service->shouldReceive('rates')->andReturn([
            'USD' => 1.02,
            'EUR' => 0.867
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency from is not found');

        $service->convert('GBP', 'EUR');
    }

    /**
     * Test succsessful convert
     */
    public function test_convert_success()
    {
        $service = Mockery::mock(CurrencyService::class)->makePartial();
        $service->shouldReceive('rates')->andReturn([
            'USD' => 1.02,
            'EUR' => 0.867
        ]);

        $rate = $service->convert('USD', 'EUR');

        $this->assertEquals(0.867, $rate);
    }

    /**
     * Test rounded converted value
     */
    public function test_round_converted_value()
    {
        $service = new CurrencyService();

        // Округление для BTC -> USD
        $this->assertEquals(12345.68, $service->roundConvertedValue(12345.6789, 'BTC', 'USD'));

        // Округление для других валют
        $this->assertEquals(12345.6789, $service->roundConvertedValue(12345.6789, 'USD', 'EUR'));
    }
}
