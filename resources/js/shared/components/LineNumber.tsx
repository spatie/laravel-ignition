import React from 'react';

type Props = {
    value: number;
    className?: string;
    style?: React.CSSProperties;
};

export default function LineNumber({ value, className = '', ...props }: Props) {
    return (
        <span className={`ui-line-number ${className}`} {...props}>
            :<span className="font-mono">{value}</span>
        </span>
    );
}
