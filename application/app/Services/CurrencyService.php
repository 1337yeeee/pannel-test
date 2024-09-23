<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class CurrencyService {

    const COMMISSION_RATE = 1.02;

    public function __construct()
    {

    }

    public function fetch(): array
    {
        $response = Http::get('https://api.coincap.io/v2/rates');

        if ($response->successful()) {
            $rates = $response->json();
            if(!is_array($rates)) {
                throw new InvalidArgumentException('The feched data was expected to be an array');
            }

            return $rates['data'];
        } else {
            throw new \Exception('Couldn\'t fetch currency info');
        }
    }

    /**
     * Fetches and proccesses rates applying service commission
     *
     * @return array{string:float}
     * 
     */
    public function rates(): array
    {
        $fetchedRates = $this->fetch();

        $rates = [];
        foreach($fetchedRates as $fetchedRate) {
            $symbol = (string) $fetchedRate['symbol'];
            $rate = floatval($fetchedRate['rateUsd']) * self::COMMISSION_RATE;
            $rates[$symbol] = $rate;
        }

        return $rates;
    }

    /**
     * [Description for convert]
     *
     * @param string $currFrom
     * @param string $currTo
     * 
     * @return float
     * 
     */
    public function convert(string $currFrom, string $currTo): float
    {
        $rates = $this->rates();

        if(!isset($rates[$currFrom])) throw new InvalidArgumentException('Currency from is not found');
        if(!isset($rates[$currTo])) throw new InvalidArgumentException('Currency to is not found');

        $rateFrom = $rates[$currFrom];
        $rateTo = $rates[$currTo];

        $rate = self::COMMISSION_RATE * $rateTo / $rateFrom;

        return $rate;
    }

    public function roundConvertedValue(float $value, string $currencyFrom, string $currencyTo): float
    {
        if ($currencyFrom == 'BTC' && $currencyTo == 'USD') {
            return round($value, 2);
        }

        return round($value, 10);

    }

}
