import { FormEvent } from "react";
import { useForm } from "@inertiajs/react";
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

  const submit = (e: FormEvent) => {
    e.preventDefault();
    post("/login");
  };

  return (
    <div className="flex h-screen items-center justify-center bg-[var(--bg-void)] text-[var(--text-primary)]">
      <form onSubmit={submit} className="w-full max-w-sm space-y-4 p-8">
        <h1 className="text-lg font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </h1>

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
          {errors.email && <p className="text-xs text-red-400">{errors.email}</p>}
        </div>

        <div className="space-y-1">
          <label className="block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
            Password
          </label>
          <input
            type="password"
            value={data.password}
            onChange={(e) => setData("password", e.target.value)}
            className="w-full border border-white/20 bg-transparent px-3 py-2 text-sm"
            autoComplete="current-password"
            required
          />
        </div>

        <Button type="submit" disabled={processing} className="w-full">
          {processing ? "Signing in…" : "Sign in"}
        </Button>
      </form>
    </div>
  );
}

Login.layout = (page: JSX.Element) => page;
