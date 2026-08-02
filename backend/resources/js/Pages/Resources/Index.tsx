import { useState } from "react";
import AdminLayout from "../../Layouts/AdminLayout";
import Button from "../../Components/Common/Button";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import { FormModal } from "@/Components/form-modal/FormModal";
import { tableListInvalidateKeys } from "@/Components/table/utils";
import { resourcesService } from "@/api-service/resources";
import { ResourcesTabs, StockCard, stockColumns, stockFields } from "./shared";
import type { ResourceStock } from "@/Types/models";

function ResourcesIndex() {
  const [stockModal, setStockModal] = useState<{ mode: "create" | "edit"; row?: ResourceStock } | null>(null);

  return (
    <div>
      <div className="flex items-center justify-between border-b border-white/10 px-6">
        <ResourcesTabs />
        <Button className="my-2" onClick={() => setStockModal({ mode: "create" })}>
          + Add stock
        </Button>
      </div>

      <div className="p-6">
        <DataTableCardField<ResourceStock>
          apiUrl="/api/resources"
          apiQueryKey="resources"
          columns={stockColumns}
          renderCard={StockCard}
          onEditRow={(row) => setStockModal({ mode: "edit", row })}
        />
      </div>

      {stockModal && (
        <FormModal<ResourceStock>
          open
          onClose={() => setStockModal(null)}
          title={stockModal.mode === "create" ? "Add stock" : "Edit stock"}
          submitLabel={stockModal.mode === "create" ? "Add stock" : "Save changes"}
          fields={stockFields}
          mode={stockModal.mode}
          row={stockModal.row}
          invalidateKeys={tableListInvalidateKeys("resources")}
          mutationFn={async (payload) =>
            stockModal.mode === "create"
              ? resourcesService.create(payload as Partial<ResourceStock>)
              : resourcesService.update(stockModal.row!.id, payload as Partial<ResourceStock>)
          }
          onSuccess={() => setStockModal(null)}
        />
      )}
    </div>
  );
}

ResourcesIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default ResourcesIndex;
