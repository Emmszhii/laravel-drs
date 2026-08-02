import { PropsWithChildren, ReactNode } from "react";

type Props = PropsWithChildren<{
  open: boolean;
  title: string;
  onClose: () => void;
  footer?: ReactNode;
}>;

export default function Modal({ open, title, onClose, footer, children }: Props) {
  if (!open) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      onClick={onClose}
    >
      <div
        className="flex max-h-[86vh] w-full max-w-md flex-col border border-white/20 bg-[var(--bg-panel)] border-t-2 border-t-[var(--amber)]"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="flex items-center justify-between border-b border-white/10 p-4">
          <h3 className="text-lg font-semibold">{title}</h3>
          <button onClick={onClose} className="text-xl leading-none text-[var(--text-secondary)] hover:text-[var(--amber)]">
            &times;
          </button>
        </div>
        <div className="flex flex-col gap-3 overflow-y-auto p-4">{children}</div>
        {footer && <div className="flex justify-end gap-2 border-t border-white/10 p-4">{footer}</div>}
      </div>
    </div>
  );
}
