import React, { useContext } from 'react';
import { igniteDataContext } from 'resources/js/ignition/igniteDataContext';
import FilePath from 'resources/js/shared/components/FilePath';
import { ErrorOccurrence } from 'resources/js/shared/types';

type Props = {
    errorOccurrence: ErrorOccurrence;
};

export default function CardHeader({ errorOccurrence }: Props) {
    const { telescopeUrl } = useContext(igniteDataContext);

    return (
        <div className="card-header">
            <div
                className="grid items-center rounded-t border-b border-tint-300 text-xs text-tint-600"
                style={{ gridTemplateColumns: '1fr 1fr' }}
            >
                <div className="grid cols-auto justify-start gap-2 px-4 py-2">
                    <div className="flex items-center">
                        <a
                            href="http://flareapp.io/docs/ignition-for-laravel/introduction"
                            target="_blank"
                            title="Ignition docs"
                        >
                            <svg className="w-4 h-5 mr-4" viewBox="0 0 428 988">
                                <polygon
                                    style={{ fill: '#FA4E79' }}
                                    points="428,247.1 428,494.1 214,617.5 214,369.3"
                                />
                                <polygon
                                    style={{ fill: '#FFF082' }}
                                    points="0,988 0,741 214,617.5 214,864.1"
                                />
                                <polygon
                                    style={{ fill: '#E6003A' }}
                                    points="214,123.9 214,617.5 0,494.1 0,0"
                                />
                                <polygon
                                    style={{ fill: '#FFE100' }}
                                    points="214,864.1 214,617.5 428,741 428,988"
                                />
                            </svg>
                        </a>
                        <FilePath path={errorOccurrence.application_path} />
                    </div>
                </div>
                <div className="grid cols-auto items-center justify-end gap-4 px-4 py-2">
                    {telescopeUrl && (
                        <div>
                            <a href={telescopeUrl} className="link-dimmed sm:ml-6" target="_blank">
                                Telescope
                            </a>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
