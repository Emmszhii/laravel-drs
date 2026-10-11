import { FormEvent, useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { Eye, EyeOff } from "lucide-react";
import Button from "../../Components/Common/Button";

/**
 * Coordinator login. Opts out of AppLayout (see app.tsx) since the
 * brand bar / dashboard chrome doesn't apply to an unauthenticated screen.
 */
export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: "",
    password: "",
    remember: false,
  });
  const [showPassword, setShowPassword] = useState(false);

  const submit = (e: FormEvent) => {
    e.preventDefault();
    post("/login");
  };

  return (
    <div className="flex h-screen items-center justify-center bg-[var(--bg-void)] text-[var(--text-primary)]">
      <Head title="Sign in" />
      <form onSubmit={submit} className="panel mx-4 w-full max-w-sm space-y-4 p-8">
        <h1 className="text-lg font-semibold">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </h1>
        <p className="-mt-2 text-sm text-[var(--text-secondary)]">Supply readiness for CDRRMO San Pablo City</p>

        <div className="space-y-1">
          <label htmlFor="login-email" className="block text-xs text-[var(--text-secondary)]">
            Email
          </label>
          <input
            id="login-email"
            type="email"
            value={data.email}
            onChange={(e) => setData("email", e.target.value)}
            className="w-full border border-line-strong bg-transparent px-3 py-2 text-sm"
            autoComplete="username"
            required
          />
          {errors.email && <p className="text-xs text-critical">{errors.email}</p>}
        </div>

        <div className="space-y-1">
          <div className="flex items-center justify-between">
            <label htmlFor="login-password" className="block text-xs text-[var(--text-secondary)]">
              Password
            </label>
            <Link
              href="/forgot-password"
              className="text-xs text-[var(--text-secondary)] underline hover:text-[var(--amber)]"
            >
              Forgot password?
            </Link>
          </div>
          <div className="relative">
            <input
              id="login-password"
              type={showPassword ? "text" : "password"}
              value={data.password}
              onChange={(e) => setData("password", e.target.value)}
              className="w-full border border-line-strong bg-transparent px-3 py-2 pr-9 text-sm"
              autoComplete="current-password"
              required
            />
            <button
              type="button"
              onClick={() => setShowPassword((v) => !v)}
              tabIndex={-1}
              aria-label={showPassword ? "Hide password" : "Show password"}
              aria-pressed={showPassword}
              className="absolute inset-y-0 right-0 flex items-center px-2.5 text-[var(--text-secondary)] hover:text-[var(--text-primary)]"
            >
              {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
            </button>
          </div>
        </div>

        <Button type="submit" disabled={processing} className="w-full">
          {processing ? "Signing in…" : "Sign in"}
        </Button>
      </form>
    </div>
  );
}

Login.layout = (page: JSX.Element) => page;
