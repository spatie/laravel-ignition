import React from 'react';
import Stack from 'resources/js/shared/components/stack/components/Stack';
import { ErrorOccurrenceWithFrames } from 'resources/js/shared/types';

type Props = {
    errorOccurrence: ErrorOccurrenceWithFrames;
};

export default function StackTab({ errorOccurrence }: Props) {
    return (
        <Stack
            frames={errorOccurrence.frames}
            open_frame_index={errorOccurrence.open_frame_index}
        />
    );
}
