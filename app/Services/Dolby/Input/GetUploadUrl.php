<?php

namespace App\Services\Dolby\Input;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class GetUploadUrl extends Request implements HasBody
{

    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $url,
    ) {}

    public function resolveEndpoint(): string
    {
        return 'media/input';
    }

    protected function defaultBody(): array
    {
        return [
            'url' => $this->url,
        ];
    }


}
