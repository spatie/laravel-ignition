import React, { useState, useRef, useEffect } from 'react';
import ReactMarkdown from 'react-markdown';
import { ErrorSolution } from 'resources/js/shared/types';
import useSolutions from 'resources/js/ignition/components/useSolutions';

type Props = {
    flareErrorSolutions: ErrorSolution[];
    className?: string;
};

export default function SolutionCard({ flareErrorSolutions, className = '', ...props }: Props) {
    const { isHidingSolutions, toggleHidingSolutions } = useSolutions();
    const [solution, setSolution] = useState(flareErrorSolutions[0]);
    const [currentSolutionIndex, setCurrentSolutionIndex] = useState(1);
    const solutionCard = useRef() as React.MutableRefObject<HTMLDivElement>;
    let animationTimeout: number;

    useEffect(() => {
        if (isHidingSolutions) {
            solutionCard.current.classList.add('solution-hidden');
        }
    }, []);

    function clickHidingSolutions() {
        if (!isHidingSolutions) {
            solutionCard.current.classList.add('solution-hiding');

            animationTimeout = window.setTimeout(() => {
                solutionCard.current.classList.remove('solution-hiding');
                toggleHidingSolutions();
            }, 100);
        } else {
            window.clearTimeout(animationTimeout);
            toggleHidingSolutions();
        }
    }

    function updateSolution(page: number) {
        setSolution(flareErrorSolutions[page - 1]);
        setCurrentSolutionIndex(page);
    }

    return (
        <div className={`${className}`} {...props}>
            <div className={`solution-toggle ${isHidingSolutions ? 'solution-toggle-show' : ''}`}>
                <a className="link-solution" target="_blank" onClick={clickHidingSolutions}>
                    {isHidingSolutions ? (
                        <>
                            <i className="far fa-lightbulb text-xs mr-1" /> Show solutions
                        </>
                    ) : (
                        'Hide solutions'
                    )}
                </a>
            </div>
            <div ref={solutionCard} className={`solution ${isHidingSolutions ? 'solution-hidden' : ''}`}>
                <div className="solution-main">
                    <div className="solution-background">
                        <svg
                            className="hidden absolute right-0 h-full | md:block"
                            x="0px"
                            y="0px"
                            viewBox="0 0 299 452"
                        >
                            <g style={{ opacity: 0.075 }}>
                                <polygon
                                    style={{ fill: 'rgb(63,63,63)' }}
                                    points="298.1,451.9 150.9,451.9 21,226.9 298.1,227.1"
                                />
                                <polygon
                                    style={{ fill: 'rgb(151,151,151)' }}
                                    points="298.1,227.1 21,226.9 150.9,1.9 298.1,1.9"
                                />
                            </g>
                        </svg>
                    </div>

                    <div className="p-12">
                        <div className="solution-content">
                            <h2 className="solution-title">{solution.title}</h2>

                            <div>
                                <ReactMarkdown
                                    source={solution.description || solution.action_description || ''}
                                    disallowedTypes={['image', 'imageReference', 'table', 'html']}
                                />
                            </div>

                            {Object.keys(solution.links).length > 0 && (
                                <div className="mt-8 grid justify-start">
                                    <div className="border-t-2 border-gray-700 opacity-25 " />
                                    <div className="pt-2 grid grid-cols-auto-1fr gap-x-4 gap-y-2 text-sm">
                                        <label className="font-semibold uppercase tracking-wider">Read more</label>
                                        <ul>
                                            {Object.keys(solution.links).map((label) => {
                                                return (
                                                    <li key={label}>
                                                        <a
                                                            href={solution.links[label]}
                                                            className="link-solution"
                                                            target="_blank"
                                                        >
                                                            {label}
                                                        </a>
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    </div>
                                </div>
                            )}
                        </div>
                        {flareErrorSolutions.length === 1 && (
                            <>
                                <Paginator
                                    page={currentSolutionIndex}
                                    lastPage={flareErrorSolutions.length}
                                    onChange={updateSolution}
                                    className="mt-6 -mb-6 text-sm"
                                />
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

type PaginatorProps = {
    page: number;
    lastPage: number;
    onChange?: (page: number) => void;
    className?: string;
    style?: React.CSSProperties;
};

function Paginator({ page, lastPage, onChange, ...props }: PaginatorProps) {
    return (
        <nav {...props}>
            <ul className={`grid grid-cols-auto grid-flow-col place-center gap-1`}>
                {createPageObjects(page, lastPage, onChange).map((pageObject, i) => (
                    <li key={i}>
                        {pageObject.value ? (
                            <a
                                className={`grid place-center h-8 min-w-8 px-2 rounded-full
                                    ${
                                        page == pageObject.value
                                            ? 'bg-tint-200 font-semibold'
                                            : 'hover:bg-tint-100 cursor-pointer'
                                    }
                                `}
                                onClick={pageObject.onClick}
                            >
                                {pageObject.label}
                            </a>
                        ) : (
                            <span className="text-tint-500">{pageObject.label}</span>
                        )}
                    </li>
                ))}
            </ul>
        </nav>
    );
}

type PageObject = {
    label: string | number;
    value: number | null;
    active: boolean;
    onClick: (() => void) | undefined;
};

function createPageObjects(page: number, lastPage: number, onClick?: (page: number) => void): Array<PageObject> {
    const linksOnEachSide = 2;

    if (lastPage <= 1) {
        return [];
    }

    // https://gist.github.com/kottenator/9d936eb3e4e3c3e02598#gistcomment-1748957
    let range = [];

    for (let i = Math.max(2, page - linksOnEachSide); i <= Math.min(lastPage - 1, page + linksOnEachSide); i++) {
        range.push(i);
    }

    if (page - linksOnEachSide > 2) {
        range.unshift('…');
    }

    if (page + linksOnEachSide < lastPage - 1) {
        range.push('…');
    }

    range.unshift(1);
    range.push(lastPage);

    return range.map((label) => {
        if (typeof label === 'string') {
            return { label, value: null, active: false, onClick: undefined };
        }

        return {
            label,
            value: label,
            active: label === page,
            onClick: onClick ? () => onClick(label) : undefined,
        };
    });
}
