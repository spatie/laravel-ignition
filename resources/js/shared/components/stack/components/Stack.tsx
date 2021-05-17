import React, { useEffect, useLayoutEffect, useMemo, useReducer, useState } from 'react';

import useKeyboardShortcut from 'resources/js/shared/hooks/useKeyboardShortcut';
import ExceptionClass from 'resources/js/shared/components/ExceptionClass';
import StackFrameGroup from './StackFrameGroup';
import {
    stackReducer,
    allVendorFramesAreExpanded,
    createFrameGroups,
    getSelectedFrame,
} from '../index';
import FilePath from 'resources/js/shared/components/FilePath';
import { ErrorFrame } from 'resources/js/shared/types';
import StackSnippet from 'resources/js/shared/components/stack/components/StackSnippet';

type Props = {
    open_frame_index?: number;
    frames: Array<ErrorFrame>;
};

export default function Stack({ frames, open_frame_index }: Props) {
    const initialState = useMemo(() => {
        let selectedFrame = frames.length;

        if (open_frame_index) {
            selectedFrame = frames.length - open_frame_index;
        }
        return stackReducer(
            { frames, expanded: [], selected: selectedFrame },
            { type: 'COLLAPSE_ALL_VENDOR_FRAMES' },
        );
    }, [frames]);

    const [state, dispatch] = useReducer(stackReducer, initialState);

    const vendorFramesExpanded = useMemo(() => allVendorFramesAreExpanded(state), [state]);
    const frameGroups = useMemo(() => createFrameGroups(state), [state]);
    const selectedFrame = useMemo(() => getSelectedFrame(state), [state]);

    useKeyboardShortcut('j', () => {
        dispatch({ type: 'SELECT_NEXT_FRAME' });
    });

    useKeyboardShortcut('k', () => {
        dispatch({ type: 'SELECT_PREVIOUS_FRAME' });
    });

    const [selectedRange, setSelectedRange] = useState<[number, number] | null>(null);

    useLayoutEffect(() => {
        const FramePattern = /F([0-9]+)?/gm;
        const LinePattern = /L([0-9]+)(-([0-9]+))?/gm;

        const frameMatches = FramePattern.exec(window.location.hash);
        const lineMatches = LinePattern.exec(window.location.hash);

        if (frameMatches) {
            const frameNumber = parseInt(frameMatches[1]);

            dispatch({ type: 'SELECT_FRAME', frame: frameNumber });
        }

        if (lineMatches) {
            const minLineNumber = parseInt(lineMatches[1]);
            const maxLineNumber = lineMatches[3] ? parseInt(lineMatches[3]) : minLineNumber;

            setSelectedRange([minLineNumber, maxLineNumber]);
        }
    }, []);

    useEffect(() => {
        const lineNumber = selectedRange
            ? selectedRange[0] === selectedRange[1]
                ? selectedRange[0]
                : `${selectedRange[0]}-${selectedRange[1]}`
            : null;

        window.history.replaceState(
            window.history.state,
            '',
            `#F${state.selected}${lineNumber ? 'L' + lineNumber : ''}`,
        );
    }, [state.selected, selectedRange]);

    return (
        <div className="stack">
            <div className="stack-nav">
                <div className="stack-nav-actions">
                    <div className="stack-nav-arrows">
                        <button className="hidden">
                            <i className="fas fa-arrow-up" />
                        </button>
                        <button className="hidden">
                            <i className="fas fa-arrow-down" />
                        </button>
                    </div>
                    <div className="px-4">
                        {vendorFramesExpanded ? (
                            <button
                                className="link-dimmed"
                                onClick={() => dispatch({ type: 'COLLAPSE_ALL_VENDOR_FRAMES' })}
                            >
                                Collapse vendor frames
                            </button>
                        ) : (
                            <button
                                className="link-dimmed"
                                onClick={() => dispatch({ type: 'EXPAND_ALL_VENDOR_FRAMES' })}
                            >
                                Expand vendor frames
                            </button>
                        )}
                    </div>
                </div>
                <div className="stack-frames">
                    <ol className="stack-frames-scroll scrollbar">
                        {frameGroups.map((frameGroup, i) => (
                            <StackFrameGroup
                                key={i}
                                frameGroup={frameGroup}
                                onExpand={() =>
                                    dispatch({
                                        type: 'EXPAND_FRAMES',
                                        frames: frameGroup.frames.map(
                                            (frame) => frame.frame_number,
                                        ),
                                    })
                                }
                                onSelect={(frameNumber) => {
                                    dispatch({ type: 'SELECT_FRAME', frame: frameNumber });
                                    setSelectedRange(null);
                                }}
                            />
                        ))}
                    </ol>
                </div>
            </div>
            <div className="stack-main">
                <div className="stack-main-header">
                    <ExceptionClass name={selectedFrame.class} method={selectedFrame.method} />
                    <div className="flex items-baseline justify-start">
                        <FilePath className="mt-1" path={selectedFrame.relative_file} />:
                        {selectedFrame.line_number}
                    </div>
                </div>
                <div className="stack-main-content">
                    <StackSnippet
                        code={selectedFrame.code_snippet}
                        highlightedLineNumber={selectedFrame.line_number}
                        selectedRange={selectedRange}
                        onSelectRange={setSelectedRange}
                    />
                </div>
            </div>
        </div>
    );
}
