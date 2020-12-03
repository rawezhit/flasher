<?php

namespace Flasher\Prime\Middleware;

use Flasher\Prime\Envelope;
use Flasher\Prime\Notification\NotificationInterface;

final class NotifyBus
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares;

    /**
     * Executes the given command and optionally returns a value
     *
     * @param Envelope|NotificationInterface $envelope
     * @param array                          $stamps
     *
     * @return mixed
     */
    public function dispatch($envelope, $stamps = array())
    {
        $envelope = Envelope::wrap($envelope, $stamps);

        $middlewareChain = $this->createExecutionChain();

        $middlewareChain($envelope);

        return $envelope;
    }

    /**
     * @param MiddlewareInterface $middleware
     *
     * @return $this
     */
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @return callable
     */
    private function createExecutionChain()
    {
        $lastCallable = static function () {
            // the final callable is a no-op
        };

        $middlewares = $this->middlewares;

        while ($middleware = array_pop($middlewares)) {
            $lastCallable = static function ($command) use ($middleware, $lastCallable) {
                return $middleware->handle($command, $lastCallable);
            };
        }

        return $lastCallable;
    }
}