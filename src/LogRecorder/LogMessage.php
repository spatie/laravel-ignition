<?php

namespace Spatie\LaravelIgnition\LogRecorder;

use Illuminate\Log\Events\MessageLogged;

class LogMessage
{
    protected ?string $message;

    protected string $level;

    protected array $context = [];

    protected ?float $microtime;

    public function __construct(?string $message, string $level, array $context = [], ?float $microtime = null)
    {
        $this->message = $message;
        $this->level = $level;
        $this->context = $context;
        $this->microtime = $microtime ?? microtime(true);
    }

    public static function fromMessageLoggedEvent(MessageLogged $event): self
    {
        return new self(
            $event->message,
            $event->level,
            $event->context
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'level' => $this->level,
            'context' => $this->context,
            'microtime' => $this->microtime,
        ];
    }
}
