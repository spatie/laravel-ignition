import React, { useState, useRef, useEffect, lazy, Suspense } from 'react';
import { Paginator, useSolutions } from 'ui';
import usePageProps from 'app/hooks/usePageProps';

const ReactMarkdown = lazy(() => import('react-markdown'));

type Props = {
    flareErrorSolutions: FlareErrorSolution[];
    className?: string;
    inset?: boolean;
};

export default function SolutionCard({
    flareErrorSolutions,
    className = '',
    inset = false,
    ...props
}: Props) {
    const { isHidingSolutions, toggleHidingSolutions } = useSolutions();
    const [solution, setSolution] = useState(flareErrorSolutions[0]);
    const [currentSolutionIndex, setCurrentSolutionIndex] = useState(1);
    const solutionCard = useRef() as React.MutableRefObject<HTMLDivElement>;
    let animationTimeout: number;
    const { asset_url } = usePageProps();

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
            <div
                ref={solutionCard}
                className={`solution ${isHidingSolutions ? 'solution-hidden' : ''}`}
            >
                <div className="solution-main">
                    <div
                        className={`solution-background ${
                            inset ? 'solution-background-inset' : ''
                        }`}
                    >
                        <img
                            className="hidden h-full | md:block"
                            src={`${asset_url}images/solution-bg.svg`}
                        />
                    </div>

                    <div className="p-12">
                        <div className="solution-content">
                            <h2 className="solution-title">{solution.title}</h2>

                            <div>
                                <Suspense fallback={null}>
                                    <ReactMarkdown
                                        source={
                                            solution.description || solution.action_description!
                                        }
                                        disallowedTypes={[
                                            'image',
                                            'imageReference',
                                            'table',
                                            'html',
                                        ]}
                                    />
                                </Suspense>
                            </div>

                            {Object.keys(solution.links).length > 0 && (
                                <div className="mt-8 grid justify-start">
                                    <div className="border-t-2 border-gray-700 opacity-25 " />
                                    <div className="pt-2 grid grid-cols-auto-1fr gap-x-4 gap-y-2 text-sm">
                                        <label className="font-semibold uppercase tracking-wider">
                                            Read more
                                        </label>
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
                                    onChange={(page) => {
                                        updateSolution(page);
                                    }}
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
