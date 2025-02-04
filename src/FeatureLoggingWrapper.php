<?php


namespace FeatureLogging;

use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Stringable;

class FeatureLoggingWrapper implements LoggerInterface
{
    private Collection $eventKeys;
    private Collection $lockedKeys;

    public function __construct(protected LoggerInterface $logDriver, protected LoggerInterface $nullLogger)
    {
        $this->eventKeys = collect();
        $this->loggedKeys = collect();
    }

    private function guessKey():string
    {
        return 'empty';
    }

    private function unlessKeyIsLocked(string $key): LoggerInterface
    {
        return ($this->lockedKeys->get($key, false)) ? $this->nullLogger : $this;
    }


    public function disableLogKey(string $key): void
    {
        $this->lockedKeys[$key] = true;
    }

    public function enableLogKey(string $key): void
    {
        $this->lockedKeys[$key] = false;
    }

    public function once(?String $key = null): LoggerInterface
    {
        $key ??= $this->guessKey();
        return $this->n(1, $key);
    }

    public function n(int $allowedTimes, ?String $key = null): LoggerInterface
    {
        $key ??= $this->guessKey();
        $count = $this->eventKeys->get($key, 0);

        if ($count >= $allowedTimes) {
            return $this->nullLogger;
        }

        $this->eventKeys->put($key, $count + 1);
        return $this->unlessKeyIsLocked($key);
    }

    public function skip(int $skipTimes, ?String $key = null, ?int $allowedTimes = null): LoggerInterface
    {
        $key ??= $this->guessKey();
        $count = $this->eventKeys->get($key, 0);

        if ($count < $skipTimes) {
            $this->eventKeys->put($key, $count + 1);
            return $this->nullLogger;
        }

        return ($allowedTimes) ? $this->n($skipTimes + $allowedTimes, $key) : $this->unlessKeyIsLocked($key);
    }

    /**
    * System is unusable.
    *
    * @param string|Stringable $message
    * @param array $context
    *
    * @return void
    */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->logDriver->emergency($message, $context);
    }


    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string|Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->logDriver->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string|Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->logDriver->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string|Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logDriver->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string|Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logDriver->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string|Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->logDriver->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string|Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logDriver->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string|Stringable $message
     * @param array $context
     *
     * @return void
     */
    public function debug(string|Stringable $message, array $context = []): void {
        $this->logDriver->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string|Stringable $message
     * @param array $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, string|Stringable $message, array $context = []): void {
        $this->logDriver->log($level, $message, $context);
    }

    /**
     * Dynamically pass calls to the log driver.
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->logDriver->$method(...$args);
    }
}
