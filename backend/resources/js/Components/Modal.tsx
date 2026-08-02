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
        className="absolute top-1/2 left-1/2 z-50 max-h-[calc(100dvh-100px)] w-full -translate-x-1/2 -translate-y-1/2 overflow-auto rounded-md bg-white shadow-md"
        style={{ maxWidth, width: '92vw' }}
        role="dialog"
        aria-modal="true"
        aria-label={title}
      >
        {title && (
          <header className="flex items-start justify-between gap-3.5 border-b border-[#f1f2f5] px-6 pt-5.5 pb-4">
            <h2 className="text-[17px] font-extrabold tracking-[-0.01em] text-[#14151a]">{title}</h2>
            <button type="button" onClick={onClose} aria-label="Close" className="rounded-sm p-1 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600">
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
