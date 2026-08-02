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
    <div className="flex flex-col lg:h-full lg:flex-row lg:overflow-hidden">
      <aside className="max-h-[280px] w-full shrink-0 overflow-y-auto border-b border-white/10 lg:h-full lg:max-h-none lg:w-[220px] lg:border-r lg:border-b-0">
        {rail}
      </aside>
      <div className="min-h-[300px] flex-1 lg:overflow-hidden">{children}</div>
      <aside className="w-full shrink-0 border-t border-white/10 lg:h-full lg:w-[300px] lg:overflow-y-auto lg:border-t-0 lg:border-l">
        {statColumn}
      </aside>
    </div>
  );
}
