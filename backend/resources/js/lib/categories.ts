/** The paper ICS 213-RR form's "Subject" choices; an item has one, a request's subject is the set of its items'. Mirrors ResourceType::CATEGORIES. */
export const CATEGORY_LABEL: Record<string, string> = {
  medical: "Medical",
  cleaning: "Cleaning",
  office: "Office",
  general: "General",
  other: "Others",
};

export const categoryOptions = Object.entries(CATEGORY_LABEL).map(([value, label]) => ({ value, label }));

/** "Medical, Office": the distinct categories of a request's items, in the form's order. */
export function subjectOf(items: { category: string }[]): string {
  const used = new Set(items.map((i) => i.category));

  return Object.keys(CATEGORY_LABEL).filter((c) => used.has(c)).map((c) => CATEGORY_LABEL[c]).join(", ");
}
