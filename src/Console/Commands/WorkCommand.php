<?php

namespace Recoded\LaravelSQS\Console\Commands;

use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Recoded\LaravelSQS\Events\SqsMessageReceived;
use Recoded\LaravelSQS\Events\ProcessedSqsMessage;
use Recoded\LaravelSQS\Events\ProcessingSqsMessage;
use Recoded\LaravelSQS\Value\SqsMessage;
use Recoded\LaravelSQS\Value\Queue;
use Throwable;
use Webmozart\Assert\Assert;

class WorkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sqs:work {queues?}';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Process the SQS queue';

    /**
     * Execute the console command.
     *
     * @param \Aws\Sqs\SqsClient $sqs
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function handle(SqsClient $sqs, Dispatcher $events): void
    {
        $queueNames = $this->argument('queues');

        if (is_string($queueNames)) {
            $queueNames = explode(',', $queueNames);
        }

        $queueNames ??= config('sqs.default_queues');

        Assert::isArray($queueNames);

        $queues = array_map(Queue::fromConfig(...), $queueNames);

        $this->getOutput()->success('Working');

        while (true) {
            $this->work($sqs, $events, $queues);
        }
    }

    /**
     * Work all the queues.
     *
     * @param \Aws\Sqs\SqsClient $sqs
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Recoded\LaravelSQS\Value\Queue[] $queues
     * @return void
     */
    private function work(SqsClient $sqs, Dispatcher $events, array $queues): void
    {
        foreach ($queues as $queue) {
            $response = $sqs->receiveMessage([
                'AttributeNames' => ['ApproximateReceiveCount'],
                'MaxNumberOfMessages' => 10,
                'QueueUrl' => $queueUrl = $queue->getQueueUrl(),
                'WaitTimeSeconds' => 10,
            ]);

            if (!isset($response['Messages']) || !is_array($response['Messages'])) {
                continue;
            }

            /** @var array{MessageId: string, Attributes: array{ApproximateReceiveCount: string}, ReceiptHandle: string} $message */
            foreach ($response['Messages'] as $message) {
                $message = SqsMessage::fromArray($message);

                try {
                    $events->dispatch(new ProcessingSqsMessage($queue, $message));

                    $propagated = $events->until(new SqsMessageReceived($queue, $message));

                    if ($propagated !== false) {
                        $sqs->deleteMessage([
                            'QueueUrl' => $queueUrl,
                            'ReceiptHandle' => $message->receiptHandle,
                        ]);
                    }
                } catch (Throwable $throwable) {
                    report($throwable);

                    $this->components->error($throwable->getMessage());

                    if ($message->attempts >= 5) {
                        $sqs->deleteMessage([
                            'QueueUrl' => $queueUrl,
                            'ReceiptHandle' => $message->receiptHandle,
                        ]);
                    }
                } finally {
                    $events->dispatch(new ProcessedSqsMessage($queue, $message));
                }
            }
        }
    }
}
