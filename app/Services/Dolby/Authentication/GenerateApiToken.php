<?php

namespace App\Services\Dolby\Authentication;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;

class GenerateApiToken extends Request implements HasBody
{

    use HasFormBody;

    protected Method $method = Method::POST;

    public function __construct(
        string $appKey,
        string $appSecret,
    ) {
        $this->withBasicAuth($appKey, $appSecret);
    }

    public function resolveEndpoint(): string
    {
        return 'auth/token';
    }

    protected function defaultBody(): array
    {
        return [
            'grant_type' => 'client_credentials',
            'expires_in' => 1800,
        ];
    }


}
