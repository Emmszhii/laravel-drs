import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { reportsService } from "@/api-service/modules/reports";
import { incidentsService } from "@/api-service/modules/incidents";
import type { ColumnDef, CardActions, FieldDef } from "@/Components/table";
import type { Report } from "@/Types/models";

const columns: ColumnDef<Report>[] = [
  {
    key: "type",
    label: "Report",
    filterable: true,
    type: "select",
    typeData: [
      { value: "After-action summary", label: "After-action summary" },
      { value: "Resource utilization", label: "Resource utilization" },
      { value: "Response timeline", label: "Response timeline" },
      { value: "NDRRMC SitRep", label: "NDRRMC SitRep" },
    ],
  },
  {
    key: "disaster",
    label: "Incident",
    render: (v) => (v as Report["disaster"])?.name ?? "—",
  },
  { key: "generated_at", label: "Generated" },
];

function ReportCard(row: Report, actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="font-semibold">{row.type}</div>
        <div className="text-xs text-[var(--text-secondary)]">
          {row.disaster?.name ?? "—"} ·{" "}
          {new Date(row.generated_at).toLocaleString()}
        </div>
      </div>
      <div className="flex gap-3 text-xs">
        {row.type === "NDRRMC SitRep" ? (
          <>
            <a
              href={reportsService.downloadUrl(row.id, "pdf")}
              className="underline text-[var(--text-secondary)] hover:text-[var(--amber)]"
            >
              PDF
            </a>
            <a
              href={reportsService.downloadUrl(row.id, "csv")}
              className="underline text-[var(--text-secondary)] hover:text-[var(--amber)]"
            >
              CSV
            </a>
          </>
        ) : (
          <a
            href={reportsService.downloadUrl(row.id)}
            className="underline text-[var(--text-secondary)] hover:text-[var(--amber)]"
          >
            Download
          </a>
        )}
        {actions.canDelete && (
          <button
            onClick={actions.onDelete}
            className="underline text-[var(--text-secondary)] hover:text-[var(--critical)]"
          >
            Remove
          </button>
        )}
      </div>
    </div>
  );
}

const reportFields: FieldDef<Report>[] = [
  {
    key: "disaster_id",
    label: "Incident",
    type: "async-select",
    required: true,
    loadOptions: async (q: string) =>
      (await incidentsService.getAll())
        .filter((d) => d.name.toLowerCase().includes(q.toLowerCase()))
        .map((d) => ({ value: d.id, label: d.name })),
  },
  {
    key: "type",
    label: "Report type",
    type: "select",
    required: true,
    options: [
      { value: "After-action summary", label: "After-action summary" },
      { value: "Resource utilization", label: "Resource utilization" },
      { value: "Response timeline", label: "Response timeline" },
      { value: "NDRRMC SitRep", label: "NDRRMC SitRep" },
    ],
  },
];

function ReportsIndex() {
  const [modalOpen, setModalOpen] = useState(false);

  return (
    <div>
      <div className="flex items-center justify-between border-b border-white/10 px-6 py-3">
        <h1 className="text-lg font-semibold uppercase">Reports</h1>
        <Button onClick={() => setModalOpen(true)}>+ Generate report</Button>
      </div>

      <div className="p-6">
        <DataTableCardField<Report>
          apiUrl="/api/reports"
          apiQueryKey="reports"
          columns={columns}
          renderCard={ReportCard}
          enableEdit={false}
        />
      </div>

      <FormModal<Report>
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        title="Generate report"
        fields={reportFields}
        mode="create"
        invalidateKeys={tableListInvalidateKeys("reports")}
        mutationFn={async (payload) =>
          reportsService.create(
            payload as { disaster_id: string; type: Report["type"] },
          )
        }
        onSuccess={() => setModalOpen(false)}
      />
    </div>
  );
}

ReportsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default ReportsIndex;
