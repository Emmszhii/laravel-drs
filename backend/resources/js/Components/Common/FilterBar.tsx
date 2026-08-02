import { ReactNode } from "react";

type Props = {
  search: string;
  onSearchChange: (value: string) => void;
  placeholder?: string;
  count?: string;
  children?: ReactNode;
};

export default function FilterBar({ search, onSearchChange, placeholder, count, children }: Props) {
  return (
    <div className="flex flex-wrap items-center gap-2.5 border-b border-white/10 p-3">
      <input
        type="text"
        value={search}
        onChange={(e) => onSearchChange(e.target.value)}
        placeholder={placeholder ?? "Search…"}
        className="min-w-[180px] border border-white/20 bg-transparent p-2 text-sm"
      />
      {children}
      {count && <span className="ml-auto text-xs text-[var(--text-secondary)]">{count}</span>}
    </div>
  );
}
