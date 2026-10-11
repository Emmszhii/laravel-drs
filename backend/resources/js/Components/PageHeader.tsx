import type { ReactNode } from "react";
import { Head } from "@inertiajs/react";

/** Every page's title row: sets the browser tab title too, so no page ships an untitled tab. */
export function PageHeader({ title, description, actions }: { title: string; description?: ReactNode; actions?: ReactNode }) {
  return (
    <div className="flex flex-wrap items-end justify-between gap-x-6 gap-y-3 px-4 pt-5 pb-4 sm:px-6">
      <Head title={title} />
      <div className="min-w-0">
        <h1 className="text-xl font-semibold">{title}</h1>
        {description && <p className="mt-1 max-w-3xl text-sm text-[var(--text-secondary)]">{description}</p>}
      </div>
      {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
    </div>
  );
}
