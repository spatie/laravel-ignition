import keyBy from 'lodash/keyBy';
import mapValues from 'lodash/mapValues';
import isString from 'lodash/isString';
import { ErrorOccurrence } from './types';

// @todo upgrade highlight.js and @types/highlight.js to version 10, and update typings (maybe see Ray)
// @ts-ignore
import hljs from 'highlight.js/lib/highlight';
// @ts-ignore
import hljsPhp from 'highlight.js/lib/languages/php';

hljs.registerLanguage('php', hljsPhp);
const { highlight, highlightBlock } = hljs;
export { highlight, highlightBlock };

export function getContextValues(
    errorOccurrence: ErrorOccurrence,
    group: string,
): { [name: string]: any } {
    return mapValues(keyBy(errorOccurrence.context_items[group] || [], 'name'), 'value');
}

export function stringifyOccurrenceData(value: any): string {
    if (value === undefined) {
        return 'undefined';
    }

    if (isString(value)) {
        try {
            value = JSON.parse(value);
        } catch (error) {}
    }

    return JSON.stringify(value, null, 4);
}
