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
    <div className="flex h-full flex-col overflow-y-auto md:flex-row md:overflow-hidden">
      <aside className="max-h-[240px] w-full shrink-0 overflow-y-auto border-b border-white/10 md:h-full md:max-h-none md:w-[220px] md:border-r md:border-b-0">
        {rail}
      </aside>
      <div className="min-h-[300px] flex-1 overflow-hidden">{children}</div>
      <aside className="w-full shrink-0 overflow-y-auto border-t border-white/10 md:h-full md:w-[300px] md:border-t-0 md:border-l">
        {statColumn}
      </aside>
    </div>
  );
}
