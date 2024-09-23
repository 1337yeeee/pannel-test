<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;

class RatesController extends Controller
{

    protected CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function rates(?string $currency=null)
    {
        $rates = $this->currencyService->rates();

        $data = [
            'status' => 'success',
	        'code' => 200,
        ];

        if($currency and isset($rates[$currency]))
        {
            $rates = [$currency => $rates[$currency]];
            $data['currency'] = $currency;
        }

        $data['data'] = $rates;

        return response()->json($data);
    }
}
