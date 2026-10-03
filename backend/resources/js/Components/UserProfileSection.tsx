import { useEffect, useRef, useState } from "react";
import { router } from "@inertiajs/react";
import { LogOut, Moon, Sun } from "lucide-react";
import { useAuth } from "@/Hooks/use-auth";
import { useTheme } from "@/Hooks/use-theme";
import { ChangePasswordModal } from "@/Components/ChangePasswordModal";
import { listAll } from "@/Offline/outbox";
import { clearOfflinePages } from "@/Offline/pages";

interface UserProfileSectionProps {
  /** 'sidebar' = bottom-of-nav block (border above); 'header' = compact icon button + dropdown. Default 'sidebar'. */
  variant?: "sidebar" | "header";
}

/**
 * Bottom-of-sidebar (AdminLayout) / header-right (AppLayout) block: avatar
 * initials + name/email/role, click to change password, theme toggle.
 *
 * `variant="header"` renders as a single compact icon button (same footprint
 * as NotificationBell) that opens a dropdown, so it doesn't consume extra
 * header height on mobile. `variant="sidebar"` keeps the always-expanded
 * three-row block.
 */
export function UserProfileSection({
  variant = "sidebar",
}: UserProfileSectionProps) {
  const { user, displayName, email, roleLabel, initials } = useAuth();
  const { theme, toggle } = useTheme();
  const [passwordModalOpen, setPasswordModalOpen] = useState(false);
  const [open, setOpen] = useState(false);
  const wrapRef = useRef<HTMLDivElement>(null);

  // Unsynced entries stay on this device, owned by this user, and sync at their next sign-in;
  // the cached offline pages are deleted so the next person here can't open them.
  const logout = async () => {
    const queued = user ? (await listAll(user.id).catch(() => [])).length : 0;
    if (queued > 0 && !window.confirm(
      `${queued} stock entr${queued === 1 ? "y has" : "ies have"} not synced yet. They stay saved on this device and sync the next time you sign in here, but no one else can see them until then. Log out anyway?`,
    )) {
      return;
    }
    await clearOfflinePages();
    router.post("/logout");
  };

  useEffect(() => {
    if (!open) return;
    const onClickOutside = (e: MouseEvent) => {
      if (wrapRef.current && !wrapRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener("mousedown", onClickOutside);
    return () => document.removeEventListener("mousedown", onClickOutside);
  }, [open]);

  if (variant === "header") {
    return (
      <div className="relative" ref={wrapRef}>
        <button
          type="button"
          onClick={() => setOpen((o) => !o)}
          className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--amber)] text-xs font-semibold text-[var(--amber)] hover:bg-white/5"
          aria-label="Account menu"
          title={displayName}
        >
          {initials}
        </button>

        {open && (
          <div className="absolute top-[calc(100%+8px)] right-0 z-[4000] w-64 max-w-[calc(100vw-24px)] border border-white/20 bg-[var(--bg-panel-raised)] shadow-lg">
            <div className="flex items-center justify-between gap-2.5 border-b border-white/10 p-3">
              <span className="min-w-0 flex-1">
                <span className="block truncate text-sm font-medium text-[var(--text-primary)]">
                  {displayName}
                </span>
                <span className="block truncate text-xs text-[var(--text-secondary)]">
                  {email}
                </span>
                <span className="mt-0.5 block text-[10px] tracking-wide text-[var(--text-secondary)] uppercase">
                  {roleLabel}
                </span>
              </span>
              <button
                type="button"
                onClick={toggle}
                title={
                  theme === "dark"
                    ? "Switch to light mode"
                    : "Switch to dark mode"
                }
                aria-label="Toggle theme"
                className="shrink-0 rounded p-1.5 text-[var(--text-secondary)] hover:text-[var(--amber)]"
              >
                {theme === "dark" ? <Sun size={15} /> : <Moon size={15} />}
              </button>
            </div>
            <button
              type="button"
              onClick={() => {
                setOpen(false);
                setPasswordModalOpen(true);
              }}
              className="block w-full px-3 py-2.5 text-left text-xs hover:bg-white/5"
            >
              Change password
            </button>
            <button
              type="button"
              onClick={() => {
                setOpen(false);
                logout();
              }}
              className="flex w-full items-center gap-1.5 px-3 py-2.5 text-left text-xs text-[var(--text-secondary)] hover:bg-white/5 hover:text-critical"
            >
              <LogOut size={13} />
              Log out
            </button>
          </div>
        )}

        <ChangePasswordModal
          open={passwordModalOpen}
          onClose={() => setPasswordModalOpen(false)}
        />
      </div>
    );
  }

  return (
    <div className="relative border-t border-white/10 p-3">
      <button
        type="button"
        onClick={toggle}
        title={
          theme === "dark" ? "Switch to light mode" : "Switch to dark mode"
        }
        aria-label="Toggle theme"
        className=" rounded p-1 text-[var(--text-secondary)] hover:text-[var(--amber)] flex gap-2 w-full"
      >
        {theme === "dark" ? <Sun size={15} /> : <Moon size={15} />}
        <span className="text-xs">
          {theme === "dark" ? "Light mode" : "Dark mode"}
        </span>
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
          <span className="block truncate text-sm font-medium text-[var(--text-primary)]">
            {displayName}
          </span>
          <span className="block truncate text-xs text-[var(--text-secondary)]">
            {email}
          </span>
        </span>
      </button>
      <div className="mt-1 pl-1 text-[10px] tracking-wide text-[var(--text-secondary)] uppercase">
        {roleLabel}
      </div>

      <button
        type="button"
        onClick={logout}
        className="mt-2 flex w-full items-center gap-2 rounded p-1 text-left text-xs text-[var(--text-secondary)] hover:text-critical"
      >
        <LogOut size={15} />
        Log out
      </button>

      <ChangePasswordModal
        open={passwordModalOpen}
        onClose={() => setPasswordModalOpen(false)}
      />
    </div>
  );
}

export default UserProfileSection;
