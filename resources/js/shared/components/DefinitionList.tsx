import React from 'react';
import CopyableCode from './CopyableCode';

type Props = {
    title?: string;
    className?: string;
    style?: React.CSSProperties;
    children?: Array<React.ReactNode>;
};

export default function DefinitionList({ children, title = '', className = '', ...props }: Props) {
    return (
        <>
            {children && (
                <div className={`${className}`} {...props}>
                    {title && <h3 className="definition-list-title">{title}</h3>}
                    {!!children.length ? (
                        <dl className={`definition-list`}>{children}</dl>
                    ) : (
                        <div className={`definition-list`}>
                            <div className="definition-list-empty">—</div>
                        </div>
                    )}
                </div>
            )}
        </>
    );
}

DefinitionList.Row = DefinitionListRow;

type DefinitionListRowProps = {
    value?: string | React.ReactNode | Array<any> | Object;
    label?: string | React.ReactNode;
};

function DefinitionListRow({ value = '', label = '' }: DefinitionListRowProps) {
    let valueOutput: React.ReactNode = value;

    if (React.isValidElement(value)) {
        valueOutput = value;
    } else if (typeof value === 'object') {
        valueOutput = <CopyableCode>{JSON.stringify(value, null, 4)}</CopyableCode>;
    }

    return (
        <>
            <dt className="definition-label">{label}</dt>
            <dd className="definition-value">{valueOutput}</dd>
        </>
    );
}
