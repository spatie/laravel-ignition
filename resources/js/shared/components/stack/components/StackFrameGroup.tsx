import React from 'react';
import ExceptionClass from 'resources/js/shared/components/ExceptionClass';
import FilePath from 'resources/js/shared/components/FilePath';
import LineNumber from 'resources/js/shared/components/LineNumber';
import { StackFrameGroupType } from 'resources/js/shared/types';

type Props = {
    frameGroup: StackFrameGroupType;
    onExpand: () => void;
    onSelect: (frameNumber: number) => void;
};

export default function StackFrameGroup({ frameGroup, onExpand, onSelect }: Props) {
    if (!frameGroup.expanded && frameGroup.type === 'vendor') {
        return (
            <li className="stack-frame-group stack-frame-group-vendor" onClick={onExpand}>
                <div className="stack-frame | cursor-pointer">
                    <button className="stack-frame-number">
                        <i className="fas fa-plus-circle text-gray-500" />
                    </button>
                    <div className="col-span-2 stack-frame-text">
                        <button className="text-left text-gray-500">
                            {frameGroup.frames.length > 1
                                ? `${frameGroup.frames.length} vendor frames…`
                                : '1 vendor frame…'}
                        </button>
                    </div>
                </div>
            </li>
        );
    }

    if (frameGroup.type === 'unknown') {
        return (
            <li className="stack-frame-group stack-frame-group-unknown">
                <div className="stack-frame">
                    <button className="stack-frame-number"></button>
                    <div className="col-span-2 stack-frame-text">
                        <span className="text-left text-gray-500">
                            {frameGroup.frames.length > 1
                                ? `${frameGroup.frames.length} unknown frames`
                                : '1 unknown frame'}
                        </span>
                    </div>
                </div>
            </li>
        );
    }

    return (
        <li>
            <ul
                className={`stack-frame-group ${
                    frameGroup.type === 'vendor' ? 'stack-frame-group-vendor' : ''
                }`}
            >
                {frameGroup.frames.map((frame, i) => (
                    <li
                        key={i}
                        className={`stack-frame ${
                            frame.selected ? 'stack-frame-selected' : ''
                        } | cursor-pointer`}
                        onClick={() => onSelect(frame.frame_number)}
                    >
                        <div className="stack-frame-number">{frame.frame_number}</div>
                        <div className="stack-frame-text">
                            {i === 0 && (
                                <header
                                    className={`stack-frame-header ${frame.class ? 'mb-1' : ''}`}
                                >
                                    <FilePath
                                        className={
                                            frameGroup.type === 'vendor'
                                                ? 'text-gray-800'
                                                : 'text-purple-800'
                                        }
                                        path={frame.relative_file}
                                    />
                                </header>
                            )}
                            <ExceptionClass name={frame.class} method={frame.method} />
                        </div>
                        <div className="stack-frame-line">
                            <LineNumber value={frame.line_number} />
                        </div>
                    </li>
                ))}
            </ul>
        </li>
    );
}
