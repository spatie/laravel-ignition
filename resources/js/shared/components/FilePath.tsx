import React from 'react';

type Props = {
    path: string;
    className?: string;
    style?: React.CSSProperties;
};

export default function FilePath({ path, className = '', ...props }: Props) {
    const segments = path.split('/');
    const file = segments.pop() || '';
    const fileSegments = file.split('.');

    return (
        <span className={`ui-path ${className}`} {...props}>
            {segments.map((segment, i) => (
                <span key={i}>
                    {segment}/<wbr />
                </span>
            ))}
            {fileSegments.map((fileSegment, i) => (
                <span key={i} className={i === 0 ? 'font-semibold' : ''}>
                    {i > 0 && '.'}
                    {fileSegment}
                </span>
            ))}
        </span>
    );
}
