import React, { useState, ChangeEvent } from 'react';
import { ErrorOccurrenceWithFrames, DebugEventType } from '../../types';
import DefinitionList from 'resources/js/shared/components/DefinitionList';
import { getContextValues } from 'resources/js/shared/util';
import map from 'lodash/map';
import sortBy from 'lodash/sortBy';
import DebugEvent from 'resources/js/shared/components/DebugEvent';
import Button from 'resources/js/shared/components/Button';
import CheckboxField from 'resources/js/shared/components/CheckboxField';
import LocalTime from 'resources/js/shared/components/LocalTime';

type Props = {
    errorOccurrence: ErrorOccurrenceWithFrames;
};

export default function DebugTab({ errorOccurrence }: Props) {
    const [visibleTypesMap, setVisibleTypesMap] = useState({
        dump: true,
        glow: true,
        log: true,
        query: true,
    });

    const visibleTypes = Object.entries(visibleTypesMap)
        .filter(([_type, visible]) => visible)
        .map(([type]) => type);

    function showAllTypes() {
        setVisibleTypesMap({
            dump: true,
            glow: true,
            log: true,
            query: true,
        });
    }

    function toggleVisibleType(type: string) {
        return function (e: ChangeEvent<HTMLInputElement>) {
            setVisibleTypesMap({ ...visibleTypesMap, [type]: e.target.checked });
        };
    }

    const dumps = getContextValues(errorOccurrence, 'dumps');
    const glows = errorOccurrence.glows;
    const logs = getContextValues(errorOccurrence, 'logs');
    const queries = getContextValues(errorOccurrence, 'queries');

    const events: Array<DebugEventType> = sortBy(
        [
            ...map(dumps, createDumpEvent),
            ...map(glows, createGlowEvent),
            ...map(logs, createLogEvent),
            ...map(queries, createQueryEvent),
        ],
        'microtime',
    ).filter((event) => visibleTypes.includes(event.type));

    return (
        <div className="tab-content">
            <div className="sticky top-0 z-10 grid grid-cols-auto grid-flow-col items-center justify-center px-6 py-2 bg-gray-100 border-b border-tint-200 text-xs">
                <nav className="grid grid-cols-auto grid-flow-col items-center gap-x-6 gap-y-2">
                    <CheckboxField
                        labelClassName="text-gray-600"
                        checked={visibleTypesMap.dump}
                        onChange={toggleVisibleType('dump')}
                        label="Dumps"
                    />
                    <CheckboxField
                        labelClassName="text-gray-600"
                        checked={visibleTypesMap.glow}
                        onChange={toggleVisibleType('glow')}
                        label="Glows"
                    />
                    <CheckboxField
                        labelClassName="text-gray-600"
                        checked={visibleTypesMap.log}
                        onChange={toggleVisibleType('log')}
                        label="Logs"
                    />
                    <CheckboxField
                        labelClassName="text-gray-600"
                        checked={visibleTypesMap.query}
                        onChange={toggleVisibleType('query')}
                        label="Queries"
                    />
                    {visibleTypes.length !== Object.keys(visibleTypesMap).length && (
                        <Button
                            secondary
                            link
                            className="no-underline absolute left-full ml-6 hidden | sm:block"
                            onClick={showAllTypes}
                        >
                            Select&nbsp;all
                        </Button>
                    )}
                </nav>
            </div>
            {events.length ? (
                <div className="layout-col">
                    <DefinitionList className="tab-content-section no-border">
                        {events.map((event, i) => (
                            <DefinitionList.Row
                                key={i}
                                value={<DebugEvent event={event} />}
                                label={
                                    <LocalTime
                                        dateTime={Math.floor(event.microtime * 1000)}
                                        relative={false}
                                        format="yyyy-MM-dd HH:mm:ss"
                                    />
                                }
                            />
                        ))}
                    </DefinitionList>
                </div>
            ) : (
                <p className="absolute inset-0 grid place-center alert-empty">
                    No debug data available.
                </p>
            )}
        </div>
    );
}

function createQueryEvent({
    microtime,
    sql,
    time,
    connection_name,
    bindings,
    replace_bindings,
}: any): DebugEventType {
    return {
        microtime,
        type: 'query',
        label: sql,
        metadata: { time, connection_name },
        context: bindings || {},
        replace_bindings: replace_bindings,
    };
}

function createDumpEvent({ microtime, html_dump, file, line_number }: any): DebugEventType {
    return {
        microtime,
        type: 'dump',
        label: html_dump,
        metadata: { file, line_number },
        context: {},
    };
}

function createLogEvent({ microtime, context, level, message }: any): DebugEventType {
    return {
        microtime,
        type: 'log',
        label: message,
        metadata: { level },
        context,
    };
}

function createGlowEvent({ microtime, message_level, meta_data, time, name }: any): DebugEventType {
    return {
        type: 'glow',
        label: name,
        microtime,
        metadata: { time, message_level },
        context: meta_data || {},
    };
}
