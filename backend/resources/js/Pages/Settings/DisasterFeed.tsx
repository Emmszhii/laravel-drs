import { FormEvent, useState } from "react";
import axios from "axios";
import { PageProps, DisasterFeedSettingsData } from "@/Types/models";
import Button from "../../Components/Common/Button";
import { SettingsTabs } from "@/Components/settings/SettingsTabs";
import AdminLayout from "../../Layouts/AdminLayout";

type Props = PageProps<{ settings: DisasterFeedSettingsData }>;

const INTERVAL_LABELS: Record<number, string> = {
  15: "Every 15 minutes",
  30: "Every 30 minutes",
  60: "Hourly",
  360: "Every 6 hours",
  1440: "Daily",
};

const STATUS_COLORS: Record<string, string> = {
  ok: "var(--ok)",
  partial: "var(--pending)",
  failed: "var(--critical)",
};

/**
 * Admin-only settings for the automated disaster/calamity feed — pulls
 * incidents from USGS (earthquakes) and GDACS (typhoons/floods/etc.) on a
 * schedule and dedups them into the Incidents list (see
 * App\Jobs\PollDisasterFeedsJob). Auto-resolve here applies to ANY
 * ACTIVE/MONITORING incident, not just feed-sourced ones.
 */
function DisasterFeedSettings({ settings: initial }: Props) {
  const [settings, setSettings] = useState(initial);
  const [saving, setSaving] = useState(false);
  const [running, setRunning] = useState(false);

  const save = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      const { data } = await axios.patch("/api/disaster-feed-settings", {
        usgs_enabled: settings.usgs_enabled,
        usgs_min_magnitude: settings.usgs_min_magnitude,
        gdacs_enabled: settings.gdacs_enabled,
        gdacs_min_alert_level: settings.gdacs_min_alert_level,
        poll_interval_minutes: settings.poll_interval_minutes,
        auto_resolve_enabled: settings.auto_resolve_enabled,
      });
      setSettings(data);
    } finally {
      setSaving(false);
    }
  };

  const refreshStatus = async () => {
    const { data } = await axios.get("/api/disaster-feed-settings");
    setSettings(data);
  };

  const runNow = async () => {
    setRunning(true);
    try {
      await axios.post("/api/disaster-feed-settings/run");
      setTimeout(refreshStatus, 3000);
    } finally {
      setRunning(false);
    }
  };

  return (
    <div>
      <SettingsTabs />
      <div className="mx-auto max-w-2xl p-8 text-[var(--text-primary)]">
        <h1 className="mb-1 text-lg font-semibold uppercase tracking-wide">Disaster feed</h1>
        <p className="mb-6 text-sm text-[var(--text-secondary)]">
          Automatically pull incidents from credible external sources (USGS earthquakes, GDACS
          typhoons/floods/etc.) and dedup them into the Incidents list on a schedule. New incidents
          are logged as ACTIVE — a coordinator still adds zones and reviews them like any other
          incident.
        </p>

        <div className="mb-6 border border-white/10 p-4">
          <div className="mb-2 flex items-center justify-between">
            <div className="text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              Last run
            </div>
            {settings.last_run_status && (
              <span
                className="px-2 py-0.5 text-xs font-semibold uppercase"
                style={{ color: STATUS_COLORS[settings.last_run_status] }}
              >
                {settings.last_run_status}
              </span>
            )}
          </div>
          <div className="mb-2 text-sm">
            {settings.last_run_at
              ? new Date(settings.last_run_at).toLocaleString()
              : "Never run yet"}
          </div>
          {settings.last_run_at && (
            <div className="mb-2 text-xs text-[var(--text-secondary)]">
              {settings.last_run_created_count} created · {settings.last_run_updated_count} updated
            </div>
          )}
          {settings.last_run_error && (
            <p className="mb-3 border border-[var(--critical)]/40 bg-[var(--critical)]/10 p-2 text-xs text-[var(--critical)]">
              {settings.last_run_error}
            </p>
          )}
          <div className="flex gap-2">
            <Button variant="ghost" type="button" onClick={runNow} disabled={running}>
              {running ? "Queuing…" : "Run now"}
            </Button>
            <Button variant="ghost" type="button" onClick={refreshStatus}>
              Refresh status
            </Button>
          </div>
          <p className="mt-2 text-xs text-[var(--text-secondary)]">
            "Run now" queues an immediate poll — the queue worker (php artisan queue:work) must be
            running for it to complete.
          </p>
        </div>

        <form onSubmit={save} className="space-y-4">
          <div className="border border-white/10 p-4">
            <label className="mb-2 flex items-center gap-2 text-sm font-semibold">
              <input
                type="checkbox"
                checked={settings.usgs_enabled}
                onChange={(e) => setSettings({ ...settings, usgs_enabled: e.target.checked })}
              />
              USGS Earthquake Hazards Program
            </label>
            <p className="mb-3 text-xs text-[var(--text-secondary)]">
              Free, keyless public feed of real-time earthquakes, filtered to the Philippines.
            </p>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              Minimum magnitude
            </label>
            <input
              type="number"
              step={0.1}
              min={0}
              max={10}
              value={settings.usgs_min_magnitude}
              onChange={(e) =>
                setSettings({ ...settings, usgs_min_magnitude: Number(e.target.value) })
              }
              className="w-full border border-white/20 bg-transparent p-2 text-sm"
            />
          </div>

          <div className="border border-white/10 p-4">
            <label className="mb-2 flex items-center gap-2 text-sm font-semibold">
              <input
                type="checkbox"
                checked={settings.gdacs_enabled}
                onChange={(e) => setSettings({ ...settings, gdacs_enabled: e.target.checked })}
              />
              GDACS (Global Disaster Alert &amp; Coordination System)
            </label>
            <p className="mb-3 text-xs text-[var(--text-secondary)]">
              Free, keyless multi-hazard feed — typhoons, floods, volcanoes, drought, wildfire.
            </p>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              Minimum alert level
            </label>
            <select
              value={settings.gdacs_min_alert_level}
              onChange={(e) =>
                setSettings({
                  ...settings,
                  gdacs_min_alert_level: e.target.value as DisasterFeedSettingsData["gdacs_min_alert_level"],
                })
              }
              className="input"
            >
              <option value="Green">Green (any alert)</option>
              <option value="Orange">Orange and above</option>
              <option value="Red">Red only</option>
            </select>
          </div>

          <div className="border border-white/10 p-4">
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
              Poll interval
            </label>
            <select
              value={settings.poll_interval_minutes}
              onChange={(e) =>
                setSettings({ ...settings, poll_interval_minutes: Number(e.target.value) })
              }
              className="input"
            >
              {settings.poll_interval_options.map((minutes) => (
                <option key={minutes} value={minutes}>
                  {INTERVAL_LABELS[minutes] ?? `Every ${minutes} minutes`}
                </option>
              ))}
            </select>
            <p className="mt-1 text-xs text-[var(--text-secondary)]">
              The scheduler checks every 5 minutes and polls once this interval has elapsed since
              the last run.
            </p>
          </div>

          <div className="border border-white/10 p-4">
            <label className="mb-1 flex items-center gap-2 text-sm font-semibold">
              <input
                type="checkbox"
                checked={settings.auto_resolve_enabled}
                onChange={(e) =>
                  setSettings({ ...settings, auto_resolve_enabled: e.target.checked })
                }
              />
              Auto-resolve stale incidents
            </label>
            <p className="text-xs text-[var(--text-secondary)]">
              Applies to <strong>any</strong> ACTIVE or MONITORING incident (feed-sourced or
              manually logged) with no open resource requests for{" "}
              <strong>{settings.auto_resolve_max_days} days</strong> and enough depot stock to
              cover any remaining demand. Change the window via DISASTER_FEED_AUTO_RESOLVE_MAX_DAYS
              in .env.
            </p>
          </div>

          <Button type="submit" disabled={saving}>
            {saving ? "Saving…" : "Save"}
          </Button>
        </form>
      </div>
    </div>
  );
}

DisasterFeedSettings.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default DisasterFeedSettings;
