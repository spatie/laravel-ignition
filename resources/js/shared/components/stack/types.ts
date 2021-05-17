import { ErrorFrame } from 'resources/js/shared/types';

export type State = {
    frames: Array<ErrorFrame>;
    selected: number;
    expanded: Array<number>;
};

export type Action =
    | { type: 'EXPAND_FRAMES'; frames: Array<number> }
    | { type: 'EXPAND_ALL_VENDOR_FRAMES' }
    | { type: 'COLLAPSE_ALL_VENDOR_FRAMES' }
    | { type: 'SELECT_FRAME'; frame: number }
    | { type: 'SELECT_NEXT_FRAME' }
    | { type: 'SELECT_PREVIOUS_FRAME' };
