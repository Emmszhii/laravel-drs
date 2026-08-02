import { FormModal } from '@/Components/form-modal/FormModal';
import { useToast } from '@/Components/Toast';
import { apiFetchJson } from '@/lib/apiFetch';
import type { FieldDef } from '@/Components/table';

interface ChangePasswordValues {
  current_password: string;
  password: string;
  password_confirmation: string;
}

const fields: FieldDef<ChangePasswordValues>[] = [
  { key: 'current_password', label: 'Current password', type: 'password', required: true, colSpan: 2 },
  {
    key: 'password',
    label: 'New password',
    type: 'password',
    required: true,
    colSpan: 2,
    helpText: 'Minimum 8 characters.',
    validate: (value) => (typeof value === 'string' && value.length > 0 && value.length < 8 ? 'Must be at least 8 characters.' : undefined),
  },
  {
    key: 'password_confirmation',
    label: 'Confirm new password',
    type: 'password',
    required: true,
    colSpan: 2,
    validate: (value, values) => (value !== values.password ? 'Passwords do not match.' : undefined),
  },
];

interface ChangePasswordModalProps {
  open: boolean;
  onClose: () => void;
}

export function ChangePasswordModal({ open, onClose }: ChangePasswordModalProps) {
  const { showToast } = useToast();

  return (
    <FormModal<ChangePasswordValues>
      open={open}
      onClose={onClose}
      title="Change password"
      fields={fields}
      mode="create"
      submitLabel="Update password"
      mutationFn={async (payload) => {
        const res = await apiFetchJson<null>('/api/profile/password', {
          method: 'PUT',
          body: JSON.stringify(payload),
        });
        return res.data as unknown as ChangePasswordValues;
      }}
      onSuccess={() => {
        showToast('Password updated successfully.');
        onClose();
      }}
    />
  );
}

export default ChangePasswordModal;
