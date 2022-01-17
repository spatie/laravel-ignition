<?php

namespace Spatie\LaravelIgnition\Views;

use ErrorException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

class BladeSourceMapCompiler extends BladeCompiler
{
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct($filesystem, 'not-needed-for-source-map');
    }

    public function detectLineNumber(string $filename, int $compiledLineNumber): int
    {
        try {
            $map = $this->compileString((string)file_get_contents($filename));
        } catch (ErrorException $e) {
            return 1;
        }

        return $this->findClosestLineNumberMapping($map, $compiledLineNumber);
    }

    public function compileString($value)
    {
        try {
            $value = $this->addEchoLineNumbers($value);

            $value = $this->addStatementLineNumbers($value);

            $value = $this->addBladeComponentLineNumbers($value);

            $value = parent::compileString($value);

            return $this->trimEmptyLines($value);
        } catch (\Exception $e) {
            report($e);

            return $value;
        }
    }

    protected function addEchoLineNumbers(string $value): string
    {
        $echoPairs = [$this->contentTags, $this->rawTags, $this->escapedTags];

        foreach ($echoPairs as $pair) {
            // Matches {{ $value }}, {!! $value !!} and  {{{ $value }}} depending on $pair
            $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $pair[0], $pair[1]);

            if (preg_match_all($pattern, $value, $matches, PREG_OFFSET_CAPTURE)) {
                foreach (array_reverse($matches[0]) as $match) {
                    $position = mb_strlen(substr($value, 0, $match[1]));

                    $value = $this->insertLineNumberAtPosition($position, $value);
                }
            }
        }

        return $value;
    }

    protected function addStatementLineNumbers(string $value): string
    {
        // Matches @bladeStatements() like @if, @component(...), @etc;
        $shouldInsertLineNumbers = preg_match_all(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
            $value,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if ($shouldInsertLineNumbers) {
            foreach (array_reverse($matches[0]) as $match) {
                $position = mb_strlen(substr($value, 0, $match[1]));

                $value = $this->insertLineNumberAtPosition($position, $value);
            }
        }

        return $value;
    }

    protected function addBladeComponentLineNumbers(string $value): string
    {
        // Matches the start of `<x-blade-component`
        $shouldInsertLineNumbers = preg_match_all(
            '/<\s*x[-:]([\w\-:.]*)/mx',
            $value,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if ($shouldInsertLineNumbers) {
            foreach (array_reverse($matches[0]) as $match) {
                $position = mb_strlen(substr($value, 0, $match[1]));

                $value = $this->insertLineNumberAtPosition($position, $value);
            }
        }

        return $value;
    }

    protected function insertLineNumberAtPosition(int $position, string $value): string
    {
        $before = mb_substr($value, 0, $position);
        $lineNumber = count(explode("\n", $before));

        return mb_substr($value, 0, $position)."|---LINE:{$lineNumber}---|".mb_substr($value, $position);
    }

    protected function trimEmptyLines(string $value): string
    {
        $value = preg_replace('/^\|---LINE:([0-9]+)---\|$/m', '', $value);

        return ltrim((string)$value, PHP_EOL);
    }

    protected function findClosestLineNumberMapping(string $map, int $compiledLineNumber): int
    {
        $map = explode("\n", $map);

        // Max 10 lines between compiled and source line number.
        // Blade components can span multiple lines and the compiled line number is often
        // a couple lines below the source-mapped `<x-component>` code.
        $maxDistance = 10;

        $pattern = '/\|---LINE:(?P<line>[0-9]+)---\|/m';
        $lineNumberToCheck = $compiledLineNumber - 1;

        while (true) {
            if ($lineNumberToCheck < $compiledLineNumber - $maxDistance) {
                // Something wrong. Return the $compiledLineNumber (unless it's out of range)
                return $compiledLineNumber > count($map) ? count($map) : $compiledLineNumber;
            }

            if (preg_match($pattern, (string) ($map[$lineNumberToCheck] ?? ''), $matches)) {
                return (int)$matches['line'];
            }

            $lineNumberToCheck--;
        }
    }
}
