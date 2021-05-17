import React, { useMemo, useState } from 'react';
import { highlight } from 'resources/js/shared/util';
import { ICompiledMode } from 'highlight.js';

type Props = {
    code: Record<string | number, string>;
    highlightedLineNumber: number;
    selectedRange: [number, number] | null;
    onSelectRange: (range: [number, number]) => void;
};

export default function StackSnippet({
    code,
    highlightedLineNumber,
    selectedRange,
    onSelectRange,
}: Props) {
    const [firstSelectedLineNumber, setFirstSelectedLineNumber] = useState<number | null>(
        selectedRange ? selectedRange[0] : null,
    );

    function withinSelectedRange(lineNumber: number) {
        if (!selectedRange) {
            return false;
        }

        return lineNumber >= selectedRange[0] && lineNumber <= selectedRange[1];
    }

    const highlightedCodeSnippet = useMemo(() => {
        let state: ICompiledMode;

        return Object.keys(code).map((lineNumber) => {
            const result = highlight('php', code[lineNumber] || '', true, state);

            state = result.top;

            return (
                <p
                    key={lineNumber}
                    className={`stack-code-line ${
                        withinSelectedRange(parseInt(lineNumber)) ? 'stack-code-line-selected' : ''
                    } ${
                        parseInt(lineNumber) === highlightedLineNumber
                            ? 'stack-code-line-highlight'
                            : ''
                    }`}
                    dangerouslySetInnerHTML={{ __html: result.value || ' ' }}
                />
            );
        });
    }, [code, selectedRange]);

    function handleLineNumberClick(e: React.MouseEvent, lineNumber: number) {
        if (e.shiftKey && firstSelectedLineNumber !== null) {
            onSelectRange(
                [firstSelectedLineNumber, lineNumber].sort((a, b) => a - b) as [number, number],
            );
        } else {
            setFirstSelectedLineNumber(lineNumber);
            onSelectRange([lineNumber, lineNumber]);
        }
    }

    return (
        <div className="stack-viewer scrollbar">
            <div className="stack-ruler">
                <div className="stack-lines">
                    {Object.keys(code).length &&
                        Object.keys(code).map((lineNumber) => {
                            return (
                                <p
                                    onClick={(event) =>
                                        handleLineNumberClick(event, parseInt(lineNumber))
                                    }
                                    key={lineNumber}
                                    className={`cursor-pointer stack-line ${
                                        withinSelectedRange(parseInt(lineNumber))
                                            ? 'stack-line-selected'
                                            : ''
                                    } ${
                                        parseInt(lineNumber) === highlightedLineNumber
                                            ? 'stack-line-highlight'
                                            : ''
                                    }`}
                                >
                                    {lineNumber}
                                </p>
                            );
                        })}
                </div>
            </div>
            <pre className="stack-code">{highlightedCodeSnippet}</pre>
        </div>
    );
}
