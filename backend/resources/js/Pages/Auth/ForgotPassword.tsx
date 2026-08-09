import { FormEvent } from "react";
import { Link, useForm, usePage } from "@inertiajs/react";
import Button from "../../Components/Common/Button";

/**
 * Self-service "forgot password" request screen — emails a reset link via
 * the same Password broker PasswordResetController::create()/store() and
 * UserController::resetPassword() already use. Opts out of AppLayout like
 * Login.tsx, since this is unauthenticated.
 */
export default function ForgotPassword() {
  const { status } = usePage<{ status?: string }>().props;
  const { data, setData, post, processing, errors } = useForm({
    email: "",
  });

  const submit = (e: FormEvent) => {
    e.preventDefault();
    post("/forgot-password");
  };

  return (
    <div className="flex h-screen items-center justify-center bg-[var(--bg-void)] text-[var(--text-primary)]">
      <form onSubmit={submit} className="w-full max-w-sm space-y-4 p-8">
        <h1 className="text-lg font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </h1>
        <p className="text-sm text-[var(--text-secondary)]">
          Enter your account email and we&rsquo;ll send you a link to reset your password.
        </p>

        {status && <p className="text-xs text-[var(--ok)]">{status}</p>}

        <div className="space-y-1">
          <label className="block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
            Email
          </label>
          <input
            type="email"
            value={data.email}
            onChange={(e) => setData("email", e.target.value)}
            className="w-full border border-white/20 bg-transparent px-3 py-2 text-sm"
            autoComplete="username"
            required
          />
          {errors.email && <p className="text-xs text-critical">{errors.email}</p>}
        </div>

        <Button type="submit" disabled={processing} className="w-full">
          {processing ? "Sending…" : "Send reset link"}
        </Button>

        <Link
          href="/login"
          className="block text-center text-xs uppercase tracking-wide text-[var(--text-secondary)] underline hover:text-[var(--amber)]"
        >
          Back to sign in
        </Link>
      </form>
    </div>
  );
}

ForgotPassword.layout = (page: JSX.Element) => page;
