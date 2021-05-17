import { useEffect } from 'react';

export default function useKeyboardShortcut(key: string, callback: (e: KeyboardEvent) => void) {
    useEffect(() => {
        function handleKeyPressed(e: KeyboardEvent) {
            if (document.activeElement) {
                if (document.activeElement.tagName === 'INPUT') {
                    return;
                }
            }

            if (e.key === key) {
                callback(e);
            }
        }

        window.addEventListener('keyup', handleKeyPressed);

        return () => {
            window.removeEventListener('keyup', handleKeyPressed);
        };
    }, [key, callback]);
}
