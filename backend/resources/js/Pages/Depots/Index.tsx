import { FormEvent, useEffect, useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import Modal from "../../Components/Common/Modal";
import IncidentMap from "../../Components/Map/IncidentMap";
import DepotPickMap from "../../Components/Map/DepotPickMap";
import usePsgc from "../../Hooks/usePsgc";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { depotsService } from "@/api-service/depots";
import type { ColumnDef, CardActions } from "@/Components/table";
import type { Depot } from "@/Types/models";

const NO_INCIDENTS: never[] = [];

const emptyForm = {
  name: "",
  status: "OPERATIONAL" as Depot["status"],
  address: "",
  lat: null as number | null,
  lng: null as number | null,
  contact_name: "",
  contact_email: "",
  contact_phone: "",
};

const columns: ColumnDef<Depot>[] = [
  { key: "name", label: "Name" },
  { key: "address", label: "Address" },
  { key: "status", label: "Status" },
];

function DepotCard(row: Depot, actions: CardActions) {
  return (
    <div className="bg-[var(--bg-void)] p-5">
      <div className="mb-1 flex items-start justify-between">
        <div className="text-[15px] font-medium">{row.name}</div>
        <div className="flex gap-2 text-xs">
          {actions.canEdit && (
            <button onClick={actions.onEdit} className="underline text-[var(--text-secondary)] hover:text-[var(--amber)]">
              Edit
            </button>
          )}
          {actions.canDelete && (
            <button onClick={actions.onDelete} className="underline text-[var(--text-secondary)] hover:text-[var(--critical)]">
              Remove
            </button>
          )}
        </div>
      </div>
      <div className="mb-2.5 text-xs text-[var(--text-secondary)]">{row.address}</div>
      <span className="inline-block bg-[var(--ok-dim,rgba(63,169,140,0.15))] px-2 py-0.5 font-mono text-xs text-[var(--ok,#3FA98C)]">
        {row.status}
      </span>
    </div>
  );
}

function DepotsIndex() {
  const [mapDepots, setMapDepots] = useState<Depot[]>([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<Depot | null>(null);
  const [form, setForm] = useState(emptyForm);
  const [saving, setSaving] = useState(false);
  const psgc = usePsgc();
  const [region, setRegion] = useState("");
  const [province, setProvince] = useState("");

  const loadMap = () => {
    depotsService.getAll().then(setMapDepots);
  };

  useEffect(loadMap, []);

  const openCreate = () => {
    setEditing(null);
    setForm(emptyForm);
    setRegion("");
    setProvince("");
    setModalOpen(true);
    psgc.loadRegions();
  };

  const openEdit = (row: Depot) => {
    setEditing(row);
    setForm({
      name: row.name,
      status: row.status,
      address: row.address,
      lat: row.lat,
      lng: row.lng,
      contact_name: row.contact_name ?? "",
      contact_email: row.contact_email ?? "",
      contact_phone: row.contact_phone ?? "",
    });
    setModalOpen(true);
  };

  const submit = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      const payload = {
        name: form.name,
        status: form.status,
        address: form.address,
        lat: form.lat ?? 0,
        lng: form.lng ?? 0,
        contact_name: form.contact_name || undefined,
        contact_email: form.contact_email || undefined,
        contact_phone: form.contact_phone || undefined,
      };

      if (editing) {
        await depotsService.update(editing.id, payload);
      } else {
        await depotsService.create(payload);
      }

      setModalOpen(false);
      loadMap();
    } finally {
      setSaving(false);
    }
  };

  return (
    <div>
      <div className="h-[300px] border-b border-white/10">
        <IncidentMap incidents={NO_INCIDENTS} depots={mapDepots} />
      </div>

      <div className="flex items-center justify-between border-b border-white/10 px-6 py-3">
        <h1 className="text-lg font-semibold uppercase">Depots</h1>
        <Button onClick={openCreate}>+ Add depot</Button>
      </div>

      <div className="p-6">
        <DataTableCardField<Depot>
          apiUrl="/api/depots"
          apiQueryKey="depots"
          columns={columns}
          renderCard={DepotCard}
          onEditRow={openEdit}
          onDelete={() => loadMap()}
        />
      </div>

      <Modal
        open={modalOpen}
        title={editing ? "Edit depot" : "Add depot"}
        onClose={() => setModalOpen(false)}
        footer={
          <>
            <Button variant="ghost" onClick={() => setModalOpen(false)}>
              Cancel
            </Button>
            <Button onClick={submit} disabled={saving}>
              {saving ? "Saving…" : editing ? "Save changes" : "Add depot"}
            </Button>
          </>
        }
      >
        <form onSubmit={submit} className="flex flex-col gap-3">
          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">Depot name</label>
            <input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className="input" />
          </div>

          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">Status</label>
            <select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value as Depot["status"] })} className="input">
              <option value="OPERATIONAL">Operational</option>
              <option value="LIMITED CAPACITY">Limited capacity</option>
              <option value="OFFLINE">Offline</option>
            </select>
          </div>

          <div className="flex gap-3">
            <div className="flex-1">
              <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">Region</label>
              <select
                value={region}
                onChange={(e) => {
                  setRegion(e.target.value);
                  setProvince("");
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
            </div>
            <div className="flex-1">
              <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">Province</label>
              <select
                value={province}
                onChange={(e) => {
                  setProvince(e.target.value);
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
            </div>
          </div>

          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">Address</label>
            <input
              value={form.address}
              onChange={(e) => setForm({ ...form, address: e.target.value })}
              placeholder="e.g. Barangay Hall, City, Province"
              className="input"
            />
          </div>

          <DepotPickMap
            lat={form.lat}
            lng={form.lng}
            address={form.address}
            onChange={(lat, lng) => setForm((f) => ({ ...f, lat, lng }))}
          />

          <div className="flex gap-3">
            <div className="flex-1">
              <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">Contact name</label>
              <input value={form.contact_name} onChange={(e) => setForm({ ...form, contact_name: e.target.value })} className="input" />
            </div>
            <div className="flex-1">
              <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">Contact phone</label>
              <input value={form.contact_phone} onChange={(e) => setForm({ ...form, contact_phone: e.target.value })} className="input" />
            </div>
          </div>
          <div>
            <label className="mb-1 block text-xs uppercase tracking-wide text-[var(--text-secondary)]">Contact email</label>
            <input
              type="email"
              value={form.contact_email}
              onChange={(e) => setForm({ ...form, contact_email: e.target.value })}
              className="input"
            />
          </div>
        </form>
      </Modal>
    </div>
  );
}

DepotsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default DepotsIndex;
