import { PropsWithChildren, ReactNode } from "react";

type Props = PropsWithChildren<{
  rail?: ReactNode;
  statColumn?: ReactNode;
}>;

/**
 * Three-column EOC layout: incident rail | map | stats.
 * In the prototype these columns are user-resizable via drag handles —
 * reproduce with a library like `re-resizable` or a small custom hook.
 */
export default function DashboardLayout({ rail, children, statColumn }: Props) {
  return (
    <div className="flex h-full">
      <aside className="w-[220px] shrink-0 overflow-y-auto border-r border-white/10">{rail}</aside>
      <div className="flex-1 overflow-hidden">{children}</div>
      <aside className="w-[300px] shrink-0 overflow-y-auto border-l border-white/10">{statColumn}</aside>
    </div>
  );
}
