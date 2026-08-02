import { X } from 'lucide-react';
import type { ReactNode } from 'react';
import { useScrollLock } from '@/Hooks/use-scroll-lock';

interface ModalProps {
  open: boolean;
  onClose: () => void;
  title?: string;
  maxWidth?: number;
  children: ReactNode;
}

/** Generic centered modal shell — the primitive RecordModal/ChangePasswordModal sit on. */
export function Modal({ open, onClose, title, maxWidth = 480, children }: ModalProps) {
  useScrollLock(open);

  if (!open) {
    return null;
  }

  return (
    <div className="fixed inset-0 z-30" role="presentation">
      <div className="absolute inset-0 z-40 bg-black/40" onClick={onClose} />
      <div
        className="absolute top-1/2 left-1/2 z-50 max-h-[calc(100dvh-100px)] w-full -translate-x-1/2 -translate-y-1/2 overflow-auto border-t-2 border-amber bg-bg-panel shadow-lg"
        style={{ maxWidth, width: '92vw' }}
        role="dialog"
        aria-modal="true"
        aria-label={title}
      >
        {title && (
          <header className="flex items-center justify-between gap-3.5 border-b border-line px-6 py-3.5">
            <h2 className="text-lg font-semibold uppercase tracking-wide text-[var(--text-primary)]">{title}</h2>
            <button type="button" onClick={onClose} aria-label="Close" className="p-1 text-[var(--text-secondary)] hover:text-amber">
              <X size={16} />
            </button>
          </header>
        )}
        <div className="p-6">{children}</div>
      </div>
    </div>
  );
}

export default Modal;
