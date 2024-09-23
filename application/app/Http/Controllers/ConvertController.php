<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CurrencyService;

class ConvertController extends Controller
{

    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function convert(Request $request)
    {
        $validatedRequest = $request->validate([
            'currency_from' => 'required',
            'currency_to' => 'required',
            'value' => 'required|numeric|min:0.01',
        ]);
        
        $currencyFrom = $validatedRequest['currency_from'];
        $currencyTo = $validatedRequest['currency_to'];
        $value = floatval($validatedRequest['value']);

        $convertedRate = $this->currencyService->convert($currencyFrom,$currencyTo);
        $convertedValue = $value * $convertedRate;
        $convertedValue = $this->currencyService->roundConvertedValue($convertedValue, $currencyFrom, $currencyTo);

        return [
            'currency_from' => $currencyFrom,
            'currency_to' => $currencyTo,
            'value' => $value,
            'converted_value' => $convertedValue,
            'rate' => $convertedRate,
        ];
    }

}
