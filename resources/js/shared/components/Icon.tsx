import React from 'react';

type Props = {
    name: string;
    className?: React.SVGAttributes<SVGSVGElement>['className'];
};

export default function Icon({ name, className = '' }: Props) {
    return (
        <svg className={`icon ${className}`}>
            <use xlinkHref={`#${name}-icon'`} />
        </svg>
    );
}
