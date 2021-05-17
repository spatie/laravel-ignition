import { ErrorFrame, FrameType } from 'resources/js/shared/types';

export function addFrameNumbers(
    frames: Array<ErrorFrame>,
): Array<ErrorFrame & { frame_number: number }> {
    return frames.map((frame, i) => ({
        ...frame,
        frame_number: frames.length - i,
    }));
}

export function getFrameType(frame: ErrorFrame): FrameType {
    if (frame.relative_file.startsWith('vendor/')) {
        return 'vendor';
    }

    if (frame.relative_file === 'unknown') {
        return 'unknown';
    }

    return 'application';
}
