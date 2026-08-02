import { Link } from "@inertiajs/react";
import { PageProps } from "@/Types/models";
import AdminLayout from "../../Layouts/AdminLayout";

type SettingsUser = { id: string; name: string; email: string; role: string };
type Props = PageProps<{ users: SettingsUser[] }>;

function SettingsUsersTabs() {
  return (
    <div className="flex gap-5 border-b border-white/10 px-6">
      <button className="border-b-2 border-[var(--amber)] py-2.5 text-sm font-semibold uppercase tracking-wide">
        Users
      </button>
      <Link
        href="/settings/routing"
        className="border-b-2 border-transparent py-2.5 text-sm font-semibold uppercase tracking-wide text-[var(--text-secondary)]"
      >
        Routing
      </Link>
    </div>
  );
}

function SettingsUsersIndex({ users }: Props) {
  return (
    <div>
      <SettingsUsersTabs />
      <div className="p-6">
        <h2 className="mb-4 text-lg font-semibold uppercase">Users</h2>
        <table className="w-full text-sm">
          <thead>
            <tr className="text-left text-xs uppercase text-[var(--text-secondary)]">
              <th className="p-2">Name</th>
              <th className="p-2">Email</th>
              <th className="p-2">Role</th>
            </tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={u.id} className="border-t border-white/10">
                <td className="p-2">{u.name}</td>
                <td className="p-2">{u.email}</td>
                <td className="p-2">{u.role}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

SettingsUsersIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default SettingsUsersIndex;
