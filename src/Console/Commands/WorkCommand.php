<?php

namespace Recoded\LaravelSQS\Console\Commands;

use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Recoded\LaravelSQS\Events\SqsMessageReceived;
use Recoded\LaravelSQS\Support\SqsUrlHelper;
use Recoded\LaravelSQS\Events\ProcessedSqsMessage;
use Recoded\LaravelSQS\Events\ProcessingSqsMessage;
use Recoded\LaravelSQS\SqsMessage;
use Throwable;

class WorkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sqs:work';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Process the SQS queue';

    /**
     * Prefix of the Queue URL.
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Suffix of the Queue URL.
     *
     * @var string|null
     */
    protected ?string $suffix;

    /**
     * Execute the console command.
     *
     * @param \Aws\Sqs\SqsClient $sqs
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Recoded\LaravelSQS\Support\SqsUrlHelper $helper
     * @return void
     */
    public function handle(SqsClient $sqs, Dispatcher $events, SqsUrlHelper $helper): void
    {
        $queueUrl = $helper->getQueueUrl();

        $this->getOutput()->success('Working');

        while (true) {
            $response = $sqs->receiveMessage([
                'AttributeNames' => ['ApproximateReceiveCount'],
                'MaxNumberOfMessages' => 10,
                'QueueUrl' => $queueUrl,
                'WaitTimeSeconds' => 10,
            ]);

            if (!isset($response['Messages']) || !is_array($response['Messages'])) {
                continue;
            }

            /** @var array{MessageId: string, Attributes: array{ApproximateReceiveCount: string}, ReceiptHandle: string} $message */
            foreach ($response['Messages'] as $message) {
                $message = SqsMessage::fromArray($message);

                try {
                    $events->dispatch(new ProcessingSqsMessage($message));

                    $propagated = $events->until(new SqsMessageReceived($message));

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
                    $events->dispatch(new ProcessedSqsMessage($message));
                }
            }
        }
    }
}
