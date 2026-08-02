type Props = {
  currentPage: number;
  lastPage: number;
  total: number;
  onPageChange: (page: number) => void;
};

export default function Pagination({ currentPage, lastPage, total, onPageChange }: Props) {
  return (
    <div className="flex items-center justify-between border-t border-white/10 p-2.5 text-xs text-[var(--text-secondary)]">
      <button
        onClick={() => onPageChange(currentPage - 1)}
        disabled={currentPage <= 1}
        className="border border-white/20 px-2.5 py-1 disabled:opacity-40"
      >
        ‹ Prev
      </button>
      <span>
        Page {currentPage} of {lastPage} · {total} total
      </span>
      <button
        onClick={() => onPageChange(currentPage + 1)}
        disabled={currentPage >= lastPage}
        className="border border-white/20 px-2.5 py-1 disabled:opacity-40"
      >
        Next ›
      </button>
    </div>
  );
}
