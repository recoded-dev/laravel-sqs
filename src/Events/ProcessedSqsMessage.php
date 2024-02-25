<?php

namespace Recoded\LaravelSQS\Events;

use Recoded\LaravelSQS\SqsMessage;

final readonly class ProcessedSqsMessage
{
    /**
     * Create a new ProcessedSqsMessage instance.
     *
     * @param \Recoded\LaravelSQS\SqsMessage $message
     * @return void
     */
    public function __construct(public SqsMessage $message)
    {
        //
    }
}
