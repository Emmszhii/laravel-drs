import { X } from 'lucide-react';
import { createPortal } from 'react-dom';

interface ImageLightboxProps {
  open: boolean;
  src: string;
  onClose: () => void;
}

/** Full-screen image viewer used by FileUploadField's preview "Eye" button. */
export function ImageLightbox({ open, src, onClose }: ImageLightboxProps) {
  if (!open) {
    return null;
  }

  return createPortal(
    <div
      className="fixed inset-0 z-100 flex items-center justify-center bg-black/80 p-6"
      role="presentation"
      onClick={onClose}
    >
      <button
        type="button"
        onClick={onClose}
        aria-label="Close"
        className="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20"
      >
        <X size={20} />
      </button>
      <img src={src} alt="" className="max-h-full max-w-full rounded-md object-contain" onClick={(e) => e.stopPropagation()} />
    </div>,
    document.body,
  );
}

export default ImageLightbox;
