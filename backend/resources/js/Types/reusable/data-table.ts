import type { ReactNode } from 'react';
import type { CardActions } from './card';
import type { FieldDef, FieldOption } from './fields';

export type { PaginationMeta } from './pagination';

export type TableViewType = 'table' | 'card';

/** Column-filter widget kind — distinct from FieldDef's form-field FieldType. */
export type ColumnFilterType = 'text' | 'select' | 'async-select' | 'async-multi-select' | 'date-range';

export interface ColumnDef<T> {
  key: string;
  label: string;
  filterable?: boolean;
  sortable?: boolean;
  render?: (value: unknown, row: T) => ReactNode;
  /** Column-filter widget kind (defaults to a plain text filter). */
  type?: ColumnFilterType;
  /** Static options for a `type: 'select'` filter. */
  typeData?: FieldOption[];
  /** Options loader for `type: 'async-select' | 'async-multi-select'` filters. */
  loadOptions?: (query: string) => Promise<FieldOption[]>;
  /** Other filter keys to clear when this filter changes. */
  filterResets?: string[];
}

export interface StatusFilterTab {
  value: string;
  label: string;
}

export interface InUseEntry {
  label: string;
  count: number;
}

export interface ModalState<T> {
  mode: 'edit';
  row?: T;
}

export interface RenderModalProps<T> {
  data: T | null;
  close: () => void;
}

export interface DataTableProps<T extends Record<string, unknown>> {
  apiUrl: string;
  apiQueryKey: string | string[];
  columns: ColumnDef<T>[];
  fields?: FieldDef<T>[];
  renderCard?: (row: T, actions: CardActions) => ReactNode;
  updateUrl?: (row: T) => string;
  updateMethod?: 'POST' | 'PUT' | 'PATCH';
  enableEdit?: boolean;
  restoreUrl?: (row: T) => string;
  archiveUrl?: (row: T) => string;
  deleteUrl?: (row: T) => string;
  onRestore?: (row: T, url: string) => Promise<void>;
  onArchive?: (row: T, url: string) => Promise<void>;
  onDelete?: (row: T, url: string) => Promise<void> | void;
  onRefreshRef?: (fn: () => void) => void;
  inUseCheck?: (row: T, action: string) => Promise<InUseEntry[]>;
  extraFilters?: Record<string, unknown>;
  defaultSortBy?: string;
  defaultSortDir?: 'asc' | 'desc';
  editPermission?: string;
  archivePermission?: string;
  deletePermission?: string;
  viewType?: TableViewType;
  paginationMode?: 'server' | 'client';
  onEditRow?: (row: T) => void;
  onFiltersChange?: (filters: Record<string, string | string[]>, search: string) => void;
  enableStatusFilter?: boolean;
  statusFilterOptions?: StatusFilterTab[];
  listHeader?: ReactNode;
  enableViewToggle?: boolean;
  children?: ReactNode;
  deleteConfirmText?: (row: T) => string | undefined;
}
