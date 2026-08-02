interface DropdownOption {
  value: string;
  label: string;
}

interface DropdownProps {
  options: DropdownOption[];
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
}

/** Plain native-select dropdown used by CardFilterPanel/ColumnFilterControl's "select" column filters. */
export function Dropdown({ options, value, onChange, placeholder = 'All' }: DropdownProps) {
  return (
    <select
      value={value}
      onChange={(e) => onChange(e.target.value)}
      className="w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-900/10"
    >
      <option value="">{placeholder}</option>
      {options.map((opt) => (
        <option key={opt.value} value={opt.value}>
          {opt.label}
        </option>
      ))}
    </select>
  );
}

export default Dropdown;
