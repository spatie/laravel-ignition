import React from 'react';

type Props = {
    children: React.ReactNode;
    fallbackComponent: React.ReactNode;
};

type State = {
    hasError: boolean;
};

export default class ErrorBoundary extends React.Component<Props, State> {
    constructor(props: Props) {
        super(props);

        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error: Error) {
        if (window.flare && window.flare.report) {
            window.flare.report(error, { component: 'ErrorBoundary' });
        }

        return { hasError: true };
    }

    render() {
        return this.state.hasError ? this.props.fallbackComponent : this.props.children;
    }
}
