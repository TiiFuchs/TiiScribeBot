<?php

namespace App\Services\Dolby\Output;

use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

class GetDownloadUrl extends \Saloon\Http\Request implements \Saloon\Contracts\Body\HasBody
{

    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $url,
    ) {}

    public function resolveEndpoint(): string
    {
        return 'media/output';
    }

    protected function defaultBody(): array
    {
        return [
            'url' => $this->url,
        ];
    }


}
