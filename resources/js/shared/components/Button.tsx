import React from 'react';

type Props = {
    size?: 'base' | 'lg' | 'sm';
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    className?: string;
    style?: React.CSSProperties;
    children?: React.ReactNode;
    secondary?: boolean;
    link?: boolean;
    onClick?: (event: React.MouseEvent) => void;
};

export default function Button({
    secondary = false,
    link = false,
    children,
    type,
    size = 'base',
    className = '',
    ...props
}: Props) {
    let style = link
        ? secondary
            ? 'link-dimmed'
            : 'link'
        : secondary
        ? 'button-secondary'
        : 'button';

    return (
        <button
            type={type}
            className={`${style} ${size ? `button-${size}` : ''} ${className}`}
            {...props}
        >
            {children}
        </button>
    );
}
