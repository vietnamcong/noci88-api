<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


class PaySpecController extends Controller
{
    public function confirm(Request $request) {
        $url    =   getConfig('pay_spec.url');
        $apiKey =   getConfig('pay_spec.key');
        $type   =   $request->type;
        $amount   =   $request->amount;
        $transaction_id   =   '';

        $signature = md5(`${$apiKey}|${$type}|${$amount}|${$transaction_id}`);

        $params = [
            'apikey'        => '',
            'type'          => '',
            'amount'        => '',
            'signature'     => '',
            'transaction'   => ''
        ];

        $response = app(Client::class)->request('POST', $url, ['json' => $params]);
    }

    public function card_confirm() {
        $url        =   getConfig('pay_spec.card_url');
        $apiKey     =   getConfig('pay_spec.key');
        $type       =   $request->type;
        $seri       =   $request->seri;
        $mathe      =   $request->mathe;
        $menhgia    =   $request->menhgia;

        $content    =   uniqid();

        $params = [
            'APIkey'        => $apiKey,
            'type'          => $type,
            'seri'          => $seri,
            'mathe'         => $mathe,
            'menhgia'       => $menhgia,
            'content'       => $content
        ];
        
        $response = app(Client::class)->request('POST', $url, ['json' => $params]);
    }
}