<?php

namespace Recoded\LaravelSQS\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

class SqsUrlHelper
{
    /**
     * Prefix of the queue URL.
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Name of the queue.
     *
     * @var string
     */
    protected string $name;

    /**
     * Suffix of the queue URL.
     *
     * @var string|null
     */
    protected ?string $suffix;

    /**
     * Create a new SqsUrlHelper instance.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @return void
     */
    public function __construct(Repository $config)
    {
        $prefix = $config->get('sqs.queue.prefix');
        $name = $config->get('sqs.queue.name');
        $suffix = $config->get('sqs.queue.suffix');

        Assert::string($prefix);
        Assert::string($name);
        Assert::nullOrString($suffix);

        $this->prefix = $prefix;
        $this->name = $name;
        $this->suffix = $suffix;
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

            return rtrim($this->prefix, '/') . '/' . Str::finish($queue, $suffix) . '.fifo';
        }

        return rtrim($this->prefix, '/') . '/' . Str::finish($queue, $suffix);
    }
}
