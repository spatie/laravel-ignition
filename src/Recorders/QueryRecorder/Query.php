<?php

namespace Spatie\LaravelIgnition\Recorders\QueryRecorder;

use Illuminate\Database\Events\QueryExecuted;

class Query
{
    protected string $sql;

    protected float $time;

    protected string $connectionName;

    protected ?array $bindings;

    protected float $microtime;

    public static function fromQueryExecutedEvent(QueryExecuted $queryExecuted, bool $reportBindings = false): self
    {
        return new static(
            $queryExecuted->sql,
            $queryExecuted->time,
            $queryExecuted->connectionName ?? '',
            $reportBindings ? $queryExecuted->bindings : null
        );
    }

    protected function __construct(
        string $sql,
        float $time,
        string $connectionName,
        ?array $bindings = null,
        ?float $microtime = null
    ) {
        $this->sql = $sql;
        $this->time = $time;
        $this->connectionName = $connectionName;
        $this->bindings = $bindings;
        $this->microtime = $microtime ?? microtime(true);
    }

    public function toArray(): array
    {
        return [
            'sql' => $this->sql,
            'time' => $this->time,
            'connection_name' => $this->connectionName,
            'bindings' => $this->bindings,
            'microtime' => $this->microtime,
        ];
    }
}
