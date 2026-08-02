/** Payload shape for a `flash.toast` prop sent from a Laravel redirect. */
export interface FlashToast {
  type: 'success' | 'error' | 'info' | 'warning';
  message: string;
}
