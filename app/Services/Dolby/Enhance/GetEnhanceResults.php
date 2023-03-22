<?php

namespace App\Services\Dolby\Enhance;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetEnhanceResults extends Request
{

    protected Method $method = Method::GET;

    public function __construct(
        protected string $jobId,
    ) {}

    public function resolveEndpoint(): string
    {
        return 'media/enhance';
    }

    protected function defaultQuery(): array
    {
        return [
            'job_id' => $this->jobId,
        ];
    }


}
