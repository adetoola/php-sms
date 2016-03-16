<?php

namespace Adetoola\SMS;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Log\Writer;
use Illuminate\Queue\QueueManager;

class SMS extends SMSContext
{
	/**
     * The log writer instance.
     *
     * @var \Illuminate\Log\Writer
     */
    protected $logger;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The queue implementation.
     *
     * @var \Illuminate\Contracts\Queue\Queue
     */
    protected $queue;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * Queue a new SMS message for sending.
     *
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @param  string|null  $queue
     * @return mixed
     */
    public function queue($recepient, $message, $queue = null)
    {
        $callback = [
        'recepient' => $recepient,
        'message' => $message,
        'sender' => isset($sender) ? $sender : null,
        'message_type' => isset($message_type) ? $message_type : 0
        ];
        
        return $this->queue->push('sms@handleQueuedMessage', $callback, $queue);
    }

    /**
     * Queue a new e-mail message for sending on the given queue.
     *
     * This method didn't match rest of framework's "onQueue" phrasing. Added "onQueue".
     *
     * @param  string  $queue
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @return mixed
     */
    public function queueOn($queue, $callback)
    {
        return $this->queue($callback, $queue);
    }

    /**
     * Queue a new e-mail message for sending after (n) seconds.
     *
     * @param  int  $delay
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @param  string|null  $queue
     * @return mixed
     */
    public function later($delay, $recepient, $message, $queue = null)
    {
        $callback = [
        'recepient' => $recepient,
        'message' => $message,
        'sender' => isset($sender) ? $sender : null,
        'message_type' => isset($message_type) ? $message_type : 0
        ];
        
        return $this->queue->later($delay, 'sms@handleQueuedMessage', $callback, $queue);
    }

    /**
     * Queue a new e-mail message for sending after (n) seconds on the given queue.
     *
     * @param  string  $queue
     * @param  int  $delay
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @return mixed
     */
    public function laterOn($queue, $delay, $callback)
    {
        return $this->later($delay, $callback, $queue);
    }

    /**
     * Build the callable for a queued e-mail job.
     *
     * @param  mixed  $callback
     * @return mixed
     */
    protected function buildQueueCallable($callback)
    {
        if (! $callback instanceof Closure) {
            return $callback;
        }

        return (new Serializer)->serialize($callback);
    }

    /**
     * Handle a queued e-mail message job.
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  array  $data
     * @return void
     */
    public function handleQueuedMessage($job, $data)
    {
        $this->send($data['recepient'], $data['message'], $data['sender'], $data['message_type']
            );

        $job->delete();
    }

    /**
     * Get the true callable for a queued e-mail message.
     *
     * @param  array  $data
     * @return mixed
     */
    protected function getQueuedCallable(array $data)
    {
        if (Str::contains($data['callback'], 'SerializableClosure')) {
            return unserialize($data['callback'])->getClosure();
        }

        return $data['callback'];
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Set the log writer instance.
     *
     * @param  \Illuminate\Log\Writer $logger
     * @return $this
     */
    public function setLogger(Writer $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set the queue manager instance.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $queue
     * @return $this
     */
    public function setQueue(QueueManager $queue)
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * @param Dispatcher $events
     *
     * @return $this
     */
    public function setEventDispatcher( Dispatcher $events = null)
    {
        $this->events = $events;

        return $this;
    }
}
