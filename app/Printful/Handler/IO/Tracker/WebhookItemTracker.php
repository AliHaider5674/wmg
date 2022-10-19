<?php declare(strict_types=1);

namespace App\Printful\Handler\IO\Tracker;

use App\Printful\Converter\Printful\WebhookItem\WebhookConverterInterface;
use App\Printful\Models\PrintfulEvent;
use Countable;
use IteratorAggregate;
use Generator;
use ArrayIterator;
use Throwable;

/**
 * Class WebhookItemTracker
 * @package App\Printful\Handler\IO\Tracker
 */
abstract class WebhookItemTracker implements IteratorAggregate, Countable
{
    protected $printfulEvents;

    /**
     * WebhookConverterInterface
     */
    protected $converter;

    /**
     * @var PrintfulEvent
     */
    protected $currentPrintfulEvent;

    /**
     * WebhookItemTracker constructor.
     * @param WebhookConverterInterface $converter
     */
    public function __construct(WebhookConverterInterface $converter)
    {
        $this->init();
        $this->converter = $converter;
    }

    /**
     * @param iterable $webhookItems
     * @return $this
     */
    public function setWebhookItems(iterable $webhookItems): self
    {
        $this->currentPrintfulEvent = $webhookItems->current();
        $this->printfulEvents = $webhookItems;
        return $this;
    }

    /**
     * @return $this
     */
    public function reset(): self
    {
        return $this->init();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return iterator_count($this->printfulEvents);
    }

    /**
     * @return Generator
     */
    public function getIterator(): Generator
    {
        foreach ($this->printfulEvents as $printfulEvent) {
            $this->currentPrintfulEvent = $printfulEvent;
            yield $this->converter->convert(
                $printfulEvent->getAttribute('webhook_item')
            );
        }
    }

    /**
     * @param callable $callback
     * @todo make the iterator continue when error
     */
    public function each(callable $callback, callable $failCallback): void
    {
        try {
            foreach ($this->getIterator() as $item) {
                $callback($item);
            }
        } catch (Throwable $e) {
            $failCallback($this->currentPrintfulEvent, $e);
        }
    }


    /**
     * @return PrintfulEvent|null
     */
    public function getCurrentEvent(): ?PrintfulEvent
    {
        return $this->currentPrintfulEvent ?? null;
    }
    /**
     * @return $this
     */
    private function init(): self
    {
        $this->printfulEvents = new ArrayIterator();
        return $this;
    }
}
