import React, { useEffect, useState } from 'react';
import ExceptionMessage from 'resources/js/ignition/components/ExceptionMessage';
import sqlFormatter from 'sql-formatter';

type Props = {
    message: string;
    exceptionClass: string;
    className?: string;
};

export default function FormattedExceptionMessage({
    message,
    exceptionClass,
    className = '',
}: Props) {
    const [cleanedUpMessage, setCleanedUpMessage] = useState<string>(message);
    const [isExpanded, setIsExpanded] = useState<boolean>(false);
    const [sqlQuery, setSqlQuery] = useState<string | null>(null);

    useEffect(() => {
        if (
            exceptionClass === 'Illuminate\\Database\\QueryException' ||
            message.match(/SQLSTATE\[.*\].*\(SQL: .*\)/)
        ) {
            const sqlQueryPattern = /\(SQL: (?<query>.*?)\)($| \(View: .*\)$)/;
            const [, query] = message.match(sqlQueryPattern) || [];
            setSqlQuery(query);
            setCleanedUpMessage(message.replace(sqlQueryPattern, '$2'));
        }
    }, [message, exceptionClass]);

    return (
        <>
            {!sqlQuery && <ExceptionMessage message={cleanedUpMessage} className={className} />}

            {sqlQuery && (
                <>
                    <p className={`ui-exception-message ui-exception-message-full ${className}`}>
                        {cleanedUpMessage}
                    </p>

                    <div className="mt-2">
                        <>
                            <code
                                className="code-block mt-2 text-sm cursor-default"
                                onClick={(e) => e.stopPropagation()}
                            >
                                {isExpanded ? (
                                    <pre>{sqlFormatter.format(sqlQuery)}</pre>
                                ) : (
                                    <pre className="truncate pr-12">{sqlQuery}</pre>
                                )}
                            </code>
                            <button
                                className="absolute top-0 right-0 mt-1 mr-2 link-dimmed text-xs"
                                onClick={(e) => {
                                    setIsExpanded(!isExpanded);
                                    e.stopPropagation();
                                }}
                            >
                                {isExpanded ? 'Collapse' : 'Expand'}
                            </button>
                        </>
                    </div>
                </>
            )}
        </>
    );
}
