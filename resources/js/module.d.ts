interface Window {
    flare?: typeof import('@flareapp/flare-client').flare;

    ignite: (data: {
        report: import('resources/js/shared/types').IgnitionErrorOccurrence;
        config: {
            editor: string;
            remoteSitesPath: string;
            localSitesPath: string;
            theme: 'light' | 'dark';
            enableShareButton: boolean;
            enableRunnableSolutions: boolean;
            directorySeparator: string;
        };
        solutions: Array<any>;
        telescopeUrl: string | null;
        shareEndpoint: string | null;
        defaultTab: string;
        defaultTabProps: Array<any> | {};
        appEnv: string;
        appDebug: boolean;
    }) => void;
}
