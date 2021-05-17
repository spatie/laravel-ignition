export declare type ErrorOccurrenceWithFrames = ErrorOccurrence & {
    frames: Array<ErrorFrame>;
};

export declare type ErrorFrame = {
    class?: string;
    method: string;
    code_snippet: Record<string | number, string>;
    file: string;
    relative_file: string;
    line_number: number;
};

export declare type ErrorOccurrence = {
    id: number;
    error_id: number;
    occurrence_number: number;
    received_at: string;
    seen_at_url: string;
    exception_message: string;
    exception_class: string;
    application_path: string;
    application_version?: string;
    notifier_client_name: string;
    language_version?: string;
    framework_version?: string;
    open_frame_index?: number;
    stage: string;
    context_items: Record<string, Array<ContextItem>>;
    group_identifier: string;
    group_count: number;
    group_detail_query: string;
    first_frame_class: string;
    first_frame_method: string;
    group_first_seen_at?: string;
    group_last_seen_at?: string;
    glows: Array<ErrorGlow>;
    solutions: Array<ErrorSolution>;
    links: {
        show: string;
        share: string;
        group_details?: string;
    };
};
export declare type ContextItem = {
    group: string;
    name: string;
    value: any;
};

export declare type ErrorGlow = {
    id: number;
    received_at: string;
    name: string;
    microtime: number;
    message_level: string;
    meta_data: {};
};

export declare type ErrorSolution = {
    id: number;
    class: string;
    title: string;
    description: string;
    links: { [label: string]: string };
    action_description?: string;
    is_runnable: boolean;
};

export type IgnitionErrorOccurrence = {
    notifier: string;
    language: string;
    framework_version: string;
    language_version: string;
    exception_class: string;
    seen_at: number;
    message: string;
    glows: Array<{
        time: number;
        name: string;
        message_level: string;
        meta_data: any;
        microtime: number;
    }>;
    solutions: Array<ErrorSolution>;
    stacktrace: Array<{
        line_number: number;
        method: string;
        class: string;
        code_snippet: Record<number, string>;
        file: string;
        is_application_frame: boolean;
    }>;
    context: {
        request: {
            url: string;
            ip: string | null;
            method: string;
            useragent: string;
        };
        request_data: {
            queryString: Record<string, string>;
            body: Record<string, string>;
            files: Array<any>;
        };
        headers: Record<string, string>;
        cookies: Record<string, string>;
        session: Record<string, string>;
        route: {
            route: string | null;
            routeParameters: Record<string, any>;
            controllerAction: string;
            middleware: Array<string>;
        };
        user: Record<string, any>;
        env: {
            laravel_version: string;
            laravel_locale: string;
            laravel_config_cached: boolean;
            php_version: string;
        };
        logs: Array<{ message: string; level: string; context: any; microtime: number }>;
        dumps: Array<{
            html_dump: string;
            file: string;
            line_number: number;
            microtime: number;
        }>;
        queries: Array<{
            sql: string;
            time: number;
            connection_name: string;
            bindings: Array<any>;
            microtime: number;
        }>;
        git: {
            hash: string;
            message: string;
            tag: string;
            remote: string;
            isDirty: boolean;
        };
    };
    stage: string;
    message_level: null | string;
    open_frame_index: null | number;
    application_path: string;
    application_version: null | string;
};

export type FrameType = 'application' | 'vendor' | 'unknown';

export type StackFrameGroupType = {
    type: FrameType;
    relative_file: string;
    expanded: boolean;
    frames: Array<ErrorFrame & { frame_number: number; selected: boolean }>;
};

export type Tabname =
    | 'stackTraceTab'
    | 'requestTab'
    | 'appTab'
    | 'userTab'
    | 'contextTab'
    | 'debugTab';

export type SharePostData = {
    selectedTabNames: Array<Tabname>;
    lineSelection: string;
};

export type BaseDebugEvent = {
    microtime: number;
    label: string;
    context: { [key: string]: string };
};

export type QueryDebugEvent = BaseDebugEvent & {
    type: 'query';
    context: {
        [key: string]: {
            type: 'string' | 'int' | 'float' | 'bool' | 'null';
            value: string;
        };
    };
    replace_bindings: boolean;
    metadata: { time: string; connection_name: string };
};
export type DumpDebugEvent = BaseDebugEvent & {
    type: 'dump';
    metadata: { file: string; line_number: number };
};
export type LogDebugEvent = BaseDebugEvent & { type: 'log'; metadata: { level: string } };
export type GlowDebugEvent = BaseDebugEvent & {
    type: 'glow';
    metadata: { time: number; message_level: string };
};

export type DebugEventType = QueryDebugEvent | DumpDebugEvent | LogDebugEvent | GlowDebugEvent;
