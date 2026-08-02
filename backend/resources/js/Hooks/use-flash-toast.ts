import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import type { FlashToast } from '@/Types/reusable/ui';

export function useFlashToast(): void {
    useEffect(() => {
        const off = (router as unknown as { on: (event: string, cb: (event: Event) => void) => () => void }).on(
            'flash',
            (event: Event) => {
                const flash = (event as CustomEvent).detail?.flash;
                const data = flash?.toast as FlashToast | undefined;

                if (!data) {
                    return;
                }

                toast[data.type](data.message);
            },
        );

        return off;
    }, []);
}
