import * as React from 'react';
import { ErrorOccurrenceWithFrames } from 'resources/js/shared/types';
import { stringifyOccurrenceData, getContextValues } from 'resources/js/shared/util';
import DefinitionList from 'resources/js/shared/components/DefinitionList';

type Props = {
    errorOccurrence: ErrorOccurrenceWithFrames;
};

export default function RequestTab({ errorOccurrence }: Props) {
    const request = getContextValues(errorOccurrence, 'request');
    const requestData = getContextValues(errorOccurrence, 'request_data');
    const headers = getContextValues(errorOccurrence, 'headers');
    const session = getContextValues(errorOccurrence, 'session');
    const cookies = getContextValues(errorOccurrence, 'cookies');

    return (
        <div className="tab-content">
            <div className="layout-col">
                <DefinitionList title="Request" className="tab-content-section border-none">
                    <DefinitionList.Row value={request.url} label="URL" />
                    <DefinitionList.Row value={request.method} label="Method" />
                </DefinitionList>
                <DefinitionList title="Headers" className="tab-content-section">
                    {Object.entries(headers || {}).map(([key, value]) => (
                        <DefinitionList.Row key={key} value={value} label={key} />
                    ))}
                </DefinitionList>
                <DefinitionList title="Query string" className="tab-content-section">
                    {Object.entries(requestData.queryString || {}).map(([key, value]) => (
                        <DefinitionList.Row key={key} value={value as any} label={key} />
                    ))}
                </DefinitionList>
                <DefinitionList title="Body" className="tab-content-section">
                    {Object.entries(requestData.body || {}).map(([key, value]) => (
                        <DefinitionList.Row key={key} value={value as any} label={key} />
                    ))}
                </DefinitionList>
                <DefinitionList title="Files" className="tab-content-section">
                    {Object.entries(requestData.files || {}).map(([key, value]) => (
                        <DefinitionList.Row key={key} value={value as any} label={key} />
                    ))}
                </DefinitionList>
                <DefinitionList title="Session" className="tab-content-section">
                    {Object.entries(session || {}).map(([key, value]) => (
                        <DefinitionList.Row
                            key={key}
                            value={stringifyOccurrenceData(value)}
                            label={key}
                        />
                    ))}
                </DefinitionList>
                <DefinitionList title="Cookies" className="tab-content-section">
                    {Object.entries(cookies || {}).map(([key, value]) => (
                        <DefinitionList.Row key={key} value={value} label={key} />
                    ))}
                </DefinitionList>
            </div>
        </div>
    );
}
