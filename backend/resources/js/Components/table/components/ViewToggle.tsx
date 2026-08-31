/**
 * @file components/table/components/ViewToggle.tsx
 * Small Table/Card segmented control for <DataTableCardField>. Only rendered
 * when `enableViewToggle` is set. Styling mirrors the existing segmented
 * controls (StatusFilter) so it introduces no new visual language.
 */

import { LayoutGrid, Table2 } from 'lucide-react';
import type { TableViewType } from '@/Types/reusable/data-table';

interface ViewToggleProps {
    value: TableViewType;
    onChange: (view: TableViewType) => void;
}

const BASE =
    'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium transition-colors';

export function ViewToggle({ value, onChange }: ViewToggleProps) {
    return (
        <div className="inline-flex items-center border border-line-strong p-0.5">
            <button
                type="button"
                onClick={() => onChange('table')}
                className={`${BASE} ${value === 'table' ? 'bg-amber text-[#1a1200]' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]'}`}
                aria-pressed={value === 'table'}
                title="Table view"
            >
                <Table2 className="size-4" strokeWidth={1.75} />
                Table
            </button>
            <button
                type="button"
                onClick={() => onChange('card')}
                className={`${BASE} ${value === 'card' ? 'bg-amber text-[#1a1200]' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]'}`}
                aria-pressed={value === 'card'}
                title="Card view"
            >
                <LayoutGrid className="size-4" strokeWidth={1.75} />
                Card
            </button>
        </div>
    );
}

export default ViewToggle;
