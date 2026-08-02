import { MoreVertical } from 'lucide-react';
import type { ComponentType } from 'react';
import { useEffect, useRef, useState } from 'react';

export interface RowMenuAction {
  label: string;
  icon?: ComponentType<{ className?: string }>;
  onClick: () => void;
  disabled?: boolean;
  danger?: boolean;
}

/** Kebab-menu of row actions (Edit / Archive / Restore / Delete), used by SettingsRow. */
export function RowMenu({ actions }: { actions: (RowMenuAction | null)[] }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  const visible = actions.filter((a): a is RowMenuAction => a !== null);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (!ref.current?.contains(e.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', onClick);
    return () => document.removeEventListener('mousedown', onClick);
  }, []);

  if (visible.length === 0) {
    return null;
  }

  return (
    <div ref={ref} className="relative">
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        aria-label="Row actions"
        className="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600"
      >
        <MoreVertical size={16} />
      </button>
      {open && (
        <div className="absolute right-0 z-20 mt-1 w-40 rounded-md border border-neutral-200 bg-white py-1 shadow-lg">
          {visible.map((action) => (
            <button
              key={action.label}
              type="button"
              disabled={action.disabled}
              onClick={() => {
                setOpen(false);
                action.onClick();
              }}
              className={`flex w-full items-center gap-2 px-3 py-2 text-left text-sm disabled:cursor-not-allowed disabled:opacity-50 ${
                action.danger ? 'text-rose-600 hover:bg-rose-50' : 'text-neutral-700 hover:bg-neutral-50'
              }`}
            >
              {action.icon && <action.icon className="h-4 w-4" />}
              {action.label}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

export default RowMenu;
