import { ButtonHTMLAttributes } from "react";

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: "primary" | "ghost";
};

export default function Button({ variant = "primary", className = "", ...props }: Props) {
  const base = "inline-flex h-9 items-center justify-center gap-1.5 whitespace-nowrap px-4 text-sm font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-50";
  const styles =
    variant === "primary"
      ? "bg-amber text-[#1a1200] hover:brightness-110"
      : "border border-line-strong bg-transparent text-[var(--text-primary)] hover:bg-tint";

  return <button className={`${base} ${styles} ${className}`} {...props} />;
}
