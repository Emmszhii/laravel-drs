import { FormEvent, useEffect, useState } from "react";
import Button from "@/Components/Common/Button";
import { ApiError, http } from "@/api-service/client";
import { resourceTypesService } from "@/api-service/modules/resourceTypes";
import { enqueue } from "@/Offline/outbox";
import type { ResourceType, Zone } from "@/Types/models";

type Props = { zones: Zone[] };

const PRIORITIES: { value: string; label: string }[] = [
  { value: "low", label: "Low" },
  { value: "medium", label: "Medium" },
  { value: "high", label: "High" },
  { value: "critical", label: "Critical" },
];

/**
 * Embedded per-incident "Request resources" form (mirrors how TriggerSolver
 * is embedded in Incidents/Index.tsx) — the only way to create a
 * ResourceRequest today. Offline-aware: a network failure queues the
 * request in IndexedDB (Offline/outbox.ts) instead of surfacing an error,
 * and it syncs automatically once connectivity returns (useOfflineSync).
 */
export default function CreateRequestForm({ zones }: Props) {
  const [resourceTypes, setResourceTypes] = useState<ResourceType[]>([]);
  const [zoneId, setZoneId] = useState(zones[0]?.id ?? "");
  const [resourceTypeId, setResourceTypeId] = useState("");
  const [quantity, setQuantity] = useState("");
  const [priority, setPriority] = useState("medium");
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    resourceTypesService.getAll().then(setResourceTypes);
  }, []);

  useEffect(() => {
    if (!zoneId && zones[0]) setZoneId(zones[0].id);
  }, [zones, zoneId]);

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setMessage(null);

    const qty = Number(quantity);
    if (!zoneId || !resourceTypeId || !qty || qty < 1) {
      setError("Select an area, resource type, and a quantity of at least 1.");
      return;
    }

    const payload = {
      zone_id: zoneId,
      resource_type_id: resourceTypeId,
      quantity_requested: qty,
      priority,
    };

    setSaving(true);
    try {
      await http.post("/api/requests", payload);
      setMessage("Request logged.");
      setQuantity("");
    } catch (err) {
      const isNetworkFailure = err instanceof ApiError && err.status === 0;
      if (isNetworkFailure || !navigator.onLine) {
        await enqueue("request", payload);
        setMessage("You're offline — request queued, will sync automatically.");
        setQuantity("");
      } else {
        setError(err instanceof ApiError ? err.message : "Failed to log request.");
      }
    } finally {
      setSaving(false);
    }
  };

  if (zones.length === 0) {
    return (
      <p className="mt-4 text-xs text-[var(--text-secondary)]">
        Add an affected area before logging resource requests.
      </p>
    );
  }

  return (
    <form onSubmit={submit} className="mt-4 border-t border-white/10 pt-4">
      <h3 className="mb-3 text-sm font-semibold uppercase tracking-wide">
        Request resources
      </h3>
      {error && <p className="mb-2 text-xs text-[var(--critical)]">{error}</p>}
      {message && <p className="mb-2 text-xs text-[var(--ok,#3FA98C)]">{message}</p>}
      <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <select
          value={zoneId}
          onChange={(e) => setZoneId(e.target.value)}
          className="border border-white/20 bg-transparent p-2 text-sm"
        >
          {zones.map((z) => (
            <option key={z.id} value={z.id}>
              {z.name}
            </option>
          ))}
        </select>
        <select
          value={resourceTypeId}
          onChange={(e) => setResourceTypeId(e.target.value)}
          className="border border-white/20 bg-transparent p-2 text-sm"
        >
          <option value="">Resource type…</option>
          {resourceTypes.map((rt) => (
            <option key={rt.id} value={rt.id}>
              {rt.name}
            </option>
          ))}
        </select>
        <input
          type="number"
          min={1}
          value={quantity}
          onChange={(e) => setQuantity(e.target.value)}
          placeholder="Qty"
          className="border border-white/20 bg-transparent p-2 text-sm"
        />
        <select
          value={priority}
          onChange={(e) => setPriority(e.target.value)}
          className="border border-white/20 bg-transparent p-2 text-sm"
        >
          {PRIORITIES.map((p) => (
            <option key={p.value} value={p.value}>
              {p.label}
            </option>
          ))}
        </select>
      </div>
      <Button type="submit" disabled={saving} className="mt-3">
        {saving ? "Saving…" : "+ Request resources"}
      </Button>
    </form>
  );
}
