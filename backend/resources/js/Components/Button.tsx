import { ButtonHTMLAttributes } from 'react';

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: 'primary' | 'ghost';
};

/** Generic button atom used by the reusable form/modal system. */
export function Button({ variant = 'primary', className = '', ...props }: Props) {
  const base = 'rounded-md px-4 py-2 text-sm font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-60';
  const styles =
    variant === 'primary'
      ? 'bg-[var(--amber,#f2a63c)] text-[#1a1200] hover:opacity-90'
      : 'border border-neutral-200 bg-transparent text-neutral-600 hover:bg-neutral-50';

  return <button className={`${base} ${styles} ${className}`} {...props} />;
}

export default Button;
