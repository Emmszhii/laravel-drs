import type { FieldOption } from './fields';

/** Props consumed by <AsyncSelectField> (see Hooks/use-async-select-field.tsx). */
export interface AsyncSelectFieldProps {
  value: unknown;
  onChange: (value: unknown) => void;
  loadOptions: (query: string) => Promise<FieldOption[]>;
  getOptionLabel?: (value: unknown) => string;
  initialLabel?: string;
  placeholder?: string;
  debounceMs?: number;
  minSearchLength?: number;
  disabled?: boolean;
  error?: string;
}

/** Props consumed by <AsyncMultiSelectField> (see use-async-multi-select-field.tsx). */
export interface AsyncMultiSelectFieldProps {
  value: unknown[];
  onChange: (value: unknown[]) => void;
  loadOptions: (query: string) => Promise<FieldOption[]>;
  getOptionLabel?: (value: unknown) => string;
  placeholder?: string;
  debounceMs?: number;
  minSearchLength?: number;
  disabled?: boolean;
  error?: string;
}
