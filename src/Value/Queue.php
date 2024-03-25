<?php

namespace Recoded\LaravelSQS\Value;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

final readonly class Queue
{
    /**
     * Create a new Queue instance.
     *
     * @param string $key
     * @param string|null $prefix
     * @param string $name
     * @param string|null $suffix
     * @param int|null $maxNumberOfMessages
     * @param int|null $waitTimeSeconds
     * @return void
     */
    public function __construct(
        public string $key,
        public ?string $prefix,
        public string $name,
        public ?string $suffix,
        public ?int $maxNumberOfMessages,
        public ?int $waitTimeSeconds,
    ) {
        //
    }

    /**
     * Get queue in URL format.
     *
     * @return string
     */
    public function getQueueUrl(): string
    {
        return filter_var($this->name, FILTER_VALIDATE_URL) === false
            ? $this->suffixQueue($this->name)
            : $this->name;
    }

    /**
     * Add the given suffix to the given queue name.
     *
     * @param string $queue
     * @return string
     */
    private function suffixQueue(string $queue): string
    {
        $suffix = $this->suffix ?? '';

        if (str_ends_with($queue, '.fifo')) {
            $queue = Str::beforeLast($queue, '.fifo');

            return rtrim($this->prefix ?? '', '/') . '/' . Str::finish($queue, $suffix) . '.fifo';
        }

        return rtrim($this->prefix ?? '', '/') . '/' . Str::finish($queue, $suffix);
    }

    /**
     * Create a new queue instance by config key.
     *
     * @param string $key
     * @return self
     */
    public static function fromConfig(string $key): self
    {
        $common = Config::get("sqs.common");
        $config = Config::get("sqs.queues.{$key}");

        Assert::isArray($common);
        Assert::isArray($config);

        $config = [...$common, ...$config];

        return new self(
            key: $key,
            prefix: $config['prefix'] ?? null,
            name: $config['name'] ?? null,
            suffix: $config['suffix'] ?? null,
            maxNumberOfMessages: $config['max_number_of_messages'] ?? null,
            waitTimeSeconds: $config['wait_time_seconds'] ?? null,
        );
    }
}
