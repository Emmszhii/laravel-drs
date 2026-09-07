import { useQueryClient } from "@tanstack/react-query";
import AdminLayout from "../../Layouts/AdminLayout";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { useToast } from "@/Components/Toast";
import { ApiError } from "@/api-service/client";
import { requestsService } from "@/api-service/modules/requests";
import { usePermission } from "@/Hooks/use-permissions";
import type { CardActions, ColumnDef } from "@/Components/table";
import type { ResourceRequest } from "@/Types/models";

const statusColor: Record<ResourceRequest["status"], string> = {
  fulfilled: "text-ok",
  enroute: "text-pending",
  pending: "text-pending",
  unmet: "text-critical",
};

const approvalColor: Record<ResourceRequest["approval_status"], string> = {
  approved: "text-ok",
  rejected: "text-critical",
  pending: "text-pending",
};

const columns: ColumnDef<ResourceRequest>[] = [
  {
    key: "zone",
    label: "Area",
    render: (v) => (v as ResourceRequest["zone"])?.name ?? "—",
  },
  {
    key: "resource_type",
    label: "Resource",
    render: (v) => (v as ResourceRequest["resource_type"])?.name ?? "—",
  },
  { key: "quantity_requested", label: "Qty" },
  {
    key: "priority",
    label: "Priority",
    filterable: true,
    type: "select",
    typeData: [
      { value: "low", label: "Low" },
      { value: "medium", label: "Medium" },
      { value: "high", label: "High" },
      { value: "critical", label: "Critical" },
    ],
  },
  {
    key: "status",
    label: "Fulfillment",
    filterable: true,
    type: "select",
    typeData: [
      { value: "pending", label: "Pending" },
      { value: "enroute", label: "En route" },
      { value: "unmet", label: "Unmet" },
      { value: "fulfilled", label: "Fulfilled" },
    ],
  },
  {
    key: "approval_status",
    label: "Approval",
    filterable: true,
    type: "select",
    typeData: [
      { value: "pending", label: "Pending" },
      { value: "approved", label: "Approved" },
      { value: "rejected", label: "Rejected" },
    ],
  },
];

function makeRequestCard(handlers: {
  onApprove: (row: ResourceRequest) => void;
  onReject: (row: ResourceRequest) => void;
}) {
  return function RequestCard(row: ResourceRequest, _actions: CardActions) {
    return (
      <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
        <div>
          <div className="font-semibold">
            {row.zone?.disaster?.name ?? row.zone?.name ?? "Unknown area"}
          </div>
          <div className="text-xs text-[var(--text-secondary)]">
            {row.zone?.name} · {row.resource_type?.name ?? "—"} · {row.quantity_requested}{" "}
            {row.resource_type?.unit ?? ""} · Priority: {row.priority}
          </div>
          {row.approval_status === "rejected" && row.rejection_reason && (
            <div className="mt-1 text-xs text-critical">Reason: {row.rejection_reason}</div>
          )}
        </div>
        <div className="flex flex-col items-end gap-1.5">
          <div className="flex gap-3 text-xs">
            <span className={`font-semibold uppercase ${statusColor[row.status]}`}>{row.status}</span>
            <span className={`font-semibold uppercase ${approvalColor[row.approval_status]}`}>
              {row.approval_status}
            </span>
          </div>
          {row.approval_status === "pending" && (
            <div className="flex gap-3 text-xs">
              <button
                onClick={() => handlers.onApprove(row)}
                className="underline text-[var(--text-secondary)] hover:text-[var(--ok)]"
              >
                Approve
              </button>
              <button
                onClick={() => handlers.onReject(row)}
                className="underline text-[var(--text-secondary)] hover:text-[var(--critical)]"
              >
                Reject
              </button>
            </div>
          )}
        </div>
      </div>
    );
  };
}

function RequestsIndex() {
  const { showToast } = useToast();
  const { can } = usePermission();
  const canApprove = can("approve-requests");
  const queryClient = useQueryClient();

  const invalidateRequests = () =>
    tableListInvalidateKeys("requests").forEach((queryKey) =>
      queryClient.invalidateQueries({ queryKey }),
    );

  const renderCard = makeRequestCard({
    onApprove: async (row) => {
      try {
        await requestsService.approve(row.id);
        showToast("Request approved.");
        invalidateRequests();
      } catch (err) {
        showToast(err instanceof ApiError ? err.message : "Failed to approve request.", "error");
      }
    },
    onReject: async (row) => {
      const reason = window.prompt("Reason for rejecting this request:");
      if (!reason) return;
      try {
        await requestsService.reject(row.id, reason);
        showToast("Request rejected.");
        invalidateRequests();
      } catch (err) {
        showToast(err instanceof ApiError ? err.message : "Failed to reject request.", "error");
      }
    },
  });

  return (
    <div>
      <div className="flex items-center justify-between px-6 py-3">
        <h2 className="text-lg font-semibold uppercase">Resource requests</h2>
      </div>

      <div className="px-6 pb-6">
        <DataTableCardField<ResourceRequest>
          apiUrl="/api/requests"
          apiQueryKey="requests"
          columns={columns}
          renderCard={canApprove ? renderCard : undefined}
          enableEdit={false}
        />
      </div>
    </div>
  );
}

RequestsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default RequestsIndex;
