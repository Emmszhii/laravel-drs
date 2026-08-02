/** Row-level action handlers + permission flags handed to a card/row renderer. */
export interface CardActions {
  onEdit: () => void;
  onArchive: () => void;
  onRestore: () => void;
  onDelete: () => void;
  restoring: boolean;
  archiving: boolean;
  canEdit: boolean;
  canArchive: boolean;
  canDelete: boolean;
}
