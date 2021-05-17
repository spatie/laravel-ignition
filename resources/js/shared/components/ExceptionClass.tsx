import React from 'react';

type Props = {
    name?: string;
    method?: string;
    className?: string;
    style?: React.CSSProperties;
};

export default function ExceptionClass({ name = '', method, className = '', ...props }: Props) {
    const segments = name.split('\\');
    const segmentsClass = segments.pop();

    return (
        <span className={`ui-exception-class ${className}`} {...props}>
            {segments.map((segment, i) => (
                <span key={i}>
                    {segment}\<wbr />
                </span>
            ))}
            {segmentsClass}
            <wbr />
            {method && (
                <span className="ui-exception-method">
                    {name && '::'}
                    {method}
                </span>
            )}
        </span>
    );
}
