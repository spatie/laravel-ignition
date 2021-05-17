import React from 'react';
import { ErrorOccurrenceWithFrames } from './types';
import OccurrenceTabs from './components/OccurrenceTabs';

import '../../css/app.css';

import AppTab from './components/tabs/AppTab';
import ContextTab from './components/tabs/ContextTab';
import DebugTab from './components/tabs/DebugTab';
import RequestTab from './components/tabs/RequestTab';
import StackTab from './components/tabs/StackTab';
import UserTab from './components/tabs/UserTab';
import IconSummary from 'resources/js/shared/components/IconSummary';

type Props = {
    errorOccurrence: ErrorOccurrenceWithFrames;
    manageSharesUrl?: string;
};

export default function ErrorUI({ errorOccurrence, manageSharesUrl }: Props) {
    return (
        <>
            <IconSummary />

            <OccurrenceTabs errorOccurrence={errorOccurrence} manageSharesUrl={manageSharesUrl}>
                <OccurrenceTabs.Tab
                    name={
                        <>
                            Stack<span className="hidden sm:inline">&nbsp;trace</span>
                        </>
                    }
                    component={StackTab}
                />
                <OccurrenceTabs.Tab name="Request" component={RequestTab} />
                <OccurrenceTabs.Tab name="App" component={AppTab} />
                <OccurrenceTabs.Tab name="User" component={UserTab} />
                <OccurrenceTabs.Tab name="Context" component={ContextTab} />
                <OccurrenceTabs.Tab name="Debug" component={DebugTab} />
            </OccurrenceTabs>
        </>
    );
}
