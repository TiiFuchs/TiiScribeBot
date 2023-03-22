<?php

namespace App\Services\Dolby\Enhance;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

class StartEnhancing extends \Saloon\Http\Request implements HasBody
{

    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $input,
        protected string $output,
        protected ?string $content,
        protected ?array $audio,
    ) {}

    public function resolveEndpoint(): string
    {
        return 'media/enhance';
    }

    protected function defaultBody(): array
    {
        $optionals = [];

        if ($this->content) {
            $optionals['content'] = [
                'type' => $this->content,
            ];
        }

        if ($this->audio) {
            $optionals['audio'] = $this->audio;
        }

        return [
            'input'  => $this->input,
            'output' => $this->output,
            ...$optionals,
        ];
    }


}
