<?php

declare(strict_types=1);

/*
 * This file is part of Topia.Money.
 *
 * (c) KodeKeep <hello@kodekeep.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KodeKeep\TopiaMoney\Exchanges;

use Carbon\Carbon;
use KodeKeep\TopiaMoney\Contracts\Exchange;
use KodeKeep\TopiaMoney\DTO\Rate;
use KodeKeep\TopiaMoney\DTO\Symbol;
use KodeKeep\TopiaMoney\Helpers\Client;
use KodeKeep\TopiaMoney\Helpers\ResolveScientificNotation;

/**
 * Undocumented class.
 */
final class CoinCap implements Exchange
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * Undocumented function.
     */
    public function __construct()
    {
        $this->client = Client::new('https://api.coincap.io/v2/');
    }

    /**
     * {@inheritdoc}
     */
    public function symbols(): array
    {
        $offset  = 0;
        $results = [];

        do {
            $response = $this->client->get('markets', [
                'limit'  => 2000,
                'offset' => $offset,
            ]);

            $results = array_merge($results, $response['data']);

            $offset += 2000;
        } while (! empty($response['data']));

        return array_map(fn ($symbol) => new Symbol([
            'symbol' => $symbol['baseSymbol'].'-'.$symbol['quoteSymbol'],
            'source' => $symbol['baseId'],
            'target' => $symbol['quoteId'],
        ]), $results);
    }

    /**
     * {@inheritdoc}
     */
    public function historical(Symbol $symbol): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rate(Symbol $symbol): Rate
    {
        $response = $this->client->get('rates/'.$symbol->source);

        return new Rate([
            'date' => Carbon::now(),
            'rate' => ResolveScientificNotation::execute((float) $response['data']['rateUsd']),
        ]);
    }
}
