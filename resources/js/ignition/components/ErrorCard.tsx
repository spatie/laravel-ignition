import React from 'react';
import CardHeader from 'resources/js/ignition/components/CardHeader';
import FormattedExceptionMessage from 'resources/js/ignition/components/FormattedExceptionMessage';
import ExceptionClass from 'resources/js/shared/components/ExceptionClass';
import { ErrorOccurrence } from 'resources/js/shared/types';

type Props = {
    errorOccurrence: ErrorOccurrence;
};

export default function ErrorCard({ errorOccurrence }: Props) {
    return (
        <div className="card card-has-header block mb-12">
            <CardHeader errorOccurrence={errorOccurrence} />

            <div className="card-details">
                <div className="card-details-overflow scrollbar">
                    <div className="overflow-hidden text-2xl">
                        <div className="grid grid-cols-auto grid-flow-col gap-2 items-center justify-start">
                            <ExceptionClass name={errorOccurrence.exception_class} />
                        </div>

                        <FormattedExceptionMessage
                            className="mt-1"
                            message={errorOccurrence.exception_message}
                            exceptionClass={errorOccurrence.exception_class}
                        />
                    </div>

                    <div>
                        <a
                            className="ui-url"
                            href={errorOccurrence.seen_at_url}
                            target="_blank"
                            onClick={(e) => e.stopPropagation()}
                        >
                            {errorOccurrence.seen_at_url}
                        </a>
                    </div>
                </div>
            </div>

            {/* @todo should probably check if there is a runnable solution? */}
            {!!errorOccurrence.solutions.length && (
                <span className="card-solution" title="This error has a solution">
                    <i className="far fa-lightbulb text-base" />
                </span>
            )}
        </div>
    );
}
