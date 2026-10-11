import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import { PageHeader } from "@/Components/PageHeader";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { resourceTypesService } from "@/api-service/modules/resourceTypes";
import { usePermission } from "@/Hooks/use-permissions";
import { TypeCard, typeColumns, typeFields } from "./shared";
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
      <PageHeader
        title="Items"
        description="The catalog the solver plans from. An item needs a unit cost before the solver can order it."
        actions={canManage && <Button onClick={() => setTypeModal({ mode: "create" })}>+ Add item</Button>}
      />

      <div className="px-4 pb-6 sm:px-6">
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
              ? "Add item"
              : "Edit item"
          }
          submitLabel="Save item"
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
