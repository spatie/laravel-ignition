import React, { Children, useState } from 'react';
import { ErrorOccurrenceWithFrames } from '../types';
import ShareButton from './ShareButton';
import useKeyboardShortcut from '../hooks/useKeyboardShortcut';
import ErrorBoundary from './ErrorBoundary';
import Alert from './Alert';

type Props = {
    errorOccurrence: ErrorOccurrenceWithFrames;
    children: Array<React.ReactElement | false>;
    isOnSharedPage?: boolean;
    manageSharesUrl?: string;
    useHistory?: boolean;
};

type Tab = {
    name: string | React.ReactElement;
    component: React.ComponentType<any>;
};

export default function OccurrenceTabs({
    errorOccurrence,
    children,
    isOnSharedPage = false,
    manageSharesUrl,
    useHistory,
}: Props) {
    const validChildren = children.filter((child) => child !== false) as Array<React.ReactElement>;

    const tabs: Array<Tab> = Children.map(validChildren, (child) => {
        return {
            name: child.props.name,
            component: child.props.component,
            checked: child.props.checked,
            onChange: child.props.onChange,
        };
    });

    const [currentTabIndex, setCurrentTabIndex] = useState(0); // TODO: useHistory ? getFromUrlFragment('tab-index') || 0 : 0

    function changeTab(tabIndex: number) {
        setCurrentTabIndex(tabIndex);

        if (useHistory) {
            // TODO: setInUrlFragment('tab-index', currentTabIndex);
        }
    }

    useKeyboardShortcut('h', () => {
        changeTab(currentTabIndex === 0 ? tabs.length - 1 : currentTabIndex - 1);
    });

    useKeyboardShortcut('l', () => {
        changeTab(currentTabIndex === tabs.length - 1 ? 0 : currentTabIndex + 1);
    });

    const Tab = tabs[currentTabIndex].component;

    return (
        <div className="tabs">
            <nav className="tab-nav">
                <ul className="tab-bar">
                    {tabs.map((tab, i) => (
                        <li key={i}>
                            <button
                                className={`tab ${i === currentTabIndex ? 'tab-active' : ''}`}
                                onClick={() => changeTab(i)}
                            >
                                {tab.name}
                            </button>
                        </li>
                    ))}
                </ul>
                {!isOnSharedPage && (
                    <>
                        <div className="tab-delimiter" />
                        <ShareButton
                            className="tab"
                            errorOccurrence={errorOccurrence}
                            manageSharesUrl={manageSharesUrl}
                        >
                            Share
                            <i className="ml-2 fas fa-share" />
                        </ShareButton>
                    </>
                )}
            </nav>
            <div className="tab-main">
                <ErrorBoundary fallbackComponent={<Fallback />}>
                    <Tab errorOccurrence={errorOccurrence} />
                </ErrorBoundary>
            </div>
        </div>
    );
}

function Fallback() {
    return (
        <Alert className="text-xs text-center py-12 h-12" type="error">
            Something went wrong
        </Alert>
    );
}

OccurrenceTabs.Tab = (_props: Tab) => null;
