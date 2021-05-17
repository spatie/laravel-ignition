import React, { useEffect, useRef, useState } from 'react';
import { highlightBlock } from '../util';

type Props = {
    children?: React.ReactNode;
    lang?: 'php';
    className?: string;
    codeClassName?: string;
};

export default function CopyableCode({
    children,
    lang,
    className = '',
    codeClassName = '',
}: Props) {
    const codeRef = useRef<HTMLElement | null>(null);
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        let timeout: number;

        if (copied) {
            timeout = window.setTimeout(() => setCopied(false), 3000);
        }

        return () => window.clearTimeout(timeout);
    }, [copied]);

    useEffect(() => {
        if (lang && codeRef.current) {
            highlightBlock(codeRef.current);
        }
    }, [lang, children]);

    function copy() {
        const el = document.createElement('textarea');
        el.value = codeRef.current ? codeRef.current.innerText : '';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);

        setCopied(true);
    }

    return (
        <div
            className={`group grid gap-2 justify-start items-center | sm:grid-flow-col ${className}`}
        >
            <div
                className={`code-inline ${codeClassName} transition-all pr-8`}
                style={{ color: 'inherit' }}
            >
                <pre>
                    <code className={lang || ''} ref={codeRef}>
                        {children}
                    </code>
                </pre>
                <button
                    className={`absolute top-0 right-0 mr-2 ${
                        copied
                            ? 'opacity-100'
                            : 'opacity-75 group-hover:opacity-100 group-hover:transition-opacity'
                    }`}
                    onClick={copy}
                    title="Copy to clipboard"
                >
                    <i
                        className={`${
                            copied ? 'fa fa-clipboard-check text-green-500' : 'far fa-clipboard'
                        }`}
                    />
                </button>
                {copied && (
                    <p
                        className="hidden z-10 shadow-md lg:block absolute top-0 right-0 px-2 py-1 -mt-1 ml-1 bg-white text-sm text-green-500 whitespace-nowrap"
                        onClick={() => setCopied(false)}
                    >
                        Copied!
                    </p>
                )}
            </div>
        </div>
    );
}
