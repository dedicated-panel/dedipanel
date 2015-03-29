<?php

namespace DP\Core\CoreBundle\Monolog;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

class DebugOnlyHandler implements HandlerInterface
{
    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param HandlerInterface $handler
     * @param bool             $debug
     */
    public function __construct(HandlerInterface $handler, $debug)
    {
        $this->handler = $handler;
        $this->debug   = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        if (false === $this->debug) {
            return false;
        }

        return $this->handler->isHandling($record);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (false === $this->debug) {
            return false;
        }

        return $this->handler->handle($record);
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        if (false === $this->debug) {
            return;
        }

        $this->handler->handleBatch($records);
    }

    /**
     * {@inheritdoc}
     */
    public function pushProcessor($callback)
    {
        $this->handler->pushProcessor($callback);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function popProcessor()
    {
        return $this->handler->popProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->handler->setFormatter($formatter);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        return $this->handler->getFormatter();
    }
}
