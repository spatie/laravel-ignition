import { useState } from 'react';

const cookieName = 'hide_solutions';

export default function useSolutions(): { isHidingSolutions: boolean; toggleHidingSolutions: () => void } {
    const [isHidingSolutions, setIsHidingSolutions] = useState(hasHideSolutionsCookie());

    function toggleHidingSolutions() {
        if (isHidingSolutions) {
            document.cookie = `${cookieName}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`;

            setIsHidingSolutions(false);
            return;
        }

        const expires = new Date();
        expires.setTime(expires.getTime() + 365 * 24 * 60 * 60 * 1000);

        document.cookie = `${cookieName}=true;expires=${expires.toUTCString()};path=/;`;

        setIsHidingSolutions(true);
    }

    function hasHideSolutionsCookie() {
        return document.cookie.includes(cookieName);
    }

    return { isHidingSolutions, toggleHidingSolutions };
}
