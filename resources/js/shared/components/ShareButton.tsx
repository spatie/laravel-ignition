import React, { useState } from 'react';
import Downshift from 'downshift';
import { ErrorOccurrence, SharePostData, Tabname } from '../types';
import CopyableCode from 'resources/js/shared/components/CopyableCode';
import Button from 'resources/js/shared/components/Button';
import CheckboxField from 'resources/js/shared/components/CheckboxField';
import axios from 'axios';
import Icon from 'resources/js/shared/components/Icon';

type Props = {
    children: React.ReactChild | Array<React.ReactChild>;
    errorOccurrence: ErrorOccurrence;
    disabled?: boolean;
    className?: string;
    manageSharesUrl?: string;
};

/* @todo share button is different in ignition than in flare */

export default function ShareButton({
    children,
    errorOccurrence,
    disabled = false,
    className = '',
    manageSharesUrl,
}: Props) {
    const [sharedUrl, setSharedUrl] = useState('');
    const [error, setError] = useState('');

    const [selectedTabs, setSelectedTabs] = useState<
        Array<{
            name: Tabname;
            prettyName: string;
            selected: boolean;
        }>
    >([
        { name: 'stackTraceTab', prettyName: 'Stack trace', selected: true },
        { name: 'requestTab', prettyName: 'Request', selected: true },
        { name: 'appTab', prettyName: 'App', selected: true },
        { name: 'userTab', prettyName: 'User', selected: true },
        { name: 'contextTab', prettyName: 'Context', selected: true },
        { name: 'debugTab', prettyName: 'Debug', selected: true },
    ]);

    function toggleTabSelected(
        tabName: 'stackTraceTab' | 'requestTab' | 'appTab' | 'userTab' | 'contextTab' | 'debugTab',
    ) {
        const tab = selectedTabs.find((tab) => tab.name === tabName);

        if (tab) {
            setSelectedTabs(
                selectedTabs.map((tab) =>
                    tab.name === tabName ? { ...tab, selected: !tab.selected } : tab,
                ),
            );
        }
    }

    async function onShareError() {
        const endpoint = errorOccurrence.links.share;

        const selectedTabNames = selectedTabs
            .filter((selectedTab) => selectedTab.selected)
            .map((selectedTab) => selectedTab.name);

        const data: SharePostData = { selectedTabNames, lineSelection: window.location.hash };

        try {
            const { data: response } = await axios.post(endpoint, data);

            if (
                response &&
                response.shared_error &&
                response.shared_error.links &&
                response.shared_error.links.show
            ) {
                setSharedUrl(response.shared_error.links.show);
            }
        } catch (error) {
            setError('Something went wrong while sharing, please try again.');
        }
    }

    return (
        <Downshift>
            {({ getItemProps, getMenuProps, getLabelProps, isOpen, toggleMenu }) => (
                <div>
                    <label {...getLabelProps({ className: 'hidden', htmlFor: undefined })}>
                        Share options menu
                    </label>

                    <div>
                        <button
                            disabled={disabled}
                            className={`${className} ${isOpen && 'tab-active'} `}
                            onClick={() => toggleMenu()}
                        >
                            <Icon name="share" />
                            {children}
                        </button>
                        {isOpen && (
                            <ul
                                {...getMenuProps()}
                                className="dropdown z-10 right-0 top-full bg-gray-700 text-white p-4 overflow-visible"
                                style={{ minWidth: '18rem', marginRight: '-1px' }}
                            >
                                <h5 className="mb-3 text-left text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">
                                    Share publicly
                                </h5>
                                <div className="grid grid-cols-2 justify-start gap-x-6 gap-y-2">
                                    {selectedTabs.map(({ selected, name, prettyName }) => (
                                        <CheckboxField
                                            {...getItemProps({ item: name, key: name })}
                                            labelClassName="text-gray-200 hover:text-white"
                                            onChange={() =>
                                                toggleTabSelected(
                                                    name as
                                                        | 'stackTraceTab'
                                                        | 'requestTab'
                                                        | 'appTab'
                                                        | 'userTab'
                                                        | 'contextTab'
                                                        | 'debugTab',
                                                )
                                            }
                                            checked={selected}
                                            label={prettyName}
                                        />
                                    ))}
                                </div>
                                <div className="grid grid-cols-auto grid-flow-col justify-between items-center mt-3">
                                    <Button
                                        secondary
                                        className="bg-tint-600 text-white"
                                        size="sm"
                                        onClick={onShareError}
                                    >
                                        Create&nbsp;share
                                    </Button>
                                    {manageSharesUrl && (
                                        <a
                                            className="link-dimmed-invers underline"
                                            target="_blank"
                                            href={manageSharesUrl}
                                        >
                                            Manage shares
                                        </a>
                                    )}
                                </div>

                                {error && <p className="mt-3 text-red-400">{error}</p>}

                                {sharedUrl && (
                                    <div className="mt-3 flex">
                                        <CopyableCode className="text-white max-w-xs sm:max-w-md overflow-x-auto overflow-y-hidden scrollbar">
                                            {sharedUrl}
                                        </CopyableCode>
                                        <i
                                            className="cursor-pointer p-2 pr-0 fas fa-external-link-alt text-xs"
                                            onClick={() => window.open(sharedUrl)}
                                        />
                                    </div>
                                )}
                            </ul>
                        )}
                    </div>
                </div>
            )}
        </Downshift>
    );
}
