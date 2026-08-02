import { useState } from 'react';
import { Moon, Sun } from 'lucide-react';
import { useAuth } from '@/Hooks/use-auth';
import { useTheme } from '@/Hooks/use-theme';
import { ChangePasswordModal } from '@/Components/ChangePasswordModal';

interface UserProfileSectionProps {
  /** 'sidebar' = bottom-of-nav block (border above); 'header' = compact, no border. Default 'sidebar'. */
  variant?: 'sidebar' | 'header';
}

/**
 * Bottom-of-sidebar (AdminLayout) / header-right (AppLayout) block: avatar
 * initials + name/email/role, click to change password, theme toggle in the
 * upper-right corner of the block.
 */
export function UserProfileSection({ variant = 'sidebar' }: UserProfileSectionProps) {
  const { displayName, email, role, initials } = useAuth();
  const { theme, toggle } = useTheme();
  const [passwordModalOpen, setPasswordModalOpen] = useState(false);

  return (
    <div className={`relative p-3 ${variant === 'sidebar' ? 'border-t border-white/10' : ''}`}>
      <button
        type="button"
        onClick={toggle}
        title={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
        aria-label="Toggle theme"
        className="absolute top-2 right-2 rounded p-1 text-[var(--text-secondary)] hover:text-[var(--amber)]"
      >
        {theme === 'dark' ? <Sun size={15} /> : <Moon size={15} />}
      </button>

      <button
        type="button"
        onClick={() => setPasswordModalOpen(true)}
        className="flex w-full items-center gap-2.5 rounded pr-7 pl-1 py-1 text-left hover:bg-white/5"
        title="Change password"
      >
        <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--amber)] text-xs font-semibold text-[var(--amber)]">
          {initials}
        </span>
        <span className="min-w-0 flex-1">
          <span className="block truncate text-sm font-medium text-[var(--text-primary)]">{displayName}</span>
          <span className="block truncate text-xs text-[var(--text-secondary)]">{email}</span>
        </span>
      </button>
      <div className="mt-1 pl-1 text-[10px] tracking-wide text-[var(--text-secondary)] uppercase">{role}</div>

      <ChangePasswordModal open={passwordModalOpen} onClose={() => setPasswordModalOpen(false)} />
    </div>
  );
}

export default UserProfileSection;
