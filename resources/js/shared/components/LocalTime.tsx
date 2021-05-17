import React, { useMemo } from 'react';
import formatDate from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import isNumber from 'lodash/isNumber';

type Props = {
    dateTime: string | number | null | undefined;
    format?: string;
    relative?: boolean;
    className?: string;
    style?: React.CSSProperties;
};

export default function LocalTime({ dateTime, relative = true, format, ...props }: Props) {
    const formattedTime = useMemo(() => {
        if (dateTime === null || dateTime === undefined) {
            return '';
        }

        const date = typeof dateTime === 'string' ? new Date(dateTime) : dateTime;

        if (relative) {
            const distanceInWords = formatDistanceToNow(date, { addSuffix: true });

            if (distanceInWords === 'less than a minute') {
                return 'just now';
            }

            return distanceInWords
                .replace('about', '')
                .replace('minutes', 'min')
                .replace('minute', 'min')
                .replace('seconds', 'sec')
                .replace('second', 'sec');
        } else {
            return (
                <span className="variant-tabular">
                    {format ? (
                        formatDate(date, format)
                    ) : (
                        <>
                            {formatDate(date, 'yyyy-MM-dd')}{' '}
                            <span className="text-tint-700">{formatDate(date, 'HH:mm:ss')}</span>
                        </>
                    )}
                </span>
            );
        }
    }, [dateTime, format, relative]);

    if (!dateTime) {
        return null;
    }

    const formattedDateTime = formatDate(
        isNumber(dateTime) ? dateTime : parseISO(dateTime),
        'yyyy-MM-dd HH:mm:ss zzzz',
    );

    return (
        <time dateTime={formattedDateTime} title={formattedDateTime} {...props}>
            {formattedTime}
        </time>
    );
}
