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
export function Dropdown({
  options,
  value,
  onChange,
  placeholder = "All",
}: DropdownProps) {
  return (
    <select
      value={value}
      onChange={(e) => onChange(e.target.value)}
      className="w-full border border-line-strong bg-[var(--bg-void)] px-2 py-2 text-sm text-[var(--text-primary)] outline-none focus:border-amber"
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
