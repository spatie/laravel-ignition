import React from 'react';
import {
    DebugEventType,
    DumpDebugEvent,
    GlowDebugEvent,
    QueryDebugEvent,
    LogDebugEvent,
} from '../types';
import DefinitionList from './DefinitionList';
import CopyableCode from './CopyableCode';
import LineNumber from './LineNumber';
import FilePath from './FilePath';
import { stringifyOccurrenceData } from '../util';
import sqlFormatter from 'sql-formatter';

type Props = {
    event: DebugEventType;
};

export default function DebugEvent({ event }: Props) {
    if (event.type === 'dump') {
        return <DumpEvent {...event} />;
    }

    if (event.type === 'glow') {
        return <GlowEvent {...event} />;
    }

    if (event.type === 'log') {
        return <LogEvent {...event} />;
    }

    if (event.type === 'query') {
        return <QueryEvent {...event} />;
    }

    return null;
}

function DumpEvent(props: DumpDebugEvent) {
    return (
        <div className="mb-2 pb-4 border-b border-dashed border-gray-300">
            <div className="mb-2 font-semibold text-xs">Dump</div>
            <code className="code-block mb-4" dangerouslySetInnerHTML={{ __html: props.label }} />
            {props.metadata.file && (
                <>
                    <FilePath path={props.metadata.file} />
                    <LineNumber value={props.metadata.line_number} />
                </>
            )}
        </div>
    );
}

function GlowEvent(props: GlowDebugEvent) {
    return (
        <div className="mb-2 pb-4 border-b border-dashed border-gray-300">
            <div className="mb-2 font-semibold text-xs">Glow</div>
            <code className="code-block mb-4" dangerouslySetInnerHTML={{ __html: props.label }} />
            <div className="text-sm">Level: {props.metadata.message_level}</div>
            <DefinitionList title="">
                {Object.entries(props.context).map(([key, value]) => (
                    <DefinitionList.Row key={key} value={value} label={key} />
                ))}
            </DefinitionList>
        </div>
    );
}

function LogEvent(props: LogDebugEvent) {
    return (
        <div className="mb-2 pb-4 border-b border-dashed border-gray-300">
            <div className="mb-2 font-semibold text-xs">
                Log
                <span className={`ml-2 px-1 font-normal level-${props.metadata.level}`}>
                    {props.metadata.level}
                </span>
            </div>
            <code className="code-block mb-4" dangerouslySetInnerHTML={{ __html: props.label }} />
            <DefinitionList title="">
                {Object.entries(props.context).map(([key, value]) => (
                    <DefinitionList.Row value={value} label={key} key={key} />
                ))}
            </DefinitionList>
        </div>
    );
}

function formatLabel(label: string, replaceBindings: Array<{ type: string; value: any }>) {
    const placeholder = "'%#this placeholder is long to force newlines#%'";
    const query = sqlFormatter.format(label.replace(/\?/g, placeholder));

    return (
        <>
            {query.split(placeholder).reduce((prev, current, i) => {
                if (!i) {
                    return [current];
                }

                const replacement = replaceBindings[i - 1];

                return prev.concat(
                    <span className="mt-2 inline-flex group" key={i}>
                        <CopyableCode>
                            <span className="text-purple-500">
                                {stringifyOccurrenceData(replacement.value)}
                            </span>
                        </CopyableCode>
                        <span className="absolute pointer-events-none z-10 right-full bottom-full -mr-2 -mb-1 whitespace-nowrap px-1 bg-white text-gray-700 text-xxs uppercase opacity-0 group-hover:opacity-100 transition-opacity shadow not-italic">
                            {replacement.type}
                        </span>
                    </span>,
                    current,
                );
            }, [] as Array<string | React.ReactElement>)}
        </>
    );
}

function QueryEvent(props: QueryDebugEvent) {
    return (
        <div className="mb-2 pb-4 border-b border-dashed border-gray-300">
            <div className="mb-2 font-semibold text-xs">Query</div>
            <DefinitionList className="mb-4">
                <dl className="definition-label">Connection</dl>
                <dd className="definition-definition ">{props.metadata.connection_name}</dd>
                <dl className="definition-label">Duration</dl>
                <dd className="definition-definition ">{props.metadata.time}ms</dd>
            </DefinitionList>

            <code className="code-block mb-4">
                {!props.replace_bindings ? (
                    <pre>{sqlFormatter.format(props.label)}</pre>
                ) : (
                    <pre>{formatLabel(props.label, props.context as any)}</pre>
                )}
            </code>

            {!props.replace_bindings && (
                <DefinitionList>
                    {Object.entries(props.context).map(([key, value]) => (
                        <React.Fragment key={key}>
                            <dl className="definition-label">{key}</dl>
                            <dd className="flex items-baseline">
                                {(value.type === 'int' || value.type === 'float') && (
                                    <CopyableCode>
                                        <span className="text-purple-500">{value.value}</span>
                                    </CopyableCode>
                                )}
                                {value.type !== 'int' && value.type !== 'float' && (
                                    <CopyableCode>
                                        <span className="text-purple-500">
                                            {stringifyOccurrenceData(value.value)}
                                        </span>
                                    </CopyableCode>
                                )}
                                <span className="ml-2 mr-auto text-gray-700 text-xxs uppercase">
                                    {value.type}
                                </span>
                            </dd>
                        </React.Fragment>
                    ))}
                </DefinitionList>
            )}
        </div>
    );
}
