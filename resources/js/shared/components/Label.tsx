import React from 'react';

type Props = {
    label?: React.ReactNode;
    htmlFor?: string;
    children?: React.ReactNode;
    className?: string;
    style?: React.CSSProperties;
};

export default function Label({ label, htmlFor, children, ...props }: Props) {
    return (
        <label htmlFor={htmlFor} {...props}>
            {children} {label}
        </label>
    );
}
