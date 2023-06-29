<?php declare(strict_types=1);

namespace App\Factory;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsDecorator(HttpClientInterface::class)]
class RetryableHttpClientDecorator implements HttpClientInterface
{
    use DecoratorTrait;

    public function __construct(
        #[AutowireDecorated]
        HttpClientInterface $client,
        private LoggerInterface $logger
    ) {
        $this->client = new RetryableHttpClient($client, logger: $logger);
    }
}
