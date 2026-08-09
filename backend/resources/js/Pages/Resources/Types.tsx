import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { resourceTypesService } from "@/api-service/modules/resourceTypes";
import { usePermission } from "@/Hooks/use-permissions";
import { ResourcesTabs, TypeCard, typeColumns, typeFields } from "./shared";
import type { ResourceType } from "@/Types/models";

function ResourcesTypesIndex() {
  const [typeModal, setTypeModal] = useState<{
    mode: "create" | "edit";
    row?: ResourceType;
  } | null>(null);
  const { can } = usePermission();
  const canManage = can("manage-depots");

  return (
    <div>
      <div className="flex items-center justify-between border-b border-white/10 px-6">
        <ResourcesTabs />
        {canManage && (
          <Button
            className="my-2"
            onClick={() => setTypeModal({ mode: "create" })}
          >
            + Add type
          </Button>
        )}
      </div>

      <div className="p-6">
        <DataTableCardField<ResourceType>
          apiUrl="/api/resource-types"
          apiQueryKey="resource-types"
          columns={typeColumns}
          renderCard={TypeCard}
          editPermission="manage-depots"
          deletePermission="manage-depots"
          onEditRow={(row) => setTypeModal({ mode: "edit", row })}
        />
      </div>

      {typeModal && (
        <FormModal<ResourceType>
          open
          onClose={() => setTypeModal(null)}
          title={
            typeModal.mode === "create"
              ? "Add resource type"
              : "Edit resource type"
          }
          submitLabel="Save type"
          fields={typeFields}
          mode={typeModal.mode}
          row={typeModal.row}
          invalidateKeys={tableListInvalidateKeys("resource-types")}
          mutationFn={async (payload) =>
            typeModal.mode === "create"
              ? resourceTypesService.create(payload as Partial<ResourceType>)
              : resourceTypesService.update(
                  typeModal.row!.id,
                  payload as Partial<ResourceType>,
                )
          }
          onSuccess={() => setTypeModal(null)}
        />
      )}
    </div>
  );
}

ResourcesTypesIndex.layout = (page: JSX.Element) => (
  <AdminLayout>{page}</AdminLayout>
);

export default ResourcesTypesIndex;
