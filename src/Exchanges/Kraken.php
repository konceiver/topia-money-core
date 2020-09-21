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
final class Kraken implements Exchange
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
        $this->client = Client::new('https://api.kraken.com/0/public/');
    }

    /**
     * {@inheritdoc}
     */
    public function symbols(): array
    {
        return array_values(array_map(fn ($symbol) => new Symbol([
            'symbol' => $symbol['altname'],
            'source' => $symbol['base'],
            'target' => $symbol['quote'],
        ]), $this->client->get('AssetPairs')['result']));
    }

    /**
     * {@inheritdoc}
     */
    public function historical(Symbol $symbol): array
    {
        return array_map(fn ($day) => new Rate([
            'date' => Carbon::createFromTimestamp($day[0]),
            'rate' => $day[4],
        ]), head($this->client->get('OHLC', [
            'pair'     => $symbol->symbol,
            'interval' => 1440,
            'since'    => 0,
        ])['result']));
    }

    /**
     * {@inheritdoc}
     */
    public function rate(Symbol $symbol): Rate
    {
        $response = head($this->client->get('Ticker', ['pair' => $symbol->symbol])['result']);

        return new Rate([
            'date' => Carbon::now(),
            'rate' => ResolveScientificNotation::execute((float) $response['c'][0]),
        ]);
    }
}