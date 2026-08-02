import { FormEvent, useEffect, useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import FilterBar from "../../Components/Common/FilterBar";
import Pagination from "../../Components/Common/Pagination";
import Modal from "../../Components/Common/Modal";
import Button from "../../Components/Common/Button";
import { ConfirmDeleteModal } from "@/Components/modal/ConfirmDeleteModal";
import TriggerSolver from "../Optimization/TriggerSolver";
import IncidentMap from "../../Components/Map/IncidentMap";
import LocationPickMap from "../../Components/Map/LocationPickMap";
import usePsgc from "../../Hooks/usePsgc";
import { ApiError } from "@/api-service/client";
import { incidentsService } from "@/api-service/modules/incidents";
import { depotsService } from "@/api-service/modules/depots";
import { PaginatedResponse } from "@/Types/pagination";
import { Disaster, Depot } from "@/Types/models";
import { reverseGeocode } from "@/Utils/geocoding";
import { matchPsgcLocation } from "@/Utils/psgcLookup";

const emptyForm = {
  name: "",
  type: "Typhoon",
  signal: "1",
  zone: "",
  lat: null as number | null,
  lng: null as number | null,
};

function IncidentsIndex() {
  const [page, setPage] = useState<PaginatedResponse<Disaster> | null>(null);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [signalFilter, setSignalFilter] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const [selected, setSelected] = useState<Disaster | null>(null);
  const [modalOpen, setModalOpen] = useState(false);
  const [form, setForm] = useState(emptyForm);
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Disaster | null>(null);
  const [deleting, setDeleting] = useState(false);
  const [depots, setDepots] = useState<Depot[]>([]);
  const [allIncidents, setAllIncidents] = useState<Disaster[]>([]);
  const psgc = usePsgc();
  const [region, setRegion] = useState("");
  const [province, setProvince] = useState("");
  const [city, setCity] = useState("");

  const MIN_LOADING_MS = 400;

  const load = () => {
    setLoading(true);
    const startedAt = Date.now();
    incidentsService
      .getPaginatedFilterSearch({
        search,
        filters: { signal: signalFilter, status: statusFilter },
        per_page: 10,
        page: currentPage,
      })
      .then(setPage)
      .finally(() => {
        const elapsed = Date.now() - startedAt;
        const remaining = Math.max(0, MIN_LOADING_MS - elapsed);
        setTimeout(() => setLoading(false), remaining);
      });
  };

  useEffect(load, [search, signalFilter, statusFilter, currentPage]);

  useEffect(() => {
    incidentsService.getAll().then(setAllIncidents);
    depotsService.getAll().then(setDepots);
  }, []);

  useEffect(() => {
    if (!selectedId) {
      setSelected(null);
      return;
    }
    incidentsService
      .getAll()
      .then((all) => setSelected(all.find((i) => i.id === selectedId) ?? null));
  }, [selectedId]);

  useEffect(() => {
    if (modalOpen) psgc.loadRegions();
  }, [modalOpen]);

  const openModal = () => {
    setForm(emptyForm);
    setRegion("");
    setProvince("");
    setCity("");
    setSaveError(null);
    psgc.reset();
    setModalOpen(true);
  };

  const applyPsgcMatch = async (municipality?: string, province?: string) => {
    const match = await matchPsgcLocation(municipality, province);
    if (!match) return;
    setRegion(match.regionCode);
    await psgc.loadProvinces(match.regionCode);
    if (match.provinceCode) {
      setProvince(match.provinceCode);
      await psgc.loadCities(match.provinceCode);
    } else {
      setProvince("");
    }
    setCity(match.cityCode);
  };

  const openModalAt = (lat: number, lng: number) => {
    setForm({ ...emptyForm, lat, lng });
    setRegion("");
    setProvince("");
    setCity("");
    setSaveError(null);
    psgc.reset();
    setModalOpen(true);
    reverseGeocode(lat, lng).then((resolved) => {
      if (resolved.display) setForm((f) => ({ ...f, zone: resolved.display! }));
      applyPsgcMatch(resolved.municipality, resolved.province);
    });
  };

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setSaveError(null);

    if (!form.name.trim() || !form.zone.trim()) {
      setSaveError("Incident name and affected municipality are required.");
      return;
    }

    if (form.lat == null || form.lng == null) {
      setSaveError("Pick a location on the map before saving.");
      return;
    }

    setSaving(true);
    try {
      await incidentsService.create({
        name: form.name,
        type: form.type as Disaster["type"],
        signal: Number(form.signal) as Disaster["signal"],
        zone: form.zone,
        lat: form.lat,
        lng: form.lng,
      });
      setModalOpen(false);
      load();
      incidentsService.getAll().then(setAllIncidents);
    } catch (err) {
      setSaveError(
        err instanceof ApiError
          ? err.message
          : "Failed to log incident. Please try again.",
      );
    } finally {
      setSaving(false);
    }
  };

  const remove = async () => {
    if (!deleteTarget) return;
    setDeleting(true);
    try {
      await incidentsService.delete(deleteTarget.id);
      if (selectedId === deleteTarget.id) setSelectedId(null);
      setDeleteTarget(null);
      load();
    } finally {
      setDeleting(false);
    }
  };

  const incidents = page?.data ?? [];
  const isInitialLoad = loading && !page;
  const isRefetching = loading && !!page;

  return (
    <div className="flex h-full flex-col">
      <div className="flex items-center justify-between border-b border-white/10 p-4">
        <h1 className="text-lg font-semibold uppercase">Incidents</h1>
        <Button onClick={openModal}>+ Log incident</Button>
      </div>
      <FilterBar
        search={search}
        onSearchChange={setSearch}
        placeholder="Search name or municipality…"
        count={page ? `${page.meta.total} total` : ""}
      >
        <select
          value={signalFilter}
          onChange={(e) => setSignalFilter(e.target.value)}
          className="border border-white/20 bg-transparent p-2 text-sm"
        >
          <option value="">All signals</option>
          {[1, 2, 3, 4, 5].map((s) => (
            <option key={s} value={s}>
              Signal {s}
            </option>
          ))}
        </select>
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="border border-white/20 bg-transparent p-2 text-sm"
        >
          <option value="">All statuses</option>
          <option value="ACTIVE">Active</option>
          <option value="MONITORING">Monitoring</option>
          <option value="RESOLVED">Resolved</option>
        </select>
      </FilterBar>

      <div className="flex flex-1 flex-col overflow-y-auto md:flex-row md:overflow-hidden">
        <div className="max-h-[260px] w-full shrink-0 overflow-y-auto border-b border-white/10 md:max-h-none md:w-[280px] md:border-r md:border-b-0">
          {isRefetching && (
            <div className="flex items-center gap-1.5 border-b border-white/10 px-3 py-1.5 text-[11px] text-[var(--text-secondary)]">
              <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-[var(--amber)]" />
              Updating…
            </div>
          )}
          {isInitialLoad
            ? Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="border-b border-white/10 p-3">
                  <div className="h-16 animate-pulse bg-white/5" />
                </div>
              ))
            : incidents.map((inc) => (
                <div
                  key={inc.id}
                  onClick={() => setSelectedId(inc.id)}
                  className={`cursor-pointer border-b border-white/10 p-3 text-sm ${
                    inc.id === selectedId
                      ? "border-l-2 border-l-[var(--amber)] bg-white/5"
                      : "hover:bg-white/5"
                  }`}
                >
                  <div className="flex items-center gap-1.5 font-semibold">
                    {inc.name}
                    {inc.source && (
                      <a
                        href={inc.source_url ?? undefined}
                        target="_blank"
                        rel="noreferrer"
                        onClick={(e) => e.stopPropagation()}
                        className="border border-white/20 px-1 py-0.5 text-[9px] font-normal uppercase tracking-wide text-[var(--text-secondary)] hover:text-[var(--amber)]"
                      >
                        {inc.source}
                      </a>
                    )}
                  </div>
                  <div className="text-xs text-[var(--text-secondary)]">
                    {inc.zone} · Signal {inc.signal} · {inc.status}
                  </div>
                </div>
              ))}
        </div>
        <div className="flex-1 overflow-y-auto p-6">
          {selected ? (
            <>
              <div className="mb-4 flex items-start justify-between">
                <div>
                  <h2 className="text-xl font-semibold">{selected.name}</h2>
                  <p className="text-sm text-[var(--text-secondary)]">
                    {selected.type} · {selected.zone} · Signal {selected.signal}{" "}
                    · {selected.status}
                  </p>
                </div>
                <button
                  onClick={() => setDeleteTarget(selected)}
                  className="text-xs text-[var(--text-secondary)] underline hover:text-[var(--critical)]"
                >
                  Remove
                </button>
              </div>
              <div className="mb-6 overflow-x-auto">
                <table className="w-full min-w-[420px] text-sm">
                  <thead>
                    <tr className="text-left text-xs uppercase text-[var(--text-secondary)]">
                      <th className="p-2">Area</th>
                      <th className="p-2">Population affected</th>
                      <th className="p-2">Severity</th>
                    </tr>
                  </thead>
                  <tbody>
                    {(selected.zones ?? []).map((z) => (
                      <tr key={z.id} className="border-t border-white/10">
                        <td className="p-2">{z.name}</td>
                        <td className="p-2">{z.population_affected}</td>
                        <td className="p-2">{z.severity_level}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              <TriggerSolver disasterId={selected.id} />
            </>
          ) : (
            <div className="flex h-full flex-col">
              <p className="mb-2 text-xs text-[var(--text-secondary)]">
                Select an incident on the left, or click the map to log a new
                incident at that location.
              </p>
              <div className="min-h-[320px] flex-1 relative z-10">
                <IncidentMap
                  incidents={allIncidents}
                  depots={depots}
                  onSelectIncident={setSelectedId}
                  onMapClick={openModalAt}
                />
              </div>
            </div>
          )}
        </div>
      </div>

      {page && (
        <Pagination
          currentPage={page.meta.current_page}
          lastPage={page.meta.last_page}
          total={page.meta.total}
          onPageChange={setCurrentPage}
        />
      )}

      <Modal
        open={modalOpen}
        title="Log incident"
        onClose={() => setModalOpen(false)}
        footer={
          <>
            <Button variant="ghost" onClick={() => setModalOpen(false)}>
              Cancel
            </Button>
            <Button onClick={submit} disabled={saving}>
              {saving ? "Saving…" : "Log incident"}
            </Button>
          </>
        }
      >
        <form onSubmit={submit} className="flex flex-col gap-3">
          {saveError && (
            <p className="border border-critical bg-[var(--bg-void)] px-3 py-2 text-xs text-critical">
              {saveError}
            </p>
          )}
          <Field label="Incident name">
            <input
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
              placeholder="e.g. Typhoon Marina"
              className="input"
            />
          </Field>
          <div className="flex gap-3">
            <Field label="Type" className="flex-1">
              <select
                value={form.type}
                onChange={(e) => setForm({ ...form, type: e.target.value })}
                className="input"
              >
                {["Typhoon", "Flood", "Earthquake", "Fire", "Landslide"].map(
                  (t) => (
                    <option key={t}>{t}</option>
                  ),
                )}
              </select>
            </Field>
            <Field label="Signal level" className="flex-1">
              <select
                value={form.signal}
                onChange={(e) => setForm({ ...form, signal: e.target.value })}
                className="input"
              >
                {[1, 2, 3, 4, 5].map((s) => (
                  <option key={s} value={s}>
                    Signal {s}
                  </option>
                ))}
              </select>
            </Field>
          </div>
          <Field label="Region">
            <select
              value={region}
              onChange={(e) => {
                setRegion(e.target.value);
                setProvince("");
                setCity("");
                psgc.loadProvinces(e.target.value);
              }}
              className="input"
            >
              <option value="">Select region…</option>
              {psgc.regions.map((r) => (
                <option key={r.code} value={r.code}>
                  {r.name}
                </option>
              ))}
            </select>
          </Field>
          <div className="flex gap-3">
            <Field label="Province" className="flex-1">
              <select
                value={province}
                onChange={(e) => {
                  setProvince(e.target.value);
                  setCity("");
                  psgc.loadCities(e.target.value);
                }}
                className="input"
              >
                <option value="">Select province…</option>
                {psgc.provinces.map((p) => (
                  <option key={p.code} value={p.code}>
                    {p.name}
                  </option>
                ))}
              </select>
            </Field>
            <Field label="City / Municipality" className="flex-1">
              <select
                value={city}
                onChange={(e) => {
                  const cityName =
                    psgc.cities.find((c) => c.code === e.target.value)?.name ??
                    "";
                  const provinceName =
                    psgc.provinces.find((p) => p.code === province)?.name ?? "";
                  setCity(e.target.value);
                  setForm({
                    ...form,
                    zone: provinceName
                      ? `${cityName}, ${provinceName}`
                      : cityName,
                  });
                }}
                className="input"
              >
                <option value="">Select city…</option>
                {psgc.cities.map((c) => (
                  <option key={c.code} value={c.code}>
                    {c.name}
                  </option>
                ))}
              </select>
            </Field>
          </div>
          <Field label="Affected municipality (editable)">
            <input
              value={form.zone}
              onChange={(e) => setForm({ ...form, zone: e.target.value })}
              placeholder="e.g. Legazpi City, Albay"
              className="input"
            />
          </Field>
          <Field label="Location">
            <LocationPickMap
              lat={form.lat}
              lng={form.lng}
              address={form.zone}
              onChange={(lat, lng) => setForm((f) => ({ ...f, lat, lng }))}
              onResolveAddress={(zone) => setForm((f) => ({ ...f, zone }))}
              onResolvePsgc={applyPsgcMatch}
            />
          </Field>
        </form>
      </Modal>

      <ConfirmDeleteModal
        open={!!deleteTarget}
        busy={deleting}
        label={deleteTarget?.name}
        onCancel={() => setDeleteTarget(null)}
        onConfirm={remove}
      />
    </div>
  );
}

function Field({
  label,
  children,
  className = "",
}: {
  label: string;
  children: React.ReactNode;
  className?: string;
}) {
  return (
    <div className={className}>
      <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">
        {label}
      </label>
      {children}
    </div>
  );
}

IncidentsIndex.layout = (page: JSX.Element) => (
  <AdminLayout>{page}</AdminLayout>
);

export default IncidentsIndex;
