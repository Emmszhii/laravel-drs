import { useEffect, useState } from "react";
import type { Dispatch } from "@/Types/models";

type PositionUpdatePayload = {
  id: string;
  disaster_id: string;
  status: Dispatch["status"];
  current_lat: number | null;
  current_lng: number | null;
  last_position_at: string | null;
  vehicle_label: string | null;
};

/**
 * Subscribes to the `dispatches` Reverb channel (see App\Events\
 * DispatchPositionUpdated / routes/channels.php) and merges live position
 * updates into an initial list of dispatches fetched via props/API. First
 * real consumer of the already-configured window.Echo (see bootstrap.ts).
 */
export default function useDispatchTracking(initial: Dispatch[]) {
  const [dispatches, setDispatches] = useState<Dispatch[]>(initial);

  useEffect(() => {
    setDispatches(initial);
  }, [initial]);

  useEffect(() => {
    const channel = window.Echo.channel("dispatches");

    channel.listen(
      ".dispatch.position-updated",
      (payload: PositionUpdatePayload) => {
        setDispatches((prev) =>
          prev.map((d) =>
            d.id === payload.id
              ? {
                  ...d,
                  status: payload.status,
                  current_lat: payload.current_lat,
                  current_lng: payload.current_lng,
                  last_position_at: payload.last_position_at,
                }
              : d,
          ),
        );
      },
    );

    return () => {
      window.Echo.leave("dispatches");
    };
  }, []);

  return dispatches;
}
