/**
 * @file components/form-modal/FormModalBody.tsx
 * The Formik-driven form body rendered inside ModalCenter/ModalSide. Receives
 * its field config via the shell's `data` prop (kept out of Formik state so the
 * component stays stable) and reads/writes form values from Formik context.
 * Each field is rendered by the shared <DynamicField> (RecordModalField).
 */

import { useFormikContext } from 'formik';
import { Loader2 } from 'lucide-react';
import { useMemo } from 'react';
import type { ModalComponentProps } from '@/Components/modal/ModalCenter';
import { DynamicField } from '@/Components/table/components/RecordModalField';
import { isFieldDisabled, isFieldVisible } from '@/Components/table/utils';
import type { FormModalConfig } from './types';

type Values = Record<string, unknown>;

export function FormModalBody({
    data,
    close,
}: ModalComponentProps<FormModalConfig>) {
    const formik = useFormikContext<Values>();

    const visibleFields = useMemo(
        () =>
            (data?.fields ?? []).filter((f) =>
                isFieldVisible(f, data?.mode ?? 'create', data?.row),
            ),
        [data],
    );

    if (!data) {
        return null;
    }

    const {
        mode,
        row,
        submitLabel,
        cancelLabel,
        uploadProgress,
        submitButtonDataCy,
        closeButtonDataCy,
    } = data;

    return (
        <form
            onSubmit={formik.handleSubmit}
            // Yup owns validation. Without this, a native constraint (e.g. an
            // invalid `type="url"` value) blocks the submit event outright, so
            // Formik never runs and no styled error renders in the modal.
            noValidate
            className="flex min-h-0 flex-1 flex-col"
        >
            <div className="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                {visibleFields.map((f) => (
                    <div
                        key={f.key}
                        className={f.colSpan === 2 ? 'sm:col-span-2' : ''}
                    >
                        <DynamicField
                            field={f}
                            value={formik.values[f.key]}
                            error={
                                formik.touched[f.key] || formik.submitCount > 0
                                    ? (formik.errors[f.key] as
                                          string | undefined)
                                    : undefined
                            }
                            disabled={
                                formik.isSubmitting ||
                                isFieldDisabled(f, mode, row)
                            }
                            initialLabel={
                                mode === 'edit' && row
                                    ? f.initialLabel?.(row)
                                    : undefined
                            }
                            onChange={(v) => formik.setFieldValue(f.key, v)}
                        />
                    </div>
                ))}
            </div>

            {formik.status && (
                <p className="mx-6 border border-critical bg-[var(--bg-void)] px-3 py-2 text-xs text-critical">
                    {String(formik.status)}
                </p>
            )}

            {uploadProgress != null && (
                <div className="px-6 pb-2">
                    <div className="mb-1 flex items-center justify-between text-xs font-medium text-[var(--text-secondary)]">
                        <span>
                            {uploadProgress >= 100
                                ? 'Processing…'
                                : 'Uploading…'}
                        </span>
                        <span>{uploadProgress}%</span>
                    </div>
                    <div className="h-1.5 w-full overflow-hidden rounded-full bg-[var(--bg-void)]">
                        <div
                            className="h-full rounded-full bg-amber transition-[width] duration-200 ease-out"
                            style={{ width: `${uploadProgress}%` }}
                        />
                    </div>
                </div>
            )}

            <div className="mt-auto flex w-full items-center justify-end gap-2.5 border-t border-line px-6 py-3.5">
                <button
                    type="button"
                    onClick={close}
                    disabled={formik.isSubmitting}
                    className="border border-line-strong px-4 py-2 text-sm text-[var(--text-secondary)] transition-colors hover:border-[var(--text-secondary)] hover:text-[var(--text-primary)] disabled:opacity-60"
                    data-cy={closeButtonDataCy ?? 'close-button'}
                >
                    {cancelLabel}
                </button>
                <button
                    type="submit"
                    disabled={formik.isSubmitting}
                    className="inline-flex items-center justify-center gap-2 bg-amber px-4 py-2 text-sm font-semibold text-[#1a1200] transition-opacity hover:opacity-90 disabled:opacity-60"
                    data-cy={submitButtonDataCy ?? 'submit-button'}
                >
                    {formik.isSubmitting && (
                        <Loader2 className="h-4 w-4 animate-spin" />
                    )}
                    {submitLabel}
                </button>
            </div>
        </form>
    );
}

export default FormModalBody;
