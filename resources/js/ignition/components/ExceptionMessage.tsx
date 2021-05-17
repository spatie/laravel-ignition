import React, { useState } from 'react';

type Props = {
    message: string;
    className?: string;
};

export default function ExceptionMessage({ message, className = '' }: Props) {
    const [fullException, setFullException] = useState<boolean>(false);

    return (
        <span
            className={`ui-exception-message ${
                fullException ? 'ui-exception-message-full' : ''
            } ${className}`}
            onClick={() => setFullException(!fullException)}
        >
            {message}
        </span>
    );
}
