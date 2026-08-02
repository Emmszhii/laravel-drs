import AdminLayout from "../../Layouts/AdminLayout";

function ReportsIndex() {
  return (
    <div className="p-6">
      <h2 className="mb-4 text-lg font-semibold uppercase">Reports</h2>
      <p className="text-sm text-[var(--text-secondary)]">
        No reports generated yet — reporting isn't backed by real data yet.
      </p>
    </div>
  );
}

ReportsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default ReportsIndex;
