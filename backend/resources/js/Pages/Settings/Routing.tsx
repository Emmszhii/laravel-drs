import { FormEvent, useState } from "react";
import axios from "axios";
import { Link } from "@inertiajs/react";
import { PageProps, RoutingSettingsData } from "@/Types/models";
import Button from "../../Components/Common/Button";
import AdminLayout from "../../Layouts/AdminLayout";

type Props = PageProps<{ settings: RoutingSettingsData }>;

function SettingsRoutingTabs() {
  return (
    <div className="flex gap-5 border-b border-white/10 px-6">
      <Link
        href="/settings/users"
        className="border-b-2 border-transparent py-2.5 text-sm font-semibold uppercase tracking-wide text-[var(--text-secondary)]"
      >
        Users
      </Link>
      <button className="border-b-2 border-[var(--amber)] py-2.5 text-sm font-semibold uppercase tracking-wide">
        Routing
      </button>
    </div>
  );
}

const FALLBACK_MESSAGES: Record<string, string> = {
  cap_exceeded: "Premium routing limit reached this month — using basic (OSRM) navigation.",
  provider_error: "Premium provider errored out — using basic (OSRM) navigation.",
};

/**
 * Admin-only settings for the optional paid routing provider. Free OSRM
 * navigation always works regardless of what's configured here — this
 * screen only controls the premium tier layered on top (see
 * App\Services\Directions\DirectionsRouter).
 */
function RoutingSettings({ settings: initial }: Props) {
  const [settings, setSettings] = useState(initial);
  const [baseUrl, setBaseUrl] = useState(initial.premium_base_url ?? "");
  const [apiKey, setApiKey] = useState("");
  const [monthlyCap, setMonthlyCap] = useState(initial.premium_monthly_cap?.toString() ?? "");
  const [testing, setTesting] = useState(false);
  const [testResult, setTestResult] = useState<"ok" | "failed" | null>(null);
  const [saving, setSaving] = useState(false);

  const testConnection = async () => {
    setTesting(true);
    setTestResult(null);
    try {
      const { data } = await axios.post("/api/routing-settings/test", {
        premium_base_url: baseUrl,
        premium_api_key: apiKey,
      });
      setTestResult(data.ok ? "ok" : "failed");
    } catch {
      setTestResult("failed");
    } finally {
      setTesting(false);
    }
  };

  const save = async (activeProvider: "osrm" | "premium") => {
    setSaving(true);
    try {
      const { data } = await axios.patch("/api/routing-settings", {
        active_provider: activeProvider,
        premium_base_url: baseUrl || null,
        premium_api_key: apiKey || undefined,
        premium_monthly_cap: monthlyCap ? Number(monthlyCap) : null,
      });
      setSettings(data);
      setApiKey("");
    } finally {
      setSaving(false);
    }
  };

  const onSubmit = (e: FormEvent) => {
    e.preventDefault();
    save("premium");
  };

  const usagePct =
    settings.premium_monthly_cap && settings.premium_monthly_cap > 0
      ? Math.min(100, Math.round((settings.usage_count / settings.premium_monthly_cap) * 100))
      : null;

  return (
    <div>
      <SettingsRoutingTabs />
      <div className="mx-auto max-w-2xl p-8 text-[var(--text-primary)]">
      <h1 className="mb-1 text-lg font-semibold uppercase tracking-wide">Routing settings</h1>
      <p className="mb-6 text-sm text-[var(--text-secondary)]">
        Basic navigation (self-hosted OSRM) is always free and always available. Configure an
        optional paid, Mapbox-Directions-API-compatible provider below for better instructions —
        it automatically falls back to basic navigation if the plan's usage cap is reached or the
        provider errors out.
      </p>

      <div className="mb-6 border border-white/10 p-4">
        <div className="mb-1 text-xs uppercase tracking-wide text-[var(--text-secondary)]">
          Active provider
        </div>
        <div className="mb-3 text-sm font-semibold">
          {settings.active_provider === "premium" ? "Premium" : "Basic (OSRM, free)"}
        </div>

        {settings.fallback_reason && (
          <p className="mb-3 border border-[var(--pending)]/40 bg-[var(--pending)]/10 p-2 text-xs text-[var(--pending)]">
            {FALLBACK_MESSAGES[settings.fallback_reason]}
          </p>
        )}

        {usagePct !== null && (
          <div>
            <div className="mb-1 flex justify-between text-xs text-[var(--text-secondary)]">
              <span>Usage this month</span>
              <span>
                {settings.usage_count} / {settings.premium_monthly_cap}
              </span>
            </div>
            <div className="h-2 w-full bg-white/10">
              <div
                className="h-2 bg-[var(--amber)]"
                style={{ width: `${usagePct}%` }}
              />
            </div>
          </div>
        )}
      </div>

      <form onSubmit={onSubmit} className="space-y-4">
        <div>
          <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
            Provider base URL
          </label>
          <input
            type="url"
            value={baseUrl}
            onChange={(e) => setBaseUrl(e.target.value)}
            placeholder="https://api.mapbox.com"
            className="w-full border border-white/20 bg-transparent p-2 text-sm"
          />
        </div>

        <div>
          <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
            API key {settings.premium_api_key_masked && `(current: ${settings.premium_api_key_masked})`}
          </label>
          <input
            type="password"
            value={apiKey}
            onChange={(e) => setApiKey(e.target.value)}
            placeholder={settings.premium_api_key_masked ? "Leave blank to keep current key" : ""}
            className="w-full border border-white/20 bg-transparent p-2 text-sm"
          />
        </div>

        <div>
          <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
            Monthly request cap
          </label>
          <input
            type="number"
            min={1}
            value={monthlyCap}
            onChange={(e) => setMonthlyCap(e.target.value)}
            placeholder="e.g. 100000"
            className="w-full border border-white/20 bg-transparent p-2 text-sm"
          />
        </div>

        {testResult && (
          <p className={testResult === "ok" ? "text-[var(--ok)]" : "text-[var(--critical)]"}>
            {testResult === "ok" ? "Connection successful." : "Connection failed."}
          </p>
        )}

        <div className="flex flex-wrap gap-2">
          <Button variant="ghost" type="button" onClick={testConnection} disabled={testing || !baseUrl || !apiKey}>
            {testing ? "Testing…" : "Test connection"}
          </Button>
          <Button type="submit" disabled={saving || !baseUrl}>
            {saving ? "Saving…" : "Activate premium"}
          </Button>
          <Button
            variant="ghost"
            type="button"
            disabled={saving}
            onClick={() => save("osrm")}
          >
            Use basic navigation only
          </Button>
        </div>
      </form>
      </div>
    </div>
  );
}

RoutingSettings.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default RoutingSettings;
