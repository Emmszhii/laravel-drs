import { FormEvent, useEffect, useState } from "react";
import { usePage } from "@inertiajs/react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import Modal from "../../Components/Common/Modal";
import IncidentMap from "../../Components/Map/IncidentMap";
import useLiveGeolocation from "@/Hooks/useLiveGeolocation";
import useDispatchTracking from "@/Hooks/useDispatchTracking";
import { dispatchesService } from "@/api-service/modules/dispatches";
import { incidentsService } from "@/api-service/modules/incidents";
import { depotsService } from "@/api-service/modules/depots";
import { ApiError } from "@/api-service/client";
import type { Dispatch, Disaster, Depot } from "@/Types/models";

const STATUS_LABELS: Record<Dispatch["status"], string> = {
  pending: "Pending",
  en_route: "En route",
  delivered: "Delivered",
  cancelled: "Cancelled",
};

const emptyForm = {
  disaster_id: "",
  depot_id: "",
  driver_id: "",
  vehicle_label: "",
  destination_lat: null as number | null,
  destination_lng: null as number | null,
};

function DispatchIndex() {
  const { auth } = usePage().props as { auth: { user: { id: string; role: string } | null } };
  const [dispatchList, setDispatchList] = useState<Dispatch[]>([]);
  const [loading, setLoading] = useState(true);
  const [incidents, setIncidents] = useState<Disaster[]>([]);
  const [depots, setDepots] = useState<Depot[]>([]);
  const [drivers, setDrivers] = useState<{ id: string; name: string }[]>([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [form, setForm] = useState(emptyForm);
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [selectedDispatchId, setSelectedDispatchId] = useState<string | null>(null);

  const dispatches = useDispatchTracking(dispatchList);
  const { sharing, error: geoError, start, stop } = useLiveGeolocation(selectedDispatchId);

  const canCoordinate =
    auth.user && ["admin", "developer", "coordinator"].includes(auth.user.role);

  const load = () => {
    setLoading(true);
    dispatchesService.getAll().then((rows) => {
      setDispatchList(rows);
      setLoading(false);
    });
  };

  useEffect(load, []);

  useEffect(() => {
    if (!modalOpen) return;
    incidentsService.getAll().then(setIncidents);
    depotsService.getAll().then(setDepots);
    dispatchesService.getDrivers().then(setDrivers);
  }, [modalOpen]);

  const myActiveDispatch = dispatches.find(
    (d) => d.driver_id === auth.user?.id && (d.status === "pending" || d.status === "en_route"),
  );

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    if (
      !form.disaster_id ||
      !form.depot_id ||
      !form.driver_id ||
      form.destination_lat == null ||
      form.destination_lng == null
    ) {
      setSaveError("Please fill in all required fields.");
      return;
    }

    const depot = depots.find((d) => d.id === form.depot_id);
    if (!depot) return;

    setSaving(true);
    setSaveError(null);
    try {
      await dispatchesService.create({
        disaster_id: form.disaster_id,
        depot_id: form.depot_id,
        driver_id: form.driver_id,
        vehicle_label: form.vehicle_label || undefined,
        origin_lat: depot.lat,
        origin_lng: depot.lng,
        destination_lat: form.destination_lat,
        destination_lng: form.destination_lng,
      });
      setModalOpen(false);
      setForm(emptyForm);
      load();
    } catch (err) {
      setSaveError(err instanceof ApiError ? err.message : "Failed to log dispatch.");
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="flex h-full flex-col">
      <div className="flex items-center justify-between border-b border-white/10 p-4">
        <h1 className="text-lg font-semibold uppercase tracking-wide">Dispatch tracking</h1>
        {canCoordinate && (
          <Button onClick={() => setModalOpen(true)}>+ Log dispatch</Button>
        )}
      </div>

      <div className="flex flex-1 overflow-hidden">
        <div className="w-80 shrink-0 overflow-y-auto border-r border-white/10">
          {loading ? (
            <p className="p-4 text-sm text-[var(--text-secondary)]">Loading…</p>
          ) : dispatches.length === 0 ? (
            <p className="p-4 text-sm text-[var(--text-secondary)]">No dispatches yet.</p>
          ) : (
            dispatches.map((d) => {
              const isMine = d.driver_id === auth.user?.id;
              return (
                <button
                  key={d.id}
                  onClick={() => setSelectedDispatchId(d.id)}
                  className={`block w-full border-b border-white/10 p-3 text-left text-sm ${
                    selectedDispatchId === d.id ? "bg-white/5" : ""
                  }`}
                >
                  <div className="font-medium">{d.vehicle_label ?? d.depot?.name ?? "Dispatch"}</div>
                  <div className="text-xs text-[var(--text-secondary)]">
                    {STATUS_LABELS[d.status]}
                    {d.driver?.name ? ` · ${d.driver.name}` : ""}
                    {isMine ? " · You" : ""}
                  </div>
                </button>
              );
            })
          )}

          {myActiveDispatch && myActiveDispatch.id === selectedDispatchId && (
            <div className="border-t border-white/10 p-4">
              <Button
                variant={sharing ? "ghost" : "primary"}
                onClick={() => (sharing ? stop() : start())}
              >
                {sharing ? "Stop sharing location" : "Share my location"}
              </Button>
              {geoError && (
                <p className="mt-2 text-xs text-[var(--critical)]">{geoError}</p>
              )}
            </div>
          )}
        </div>

        <div className="flex-1">
          <IncidentMap
            incidents={[]}
            depots={depots.length ? depots : dispatches.map((d) => d.depot).filter(Boolean) as Depot[]}
            dispatches={dispatches.map((d) => ({
              id: d.id,
              vehicle_label: d.vehicle_label,
              status: d.status,
              current_lat: d.current_lat,
              current_lng: d.current_lng,
            }))}
          />
        </div>
      </div>

      <Modal
        open={modalOpen}
        title="Log dispatch"
        onClose={() => setModalOpen(false)}
        footer={
          <>
            <Button variant="ghost" onClick={() => setModalOpen(false)}>
              Cancel
            </Button>
            <Button onClick={submit} disabled={saving}>
              {saving ? "Saving…" : "Log dispatch"}
            </Button>
          </>
        }
      >
        {saveError && <p className="text-sm text-[var(--critical)]">{saveError}</p>}
        <label className="text-xs uppercase text-[var(--text-secondary)]">
          Incident
          <select
            className="mt-1 w-full border border-white/20 bg-transparent p-2 text-sm"
            value={form.disaster_id}
            onChange={(e) => {
              const incident = incidents.find((i) => i.id === e.target.value);
              setForm((f) => ({
                ...f,
                disaster_id: e.target.value,
                destination_lat: incident?.lat ?? f.destination_lat,
                destination_lng: incident?.lng ?? f.destination_lng,
              }));
            }}
          >
            <option value="">Select an incident…</option>
            {incidents.map((i) => (
              <option key={i.id} value={i.id}>
                {i.name}
              </option>
            ))}
          </select>
        </label>
        <label className="text-xs uppercase text-[var(--text-secondary)]">
          Origin depot
          <select
            className="mt-1 w-full border border-white/20 bg-transparent p-2 text-sm"
            value={form.depot_id}
            onChange={(e) => setForm((f) => ({ ...f, depot_id: e.target.value }))}
          >
            <option value="">Select a depot…</option>
            {depots.map((d) => (
              <option key={d.id} value={d.id}>
                {d.name}
              </option>
            ))}
          </select>
        </label>
        <label className="text-xs uppercase text-[var(--text-secondary)]">
          Driver
          <select
            className="mt-1 w-full border border-white/20 bg-transparent p-2 text-sm"
            value={form.driver_id}
            onChange={(e) => setForm((f) => ({ ...f, driver_id: e.target.value }))}
          >
            <option value="">Select a driver…</option>
            {drivers.map((u) => (
              <option key={u.id} value={u.id}>
                {u.name}
              </option>
            ))}
          </select>
        </label>
        <label className="text-xs uppercase text-[var(--text-secondary)]">
          Vehicle label (optional)
          <input
            className="mt-1 w-full border border-white/20 bg-transparent p-2 text-sm"
            value={form.vehicle_label}
            onChange={(e) => setForm((f) => ({ ...f, vehicle_label: e.target.value }))}
            placeholder="e.g. Truck 3"
          />
        </label>
        {(() => {
          const incident = incidents.find((i) => i.id === form.disaster_id);
          if (!incident) return null;
          return (
            <p className="text-xs text-[var(--text-secondary)]">
              Destination defaults to the incident's location ({incident.zone}). Selecting a
              depot pre-fills origin coordinates from it.
            </p>
          );
        })()}
        <input type="hidden" value={form.destination_lat ?? ""} readOnly />
      </Modal>
    </div>
  );
}

DispatchIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default DispatchIndex;
