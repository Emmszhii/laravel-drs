import { FormEvent, useState } from "react";
import { useForm } from "@inertiajs/react";
import { Eye, EyeOff } from "lucide-react";
import Button from "../../Components/Common/Button";

interface ResetPasswordProps {
  token: string;
  email: string;
}

/**
 * Reached via the emailed "set password" link (new-account invite or
 * forgot-password). Opts out of AppLayout like Login.tsx, since this is an
 * unauthenticated screen.
 */
export default function ResetPassword({ token, email }: ResetPasswordProps) {
  const { data, setData, post, processing, errors } = useForm({
    token,
    email,
    password: "",
    password_confirmation: "",
  });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);

  const submit = (e: FormEvent) => {
    e.preventDefault();
    post("/password/reset");
  };

  return (
    <div className="flex h-screen items-center justify-center bg-[var(--bg-void)] text-[var(--text-primary)]">
      <form onSubmit={submit} className="w-full max-w-sm space-y-4 p-8">
        <h1 className="text-lg font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </h1>
        <p className="text-sm text-[var(--text-secondary)]">
          Set a password for <span className="text-[var(--text-primary)]">{data.email}</span>.
        </p>

        <div className="space-y-1">
          <label className="block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
            New password
          </label>
          <div className="relative">
            <input
              type={showPassword ? "text" : "password"}
              value={data.password}
              onChange={(e) => setData("password", e.target.value)}
              className="w-full border border-white/20 bg-transparent px-3 py-2 pr-9 text-sm"
              autoComplete="new-password"
              required
            />
            <button
              type="button"
              onClick={() => setShowPassword((v) => !v)}
              tabIndex={-1}
              className="absolute inset-y-0 right-0 flex items-center px-2.5 text-[var(--text-secondary)] hover:text-[var(--text-primary)]"
            >
              {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
            </button>
          </div>
          {errors.password && (
            <p className="text-xs text-critical">{errors.password}</p>
          )}
        </div>

        <div className="space-y-1">
          <label className="block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
            Confirm password
          </label>
          <div className="relative">
            <input
              type={showConfirm ? "text" : "password"}
              value={data.password_confirmation}
              onChange={(e) => setData("password_confirmation", e.target.value)}
              className="w-full border border-white/20 bg-transparent px-3 py-2 pr-9 text-sm"
              autoComplete="new-password"
              required
            />
            <button
              type="button"
              onClick={() => setShowConfirm((v) => !v)}
              tabIndex={-1}
              className="absolute inset-y-0 right-0 flex items-center px-2.5 text-[var(--text-secondary)] hover:text-[var(--text-primary)]"
            >
              {showConfirm ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
            </button>
          </div>
        </div>

        {errors.email && <p className="text-xs text-critical">{errors.email}</p>}

        <Button type="submit" disabled={processing} className="w-full">
          {processing ? "Saving…" : "Set password"}
        </Button>
      </form>
    </div>
  );
}

ResetPassword.layout = (page: JSX.Element) => page;
