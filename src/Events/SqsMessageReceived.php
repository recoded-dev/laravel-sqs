<?php

namespace Recoded\LaravelSQS\Events;

use Recoded\LaravelSQS\Value\SqsMessage;
use Recoded\LaravelSQS\Value\Queue;

final readonly class SqsMessageReceived
{
    /**
     * Create a new SqsMessageReceived instance.
     *
     * @param \Recoded\LaravelSQS\Value\Queue $queue
     * @param \Recoded\LaravelSQS\Value\SqsMessage $message
     * @return void
     */
    public function __construct(
        public Queue $queue,
        public SqsMessage $message,
    ) {
        //
    }
}
