import { FormEvent, useState } from "react";
import Modal from "@/Components/Common/Modal";
import Button from "@/Components/Common/Button";
import { http, ApiError } from "@/api-service/client";
import type { Zone } from "@/Types/models";

type Props = {
  disasterId: string;
  zone: Zone;
  onClose: () => void;
  onSaved: () => void;
};

const NUMERIC_FIELDS = [
  { key: "casualties_dead", label: "Dead" },
  { key: "casualties_injured", label: "Injured" },
  { key: "casualties_missing", label: "Missing" },
  { key: "families_affected", label: "Families affected" },
  { key: "houses_totally_damaged", label: "Houses totally damaged" },
  { key: "houses_partially_damaged", label: "Houses partially damaged" },
] as const;

/**
 * Embedded per-zone editor for NDRRMC/Sendai-standard indicator fields
 * (casualties, families/houses affected, damage cost) — backs the "Edit"
 * link on each affected-area row in Incidents/Index.tsx.
 */
export default function ZoneNdrrmcModal({ disasterId, zone, onClose, onSaved }: Props) {
  const [form, setForm] = useState({
    casualties_dead: zone.casualties_dead,
    casualties_injured: zone.casualties_injured,
    casualties_missing: zone.casualties_missing,
    families_affected: zone.families_affected,
    houses_totally_damaged: zone.houses_totally_damaged,
    houses_partially_damaged: zone.houses_partially_damaged,
    estimated_damage_cost: zone.estimated_damage_cost ?? "",
  });
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setError(null);
    try {
      await http.post(`/api/incidents/${disasterId}/areas/${zone.id}`, {
        ...form,
        estimated_damage_cost: form.estimated_damage_cost === "" ? null : Number(form.estimated_damage_cost),
      });
      onSaved();
      onClose();
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Failed to save.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal
      open
      title={`NDRRMC fields — ${zone.name}`}
      onClose={onClose}
      footer={
        <>
          <Button variant="ghost" onClick={onClose}>
            Cancel
          </Button>
          <Button onClick={submit} disabled={saving}>
            {saving ? "Saving…" : "Save"}
          </Button>
        </>
      }
    >
      {error && <p className="text-xs text-[var(--critical)]">{error}</p>}
      <div className="grid grid-cols-2 gap-3">
        {NUMERIC_FIELDS.map((f) => (
          <label key={f.key} className="text-xs uppercase text-[var(--text-secondary)]">
            {f.label}
            <input
              type="number"
              min={0}
              value={form[f.key]}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, [f.key]: Number(e.target.value) }))
              }
              className="mt-1 w-full border border-white/20 bg-transparent p-2 text-sm"
            />
          </label>
        ))}
        <label className="col-span-2 text-xs uppercase text-[var(--text-secondary)]">
          Estimated damage cost (PHP)
          <input
            type="number"
            min={0}
            value={form.estimated_damage_cost}
            onChange={(e) =>
              setForm((prev) => ({ ...prev, estimated_damage_cost: e.target.value }))
            }
            className="mt-1 w-full border border-white/20 bg-transparent p-2 text-sm"
          />
        </label>
      </div>
    </Modal>
  );
}
