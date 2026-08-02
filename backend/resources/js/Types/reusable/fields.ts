import type { ReactNode } from 'react';

export type ModalMode = 'create' | 'edit';

export type FieldType =
  | 'text'
  | 'number'
  | 'email'
  | 'password'
  | 'url'
  | 'date'
  | 'datetime-local'
  | 'textarea'
  | 'select'
  | 'checkbox'
  | 'async-select'
  | 'file';

export interface FieldOption {
  value: string | number;
  label: string;
}

/** A file already stored server-side (returned on an edit row). */
export interface ExistingFile {
  id: string | number;
  name: string;
  url: string;
  size?: number;
  type?: string;
}

/** Controlled value shape for a `type: 'file'` field. */
export interface FileFieldValue {
  existing: ExistingFile[];
  files: File[];
  removedIds: (string | number)[];
}

/** Declarative description of one form field, driving FormModal/RecordModal. */
export interface FieldDef<T = unknown> {
  key: string;
  label: string;
  type?: FieldType;
  required?: boolean;
  placeholder?: string;
  helpText?: string;
  defaultValue?: unknown;
  options?: FieldOption[];
  colSpan?: 1 | 2;
  dataCy?: string;
  /** Submit the value under a different payload key. */
  payloadKey?: string;
  /** Transform the raw form value before it's sent to the API. */
  transform?: (value: unknown) => unknown;
  /** Custom validator; return an error message, or falsy when valid. */
  validate?: (value: unknown, values: Record<string, unknown>) => string | undefined;
  showOnCreate?: boolean;
  showOnEdit?: boolean | ((row?: T) => boolean);
  disabled?: boolean | ((mode: ModalMode, row?: T) => boolean);

  // async-select
  loadOptions?: (query: string) => Promise<FieldOption[]>;
  getOptionLabel?: (value: unknown) => string;
  initialLabel?: (row: T) => string | undefined;
  debounceMs?: number;
  minSearchLength?: number;

  // file
  multiple?: boolean;
  accept?: string;
  maxSizeMB?: number;
  maxFiles?: number;
  preview?: boolean;

  children?: ReactNode;
}
