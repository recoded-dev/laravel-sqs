<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Config;
use Recoded\LaravelSQS\Support\SqsUrlHelper;
use Tests\TestCase;

final class SqsUrlHelperTest extends TestCase
{
    /**
     * @dataProvider queueUrlConfigProvider
     */
    public function testItGeneratesCorrectly(string $prefix, string $name, ?string $suffix, string $fullUrl): void
    {
        Config::set([
            'sqs.queue.prefix' => $prefix,
            'sqs.queue.name' => $name,
            'sqs.queue.suffix' => $suffix,
        ]);

        /** @var \Recoded\LaravelSQS\Support\SqsUrlHelper $helper */
        $helper = $this->app->make(SqsUrlHelper::class);

        self::assertSame($fullUrl, $helper->getQueueUrl());
    }

    /**
     * @return array<int, array{string, string, string|null, string}>
     */
    public static function queueUrlConfigProvider(): array
    {
        return [
            [
                'https://sqs.[region].amazonaws.com/[accountid]',
                'foo-bar',
                null,
                'https://sqs.[region].amazonaws.com/[accountid]/foo-bar',
            ],
            [
                'https://sqs.[region].amazonaws.com/[accountid]',
                'foo-bar',
                'suffix',
                'https://sqs.[region].amazonaws.com/[accountid]/foo-barsuffix',
            ],
            [
                'https://sqs.[region].amazonaws.com/[accountid]',
                'foo-bar.fifo',
                null,
                'https://sqs.[region].amazonaws.com/[accountid]/foo-bar.fifo',
            ],
            [
                'https://sqs.[region].amazonaws.com/[accountid]',
                'foo-bar.fifo',
                '-suffix',
                'https://sqs.[region].amazonaws.com/[accountid]/foo-bar-suffix.fifo',
            ],
        ];
    }
}
