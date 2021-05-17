import * as React from 'react';
/* import gitUrlParse from 'git-url-parse'; */ /* @todo use again (caused 'require("url")' issues) */
import { ContextItem, ErrorOccurrence, ErrorOccurrenceWithFrames } from '../../types';
import { getContextValues } from 'resources/js/shared/util';
import DefinitionList from 'resources/js/shared/components/DefinitionList';
import Alert from 'resources/js/shared/components/Alert';

type Props = {
    errorOccurrence: ErrorOccurrenceWithFrames;
};

export default function ContextTab({ errorOccurrence }: Props) {
    const env = getContextValues(errorOccurrence, 'env');
    const git = getContextValues(errorOccurrence, 'git');
    const context = getContextValues(errorOccurrence, 'context');

    const predefinedContextItemGroups = [
        'request',
        'request_data',
        'headers',
        'session',
        'cookies',
        'view',
        'queries',
        'route',
        'user',
        'env',
        'git',
        'context',
        'logs',
        'dumps',
    ];

    function getCustomContextItemGroups(occurrence: ErrorOccurrence): {
        [key: string]: Array<ContextItem>;
    } {
        const customGroups = Object.keys(occurrence.context_items).filter(
            (key) => !predefinedContextItemGroups.includes(key),
        );

        return Object.assign(
            {},
            ...customGroups.map((prop) => {
                return {
                    [prop]: occurrence.context_items[prop],
                };
            }),
        );
    }

    const customContextGroups = getCustomContextItemGroups(errorOccurrence);

    /* const gitInfo = getGitInfo(git.remote, git.hash); */

    return (
        <div className="tab-content">
            <div className="layout-col">
                <section className="tab-content-section border-none">
                    {!!Object.keys(git).length ? (
                        <DefinitionList title="Git">
                            <DefinitionList.Row
                                label="Repository"
                                value={
                                    {
                                        /* <a className="underline" href={gitInfo.repoUrl} target="_blank">
                                        {gitInfo.repoUrl}
                                    </a> */
                                    }
                                }
                            />
                            <DefinitionList.Row
                                value={
                                    {
                                        /* <a href={gitInfo.commitUrl} target="_blank">
                                        "{git.message}" (<code>{git.hash}</code>)
                                    </a> */
                                    }
                                }
                                label="Message"
                            />
                            <DefinitionList.Row value={git.tag} label="Tag" />
                            {git.isDirty && (
                                <div className="mt-4 sm:col-start-2">
                                    <Alert className="inline-block min-h-0" type="warning">
                                        This commit is dirty. (Un)staged changes have been made
                                        since this commit.
                                    </Alert>
                                </div>
                            )}
                        </DefinitionList>
                    ) : (
                        <DefinitionList title="Git">
                            {Object.entries(env).map(([key, value]) => (
                                <DefinitionList.Row key={key} value={value} label={key} />
                            ))}
                        </DefinitionList>
                    )}
                </section>
                <DefinitionList title="Environment information" className="tab-content-section">
                    {errorOccurrence.application_version && (
                        <DefinitionList.Row
                            key="app_version"
                            value={errorOccurrence.application_version}
                            label="app_version"
                        />
                    )}

                    {Object.entries(env).map(([key, value]) => (
                        <DefinitionList.Row key={key} value={value} label={key} />
                    ))}
                </DefinitionList>

                <DefinitionList title="Generic context" className="tab-content-section">
                    {Object.entries(context).map(([key, value]) => (
                        <DefinitionList.Row key={key} value={value} label={key} />
                    ))}
                </DefinitionList>

                {Object.entries(customContextGroups).map(([groupName, customGroup]) => (
                    <DefinitionList
                        key={groupName}
                        title={groupName}
                        className="tab-content-section"
                    >
                        {Object.entries(customGroup).map(([key, value]) => (
                            <DefinitionList.Row key={key} value={value.value} label={value.name} />
                        ))}
                    </DefinitionList>
                ))}
            </div>
        </div>
    );
}
/* 
function getGitInfo(remote?: string, hash?: string): { resource: string; repoUrl: string; commitUrl: string } {
    if (!remote) {
        return {
            resource: '',
            repoUrl: '',
            commitUrl: '',
        };
    }

    const repoInfo = gitUrlParse(remote);

    const repoUrl = gitUrlParse.stringify({ ...repoInfo, git_suffix: false }, 'https');

    return {
        repoUrl,
        resource: repoInfo.resource,
        commitUrl: `${repoUrl}/commit/${hash}`,
    };
} */
