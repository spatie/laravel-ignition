import React from 'react';
import ReactDOM from 'react-dom';
import ErrorCard from 'resources/js/ignition/components/ErrorCard';
import SolutionCard from 'resources/js/ignition/components/SolutionCard';
import { igniteDataContext } from 'resources/js/ignition/igniteDataContext';
import ErrorUI from 'resources/js/shared/ErrorUI';
import { ErrorOccurrenceWithFrames, IgnitionErrorOccurrence } from 'resources/js/shared/types';

import './symfony/symfony';
import './symfony/symfony.css';

window.ignite = (data) => {
    const errorOccurrence = transformIgnitionError(data.report);

    ReactDOM.render(
        <igniteDataContext.Provider value={data}>
            <div className="layout-col mt-12">
                <ErrorCard errorOccurrence={errorOccurrence} />

                {data.report.solutions.length > 0 && (
                    <div className="layout-col z-1">
                        <SolutionCard flareErrorSolutions={data.report.solutions} />
                    </div>
                )}

                <ErrorUI errorOccurrence={errorOccurrence} />
            </div>
        </igniteDataContext.Provider>,
        document.querySelector('#app'),
    );
};

function transformIgnitionError(ignitionError: IgnitionErrorOccurrence): ErrorOccurrenceWithFrames {
    return {
        frames: ignitionError.stacktrace.map((frame) => ({
            ...frame,
            relative_file: frame.file
                .replace(ignitionError.application_path + '/', '')
                .replace(ignitionError.application_path + '\\', ''),
            class: frame.class || '',
        })),
        context_items: {
            request: [
                { group: 'request', name: 'url', value: ignitionError?.context?.request?.url },
                {
                    group: 'request',
                    name: 'useragent',
                    value: ignitionError.context?.request?.useragent,
                },
                { group: 'request', name: 'ip', value: ignitionError.context?.request?.ip },
                { group: 'request', name: 'method', value: ignitionError.context?.request?.method },
            ],
            request_data: [
                {
                    group: 'request_data',
                    name: 'queryString',
                    value: ignitionError.context.request_data.queryString,
                },
                {
                    group: 'request_data',
                    name: 'body',
                    value: ignitionError.context.request_data.body,
                },
                {
                    group: 'request_data',
                    name: 'files',
                    value: ignitionError.context.request_data.files,
                },
            ],
            queries: ignitionError.context?.queries?.map((query, i) => ({
                group: 'queries',
                name: String(i),
                value: {
                    ...query,
                    replace_bindings: true,
                    bindings: query.bindings.map((binding) => ({
                        type: typeof binding,
                        value: binding,
                    })),
                },
            })),
            dumps: ignitionError.context?.dumps.map((value, i) => ({
                group: 'dumps',
                name: String(i),
                value,
            })),
            logs: ignitionError.context?.logs?.map((value, i) => ({
                group: 'logs',
                name: String(i),
                value,
            })),
            headers: Object.entries(ignitionError.context.headers || {}).map(([name, [value]]) => ({
                group: 'headers',
                name,
                value,
            })),
            cookies: Object.entries(ignitionError.context.cookies || {}).map(([name, value]) => ({
                group: 'headers',
                name,
                value,
            })),
            session: Object.entries(ignitionError.context.session || {}).map(([name, value]) => ({
                group: 'session',
                name,
                value,
            })),
            env: Object.entries(ignitionError.context.env || {}).map(([name, value]) => ({
                group: 'env',
                name,
                value,
            })),
            user: Object.entries(ignitionError.context.user || {}).map(([name, value]) => ({
                group: 'user',
                name,
                value,
            })),
            route: Object.entries(ignitionError.context.route || {}).map(([name, value]) => ({
                group: 'route',
                name,
                value,
            })),
            git: Object.entries(ignitionError.context.git || {}).map(([name, value]) => ({
                group: 'git',
                name,
                value,
            })),
            view: [] /* @todo ? */,
            context: [] /* @todo ? */,
        },
        id: 0,
        error_id: 0,
        occurrence_number: 0,
        received_at: new Date(ignitionError.seen_at * 1000).toISOString(),
        seen_at_url: ignitionError?.context?.request?.url,
        exception_class: ignitionError.exception_class,
        exception_message: ignitionError.message,
        application_path: ignitionError.application_path,
        application_version: ignitionError.application_version || '',
        language_version: ignitionError.language_version,
        framework_version: ignitionError.framework_version,
        notifier_client_name: 'Flare',
        stage: ignitionError.stage,
        first_frame_class: ignitionError.stacktrace[0].class || '',
        first_frame_method: ignitionError.stacktrace[0].method,
        glows: ignitionError.glows.map((glow) => ({
            ...glow,
            id: 0,
            received_at: '',
        })) /* @todo are these extra properties needed/used? */,
        solutions: [],
        group_identifier: '',
        group_count: 0,
        group_detail_query: '',
        links: { show: '', share: '' } /* @todo catch these being empty in the UI */,
    };
}
