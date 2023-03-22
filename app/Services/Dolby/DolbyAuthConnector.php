<?php

namespace App\Services\Dolby;

use Saloon\Http\Connector;

class DolbyAuthConnector extends Connector
{

    public function resolveBaseUrl(): string
    {
        return 'https://api.dolby.io/v1';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
    }

}
