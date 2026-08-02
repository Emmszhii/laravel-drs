type Props = {
  stock: Array<{ id: string; type: string; depot: string; available: number; reserved: number }>;
};

/**
 * Stock tab of the Resources module — should be the default tab, per
 * the agreed UX (Resource types is the secondary tab). See the prototype
 * for the search/filter/pagination behavior to reproduce here.
 */
export default function Inventory({ stock }: Props) {
  return (
    <div className="p-6">
      <h2 className="mb-4 text-lg font-semibold uppercase">Stock</h2>
      <table className="w-full text-sm">
        <thead>
          <tr className="text-left text-xs uppercase text-[var(--text-secondary)]">
            <th className="p-2">Type</th>
            <th className="p-2">Depot</th>
            <th className="p-2">Available</th>
            <th className="p-2">Reserved</th>
          </tr>
        </thead>
        <tbody>
          {stock.map((s) => (
            <tr key={s.id} className="border-t border-white/10">
              <td className="p-2">{s.type}</td>
              <td className="p-2">{s.depot}</td>
              <td className="p-2">{s.available}</td>
              <td className="p-2">{s.reserved}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
