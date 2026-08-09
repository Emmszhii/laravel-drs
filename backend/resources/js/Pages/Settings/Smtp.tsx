import { FormEvent, useState } from "react";
import axios from "axios";
import { PageProps, SmtpSettingsData } from "@/Types/models";
import Button from "../../Components/Common/Button";
import { SettingsTabs } from "@/Components/settings/SettingsTabs";
import AdminLayout from "../../Layouts/AdminLayout";

type Props = PageProps<{ settings: SmtpSettingsData }>;

/**
 * Admin-only settings for the optional custom outgoing mail server. When
 * left disabled (the default), the app keeps sending mail through the
 * server's built-in configuration exactly as before — this screen only
 * takes effect once "Use custom SMTP configuration" is turned on and a
 * host is saved (see App\Models\SmtpSetting::applyToMailer()).
 */
function SmtpSettings({ settings: initial, auth }: Props) {
  const [settings, setSettings] = useState(initial);
  const [isEnabled, setIsEnabled] = useState(initial.is_enabled);
  const [host, setHost] = useState(initial.host ?? "");
  const [port, setPort] = useState(initial.port?.toString() ?? "");
  const [username, setUsername] = useState(initial.username ?? "");
  const [password, setPassword] = useState("");
  const [encryption, setEncryption] = useState<"" | "tls" | "ssl">(initial.encryption ?? "");
  const [fromAddress, setFromAddress] = useState(initial.from_address ?? "");
  const [fromName, setFromName] = useState(initial.from_name ?? "");
  const [testTo, setTestTo] = useState(auth.user?.email ?? "");
  const [testing, setTesting] = useState(false);
  const [testResult, setTestResult] = useState<"ok" | "failed" | null>(null);
  const [saving, setSaving] = useState(false);

  const testConnection = async () => {
    setTesting(true);
    setTestResult(null);
    try {
      const { data } = await axios.post("/api/smtp-settings/test", {
        host,
        port: port ? Number(port) : null,
        username: username || null,
        password: password || undefined,
        encryption: encryption || null,
        from_address: fromAddress,
        from_name: fromName || null,
        to: testTo || undefined,
      });
      setTestResult(data.ok ? "ok" : "failed");
    } catch {
      setTestResult("failed");
    } finally {
      setTesting(false);
    }
  };

  const onSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      const { data } = await axios.patch("/api/smtp-settings", {
        is_enabled: isEnabled,
        host: host || null,
        port: port ? Number(port) : null,
        username: username || null,
        password: password || undefined,
        encryption: encryption || null,
        from_address: fromAddress || null,
        from_name: fromName || null,
      });
      setSettings(data);
      setPassword("");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div>
      <SettingsTabs />
      <div className="mx-auto max-w-2xl p-8 text-[var(--text-primary)]">
        <h1 className="mb-1 text-lg font-semibold uppercase tracking-wide">SMTP settings</h1>
        <p className="mb-6 text-sm text-[var(--text-secondary)]">
          Outgoing mail (password resets, new-account invites) uses the server's default mail
          configuration unless you enable a custom SMTP server below. If this is left off, or the
          host is blank, mail keeps going out through the existing default setup — nothing changes
          for anyone who doesn't touch this screen.
        </p>

        <div className="mb-6 border border-white/10 p-4">
          <div className="mb-1 text-xs uppercase tracking-wide text-[var(--text-secondary)]">
            Status
          </div>
          <div className="text-sm font-semibold">
            {settings.is_configured ? "Using custom SMTP server" : "Using default mail configuration"}
          </div>
        </div>

        <form onSubmit={onSubmit} className="space-y-4">
          <label className="flex items-center gap-2 text-sm">
            <input
              type="checkbox"
              checked={isEnabled}
              onChange={(e) => setIsEnabled(e.target.checked)}
            />
            Use custom SMTP configuration
          </label>

          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              Host
            </label>
            <input
              type="text"
              value={host}
              onChange={(e) => setHost(e.target.value)}
              placeholder="smtp.example.com"
              className="w-full border border-white/20 bg-transparent p-2 text-sm"
            />
          </div>

          <div className="flex gap-4">
            <div className="flex-1">
              <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
                Port
              </label>
              <input
                type="number"
                min={1}
                max={65535}
                value={port}
                onChange={(e) => setPort(e.target.value)}
                placeholder="587"
                className="w-full border border-white/20 bg-transparent p-2 text-sm"
              />
            </div>
            <div className="flex-1">
              <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
                Encryption
              </label>
              <select
                value={encryption}
                onChange={(e) => setEncryption(e.target.value as "" | "tls" | "ssl")}
                className="w-full border border-white/20 bg-transparent p-2 text-sm"
              >
                <option value="">None</option>
                <option value="tls">TLS (STARTTLS)</option>
                <option value="ssl">SSL</option>
              </select>
            </div>
          </div>

          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              Username
            </label>
            <input
              type="text"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              className="w-full border border-white/20 bg-transparent p-2 text-sm"
            />
          </div>

          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              Password {settings.password_set && "(a password is currently saved)"}
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder={settings.password_set ? "Leave blank to keep current password" : ""}
              className="w-full border border-white/20 bg-transparent p-2 text-sm"
            />
          </div>

          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              From address
            </label>
            <input
              type="email"
              value={fromAddress}
              onChange={(e) => setFromAddress(e.target.value)}
              placeholder="noreply@example.com"
              className="w-full border border-white/20 bg-transparent p-2 text-sm"
            />
          </div>

          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              From name
            </label>
            <input
              type="text"
              value={fromName}
              onChange={(e) => setFromName(e.target.value)}
              className="w-full border border-white/20 bg-transparent p-2 text-sm"
            />
          </div>

          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              Send test email to
            </label>
            <input
              type="email"
              value={testTo}
              onChange={(e) => setTestTo(e.target.value)}
              className="w-full border border-white/20 bg-transparent p-2 text-sm"
            />
          </div>

          {testResult && (
            <p className={testResult === "ok" ? "text-[var(--ok)]" : "text-[var(--critical)]"}>
              {testResult === "ok" ? "Test email sent successfully." : "Test email failed to send."}
            </p>
          )}

          {settings.last_test_status && (
            <p className="text-xs text-[var(--text-secondary)]">
              Last test: {settings.last_test_status === "ok" ? "succeeded" : "failed"}
              {settings.last_test_at && ` at ${settings.last_test_at}`}
            </p>
          )}

          <div className="flex flex-wrap gap-2">
            <Button
              variant="ghost"
              type="button"
              onClick={testConnection}
              disabled={testing || !host || !port || !fromAddress || !testTo}
            >
              {testing ? "Sending…" : "Send test email"}
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? "Saving…" : "Save"}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}

SmtpSettings.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default SmtpSettings;
