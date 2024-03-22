<?php

namespace Tests\Value;

use Recoded\LaravelSQS\Value\Queue;
use Tests\TestCase;

final class QueueTest extends TestCase
{
    /**
     * @dataProvider queueUrlConfigProvider
     */
    public function testItGeneratesCorrectly(string $prefix, string $name, ?string $suffix, string $fullUrl): void
    {
        $queue = new Queue(
            key: 'foo',
            prefix: $prefix,
            name: $name,
            suffix: $suffix,
        );

        self::assertSame($fullUrl, $queue->getQueueUrl());
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
