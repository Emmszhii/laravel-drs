import { useState } from "react";
import { PageProps } from "@/Types/models";
import AdminLayout from "../../Layouts/AdminLayout";
import Inventory from "./Inventory";

type Props = PageProps<{
  stock: Array<{ id: string; type: string; depot: string; available: number; reserved: number }>;
  resourceTypes: Array<{ id: string; name: string; unit: string }>;
}>;

function ResourcesIndex({ stock, resourceTypes }: Props) {
  const [subtab, setSubtab] = useState<"stock" | "types">("stock");

  return (
    <div>
      <div className="flex gap-5 border-b border-white/10 px-6">
        <button
          onClick={() => setSubtab("stock")}
          className={`border-b-2 py-2.5 text-sm font-semibold uppercase tracking-wide ${
            subtab === "stock" ? "border-[var(--amber)]" : "border-transparent text-[var(--text-secondary)]"
          }`}
        >
          Stock
        </button>
        <button
          onClick={() => setSubtab("types")}
          className={`border-b-2 py-2.5 text-sm font-semibold uppercase tracking-wide ${
            subtab === "types" ? "border-[var(--amber)]" : "border-transparent text-[var(--text-secondary)]"
          }`}
        >
          Resource types
        </button>
      </div>

      {subtab === "stock" ? (
        <Inventory stock={stock} />
      ) : (
        <div className="p-6">
          <h2 className="mb-4 text-lg font-semibold uppercase">Resource types</h2>
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-xs uppercase text-[var(--text-secondary)]">
                <th className="p-2">Name</th>
                <th className="p-2">Unit</th>
              </tr>
            </thead>
            <tbody>
              {resourceTypes.map((t) => (
                <tr key={t.id} className="border-t border-white/10">
                  <td className="p-2">{t.name}</td>
                  <td className="p-2">{t.unit}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

ResourcesIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default ResourcesIndex;
