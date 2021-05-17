import * as React from 'react';
import md5 from 'md5';
import { ErrorOccurrenceWithFrames } from '../../types';
import { getContextValues } from 'resources/js/shared/util';
import DefinitionList from 'resources/js/shared/components/DefinitionList';
import CopyableCode from 'resources/js/shared/components/CopyableCode';

type Props = {
    errorOccurrence: ErrorOccurrenceWithFrames;
};

export default function UserTab({ errorOccurrence }: Props) {
    const request = getContextValues(errorOccurrence, 'request');
    const user = getContextValues(errorOccurrence, 'user');

    let gravatar = '';

    if (user.email) {
        gravatar = `https://www.gravatar.com/avatar/${md5(user.email)}.jpg?s=80`;
    }

    return (
        <div className="tab-content">
            <div className="layout-col">
                <DefinitionList title="User Data" className="tab-content-section border-none">
                    {user.email && (
                        <DefinitionList.Row value={<img src={gravatar} />} label="Gravatar" />
                    )}
                    <DefinitionList.Row value={user.email} label="Email" />
                    <DefinitionList.Row
                        value={<CopyableCode>{JSON.stringify(user, null, 4)}</CopyableCode>}
                        label="User data"
                    />
                </DefinitionList>

                <DefinitionList title="Client info" className="tab-content-section">
                    <DefinitionList.Row value={request.ip} label="IP address" />
                    <DefinitionList.Row value={request.useragent} label="User agent" />
                    {request.flare_user_agent_platform && (
                        <DefinitionList.Row
                            value={request.flare_user_agent_platform}
                            label="Platform"
                        />
                    )}
                    {request.flare_user_agent_device && (
                        <DefinitionList.Row
                            value={request.flare_user_agent_device}
                            label="Device"
                        />
                    )}
                    {request.flare_user_agent_browser && (
                        <DefinitionList.Row
                            value={request.flare_user_agent_browser}
                            label="Browser"
                        />
                    )}
                    {request.flare_user_agent_browser_version && (
                        <DefinitionList.Row
                            value={request.flare_user_agent_browser_version}
                            label="Browser version"
                        />
                    )}
                </DefinitionList>
            </div>
        </div>
    );
}
