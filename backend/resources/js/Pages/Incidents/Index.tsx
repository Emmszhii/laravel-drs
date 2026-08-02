import { FormEvent, useEffect, useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import FilterBar from "../../Components/Common/FilterBar";
import Pagination from "../../Components/Common/Pagination";
import Modal from "../../Components/Common/Modal";
import Button from "../../Components/Common/Button";
import { ConfirmDeleteModal } from "@/Components/modal/ConfirmDeleteModal";
import TriggerSolver from "../Optimization/TriggerSolver";
import usePsgc from "../../Hooks/usePsgc";
import { ApiError } from "@/api-service/client";
import { incidentsService } from "@/api-service/modules/incidents";
import { PaginatedResponse } from "@/Types/pagination";
import { Disaster } from "@/Types/models";

const emptyForm = {
  name: "",
  type: "Typhoon",
  signal: "1",
  zone: "",
  lat: "",
  lng: "",
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

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setSaveError(null);

    if (!form.name.trim() || !form.zone.trim()) {
      setSaveError("Incident name and affected municipality are required.");
      return;
    }

    setSaving(true);
    try {
      await incidentsService.create({
        name: form.name,
        type: form.type as Disaster["type"],
        signal: Number(form.signal) as Disaster["signal"],
        zone: form.zone,
        lat: Number(form.lat),
        lng: Number(form.lng),
      });
      setModalOpen(false);
      load();
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

      <div className="flex flex-1 overflow-hidden">
        <div className="w-[280px] shrink-0 overflow-y-auto border-r border-white/10">
          {loading && incidents.length === 0
            ? Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="border-b border-white/10 p-3">
                  <div className="mb-2 h-3.5 w-3/4 animate-pulse rounded bg-white/10" />
                  <div className="h-3 w-1/2 animate-pulse rounded bg-white/5" />
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
                  <div className="font-semibold">{inc.name}</div>
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
              <table className="mb-6 w-full text-sm">
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
              <TriggerSolver disasterId={selected.id} />
            </>
          ) : (
            <p className="text-sm text-[var(--text-secondary)]">
              Select an incident to see details.
            </p>
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
          <div className="flex gap-3">
            <Field label="Latitude" className="flex-1">
              <input
                type="number"
                step="any"
                value={form.lat}
                onChange={(e) => setForm({ ...form, lat: e.target.value })}
                className="input"
              />
            </Field>
            <Field label="Longitude" className="flex-1">
              <input
                type="number"
                step="any"
                value={form.lng}
                onChange={(e) => setForm({ ...form, lng: e.target.value })}
                className="input"
              />
            </Field>
          </div>
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
