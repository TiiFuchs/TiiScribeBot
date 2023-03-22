<?php

namespace App\Services\Dolby;

use Saloon\Http\Connector;

class DolbyConnector extends Connector
{

    public function __construct(
        protected string $apiToken,
    ) {
        $this->withTokenAuth($this->apiToken);
    }

    public function resolveBaseUrl(): string
    {
        return 'https://api.dolby.com';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
    }


}
