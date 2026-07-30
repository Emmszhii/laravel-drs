import { ButtonHTMLAttributes } from "react";

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: "primary" | "ghost";
};

export default function Button({ variant = "primary", className = "", ...props }: Props) {
  const base = "px-4 py-2 text-sm font-semibold uppercase tracking-wide";
  const styles =
    variant === "primary"
      ? "bg-[var(--amber)] text-[#1a1200]"
      : "border border-white/20 text-[var(--text-secondary)] bg-transparent";

  return <button className={`${base} ${styles} ${className}`} {...props} />;
}
