/**
 * @file components/table/components/RecordModalField.tsx
 * The dynamic input renderer for RecordModal. Split out of RecordModal so each
 * file stays focused (and under the line cap), and restyled onto the shared
 * form atoms (Field / inputCls / textareaCls) so the generic record modal looks
 * identical to CreateBatchModal.
 *
 * Supported types: text, number, email, password, date, datetime-local,
 *                  textarea, select, checkbox, async-select, file.
 */

import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import { Field, inputCls, textareaCls } from '@/Components/form/Field';
import { AsyncSelectField } from '@/Hooks/use-async-select-field';
import { FileUploadField } from '@/Hooks/use-file-upload-field';
import type { FileFieldValue } from '@/Types/reusable/fields';
import type { FieldDef } from '../types';
interface DynamicFieldProps<T> {
    field: FieldDef<T>;
    value: unknown;
    error?: string;
    disabled?: boolean;
    /** Preset label for async-select edit mode (resolved from the row). */
    initialLabel?: string;
    onChange: (value: unknown) => void;
}
export function DynamicField<T>({
    field,
    value,
    error,
    disabled,
    initialLabel,
    onChange,
}: DynamicFieldProps<T>) {
    const fieldDataCy =
        field.dataCy ?? `input-${field.key.replace(/_/g, '-')}`;

    if (field.type === 'file') {
        return (
            <Field
                label={field.label}
                required={field.required}
                helpText={field.helpText}
                error={error}
                data-cy={`${fieldDataCy}-field`}
            >
                <FileUploadField
                    value={
                        (value as FileFieldValue) ?? {
                            existing: [],
                            files: [],
                            removedIds: [],
                        }
                    }
                    onChange={onChange}
                    multiple={field.multiple}
                    accept={field.accept}
                    maxSizeMB={field.maxSizeMB}
                    maxFiles={field.maxFiles}
                    preview={field.preview}
                    disabled={disabled}
                    error={error}
                    data-cy={fieldDataCy}
                />
            </Field>
        );
    }

    if (field.type === 'async-select') {
        return (
            <Field
                label={field.label}
                required={field.required}
                error={error}
                data-cy="record-modal-field-field-field-label-2"
            >
                <AsyncSelectField
                    value={value}
                    onChange={onChange}
                    loadOptions={field.loadOptions!}
                    getOptionLabel={field.getOptionLabel}
                    initialLabel={initialLabel}
                    placeholder={field.placeholder}
                    debounceMs={field.debounceMs}
                    minSearchLength={field.minSearchLength}
                    disabled={disabled}
                    error={error}
                    data-cy="record-modal-field-async-select-field-field-placeholder"
                />
            </Field>
        );
    }

    if (field.type === 'checkbox') {
        return (
            <div data-cy="record-modal-field-div-checkbox-wrapper">
                <label
                    className="flex cursor-pointer items-center gap-2.5 border border-line-strong px-3 py-2.5"
                    data-cy="record-modal-field-label-5"
                >
                    <input
                        type="checkbox"
                        checked={Boolean(value)}
                        disabled={disabled}
                        onChange={(e) => onChange(e.target.checked)}
                        className="h-4 w-4 accent-amber"
                        data-cy="record-modal-field-input-checkbox"
                    />
                    <span
                        className="text-sm font-medium text-[var(--text-primary)]"
                        data-cy="record-modal-field-span-7"
                    >
                        {field.label}
                    </span>
                </label>
                {error && (
                    <p
                        className="mt-1 text-xs text-critical"
                        data-cy="record-modal-field-p-checkbox-error"
                    >
                        {error}
                    </p>
                )}
            </div>
        );
    }

    return (
        <Field
            label={field.label}
            required={field.required}
            helpText={field.helpText}
            error={error}
            data-cy={`${fieldDataCy}-field`}
        >
            <FieldControl
                field={field}
                value={value}
                disabled={disabled}
                onChange={onChange}
                dataCy={fieldDataCy}
            />
        </Field>
    );
}

/** The bare control for text-like / select / textarea fields (no label wrapper). */
function FieldControl<T>({
    field,
    value,
    disabled,
    onChange,
    dataCy,
}: {
    field: FieldDef<T>;
    value: unknown;
    disabled?: boolean;
    onChange: (value: unknown) => void;
    dataCy: string;
}) {
    if (field.type === 'textarea') {
        return (
            <textarea
                rows={3}
                value={(value as string) ?? ''}
                disabled={disabled}
                placeholder={field.placeholder}
                onChange={(e) => onChange(e.target.value)}
                className={textareaCls}
                data-cy={dataCy}
            />
        );
    }

    if (field.type === 'select') {
        return (
            <select
                value={(value as string | number) ?? ''}
                disabled={disabled}
                onChange={(e) => onChange(e.target.value)}
                className={inputCls}
                data-cy={dataCy}
            >
                <option
                    value=""
                    disabled
                    data-cy="record-modal-field-option-12"
                >
                    {field.placeholder ??
                        `Select ${field.label.toLowerCase()}…`}
                </option>
                {field.options?.map((opt) => (
                    <option
                        key={opt.value}
                        value={opt.value}
                        data-cy="record-modal-field-option-13"
                    >
                        {opt.label}
                    </option>
                ))}
            </select>
        );
    }

    if (field.type === 'password') {
        return <PasswordControl value={value} disabled={disabled} onChange={onChange} placeholder={field.placeholder} dataCy={dataCy} />;
    }

    return (
        <input
            type={field.type ?? 'text'}
            value={(value as string | number) ?? ''}
            disabled={disabled}
            placeholder={field.placeholder}
            onChange={(e) =>
                onChange(
                    field.type === 'number'
                        ? e.target.valueAsNumber
                        : e.target.value,
                )
            }
            className={inputCls}
            data-cy={dataCy}
        />
    );
}

/** Password input with a show/hide toggle — starts masked by default. */
function PasswordControl({
    value,
    disabled,
    placeholder,
    onChange,
    dataCy,
}: {
    value: unknown;
    disabled?: boolean;
    placeholder?: string;
    onChange: (value: unknown) => void;
    dataCy: string;
}) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="relative">
            <input
                type={visible ? 'text' : 'password'}
                value={(value as string) ?? ''}
                disabled={disabled}
                placeholder={placeholder}
                onChange={(e) => onChange(e.target.value)}
                className={`${inputCls} pr-9`}
                data-cy={dataCy}
            />
            <button
                type="button"
                onClick={() => setVisible((v) => !v)}
                tabIndex={-1}
                className="absolute inset-y-0 right-0 flex items-center px-2.5 text-[var(--text-secondary)] hover:text-[var(--text-primary)]"
                data-cy={`${dataCy}-toggle-visibility`}
            >
                {visible ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
            </button>
        </div>
    );
}
