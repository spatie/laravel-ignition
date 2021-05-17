import React from 'react';

type Props = {
    type?: 'success' | 'info' | 'warning' | 'error' | 'danger';
    className?: string;
    style?: React.CSSProperties;
    children?: React.ReactNode;
    card?: boolean;
};

export default function Alert({
    type = 'info',
    className = '',
    children,
    card = false,
    ...props
}: Props) {
    const typeClassName = {
        info: 'alert-info',
        success: 'alert-success',
        warning: 'alert-warning',
        error: 'alert-error',
        danger: 'alert-danger',
    }[type];

    return (
        <div
            className={`alert ${card ? 'alert-card' : ''} ${typeClassName} ${className}`}
            {...props}
        >
            {card ? <div className="alert-card-content">{children}</div> : children}
        </div>
    );
}
