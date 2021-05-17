import * as React from 'react';
import DefinitionList from '../DefinitionList';
import { ErrorOccurrenceWithFrames } from '../../types';
import { getContextValues } from '../../util';

type Props = {
    errorOccurrence: ErrorOccurrenceWithFrames;
};

export default function AppTab({ errorOccurrence }: Props) {
    const route = getContextValues(errorOccurrence, 'route');
    const view = getContextValues(errorOccurrence, 'view');

    return (
        <div className="tab-content">
            <div className="layout-col">
                <DefinitionList title="Routing" className="tab-content-section border-none">
                    <DefinitionList.Row value={route.controllerAction} label="Controller" />
                    <DefinitionList.Row value={route.route || 'unknown'} label="Route name" />
                    <DefinitionList.Row
                        value={
                            <DefinitionList>
                                {Object.entries(route.routeParameters || []).map(
                                    ([key, parameter]) => (
                                        <DefinitionList.Row
                                            key={key}
                                            label={key}
                                            value={parameter as string}
                                        />
                                    ),
                                )}
                            </DefinitionList>
                        }
                        label="Route parameters"
                    />
                    <DefinitionList.Row
                        value={
                            <DefinitionList>
                                {(route.middleware || []).map((middleware: string, i: number) => (
                                    <DefinitionList.Row key={i} value={middleware} />
                                ))}
                            </DefinitionList>
                        }
                        label="Middleware"
                    />
                </DefinitionList>

                <DefinitionList title="View" className="tab-content-section">
                    <DefinitionList.Row value={view.view} label="View name" />
                    <DefinitionList.Row
                        value={
                            <DefinitionList>
                                {Object.entries(view.data || {}).map(([key, dump]) => (
                                    <DefinitionList.Row
                                        key={key}
                                        label={key}
                                        value={
                                            <div
                                                dangerouslySetInnerHTML={{ __html: dump as string }}
                                            />
                                        }
                                    />
                                ))}
                            </DefinitionList>
                        }
                        label="View data"
                    />
                </DefinitionList>
            </div>
        </div>
    );
}
