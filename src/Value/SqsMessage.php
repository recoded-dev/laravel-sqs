<?php

namespace Recoded\LaravelSQS\Value;

final readonly class SqsMessage
{
    /**
     * Create a new SqsMessage instance.
     *
     * @param string $id
     * @param array<string, mixed> $payload
     * @param positive-int $attempts
     * @param string $receiptHandle
     * @return void
     */
    public function __construct(
        public string $id,
        public array $payload,
        public int $attempts,
        public string $receiptHandle,
    ) {
        //
    }

    /**
     * @param array{MessageId: string, Attributes: array{ApproximateReceiveCount: string}, ReceiptHandle: string} $message
     * @return self
     */
    public static function fromArray(array $message): self
    {
        return new self(
            id: $message['MessageId'],
            payload: $message,
            attempts: max((int) $message['Attributes']['ApproximateReceiveCount'], 1),
            receiptHandle: $message['ReceiptHandle'],
        );
    }
}
