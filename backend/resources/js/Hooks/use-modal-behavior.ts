import { useEffect, useRef } from 'react';
import { useScrollLock } from './use-scroll-lock';

interface UseModalBehaviorOptions {
  show: boolean;
  onClose: () => void;
  closeOnEscape?: boolean;
  closeOnOverlayClick?: boolean;
}

/** Shared Escape-to-close + overlay-click-to-close + scroll-lock behavior for ModalCenter/ModalSide. */
export function useModalBehavior({ show, onClose, closeOnEscape = true, closeOnOverlayClick = true }: UseModalBehaviorOptions) {
  const overlayRef = useRef<HTMLDivElement>(null);

  useScrollLock(show);

  useEffect(() => {
    if (!show || !closeOnEscape) {
      return;
    }

    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') {
        onClose();
      }
    };

    document.addEventListener('keydown', onKeyDown);
    return () => document.removeEventListener('keydown', onKeyDown);
  }, [show, closeOnEscape, onClose]);

  const handleOverlayClick = (e: React.MouseEvent) => {
    if (closeOnOverlayClick && e.target === overlayRef.current) {
      onClose();
    }
  };

  return { overlayRef, handleOverlayClick };
}
