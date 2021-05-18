import { createContext } from 'react';
import { IgniteData } from 'resources/js/shared/types';

export const igniteDataContext = createContext<IgniteData>({} as any);
