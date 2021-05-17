import React from 'react';
import Label from './Label';

type Props = {
    name?: string;
    id?: string;
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'full';
    disabled?: boolean;
    placeholder?: string;
    label?: React.ReactNode;
    value?: string | number;
    checked?: boolean;
    defaultChecked?: boolean;
    labelClassName?: string;
    className?: string;
    onChange?: (event: React.ChangeEvent<HTMLInputElement>) => void;
};

export default function CheckboxField({
    name,
    id,
    label,
    value,
    defaultChecked,
    onChange,
    checked,
    disabled = false,
    labelClassName = '',
    className = '',
}: Props) {
    return (
        <Label label={label} htmlFor={id || name} className={`checkbox-label ${labelClassName}`}>
            <input
                type="checkbox"
                id={id || name}
                name={name}
                value={value || 'checked'}
                defaultChecked={defaultChecked}
                checked={checked}
                onChange={onChange}
                className={`checkbox ${className}`}
                disabled={disabled}
            />
        </Label>
    );
}
