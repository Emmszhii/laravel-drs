import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { render, screen, fireEvent, cleanup } from "@testing-library/react";
import DirectionsPanel from "./DirectionsPanel";
import useDirections from "../../Hooks/useDirections";
import useVoiceGuidance from "../../Hooks/useVoiceGuidance";
import useWatchPosition from "../../Hooks/useWatchPosition";
import type { DirectionsResult, DirectionsStep } from "@/Types/models";

vi.mock("../../Hooks/useDirections", () => ({
  default: vi.fn(),
}));

vi.mock("../../Hooks/useVoiceGuidance", () => ({
  default: vi.fn(),
}));

vi.mock("../../Hooks/useWatchPosition", () => ({
  default: vi.fn(),
}));

const mockedUseDirections = vi.mocked(useDirections);
const mockedUseVoiceGuidance = vi.mocked(useVoiceGuidance);
const mockedUseWatchPosition = vi.mocked(useWatchPosition);

const depots = [
  { id: "d1", name: "Laguna Depot", lat: 14.1, lng: 121.05 },
  { id: "d2", name: "Cavite Depot", lat: 14.2, lng: 120.9 },
];
const destinations = [
  { id: "z1", name: "Zone Alpha", lat: 14.3, lng: 121.1 },
];

function makeStep(overrides: Partial<DirectionsStep> = {}): DirectionsStep {
  return {
    instruction: "Head north",
    distance_m: 500,
    duration_s: 60,
    maneuver_type: "depart",
    maneuver_modifier: null,
    location: [121.05, 14.1],
    ...overrides,
  };
}

function directionsReturn(overrides: Partial<ReturnType<typeof useDirections>> = {}) {
  return {
    route: null,
    steps: [],
    loading: false,
    error: null,
    currentStepIndex: 0,
    next: vi.fn(),
    prev: vi.fn(),
    reset: vi.fn(),
    ...overrides,
  };
}

function voiceGuidanceReturn(overrides: Partial<ReturnType<typeof useVoiceGuidance>> = {}) {
  return {
    muted: false,
    toggleMute: vi.fn(),
    supported: true,
    speak: vi.fn(),
    ...overrides,
  };
}

function watchPositionReturn(overrides: Partial<ReturnType<typeof useWatchPosition>> = {}) {
  return {
    position: null,
    error: null,
    watching: false,
    start: vi.fn(),
    stop: vi.fn(),
    ...overrides,
  };
}

describe("DirectionsPanel", () => {
  beforeEach(() => {
    mockedUseDirections.mockReturnValue(directionsReturn());
    mockedUseVoiceGuidance.mockReturnValue(voiceGuidanceReturn());
    mockedUseWatchPosition.mockReturnValue(watchPositionReturn());
  });

  afterEach(() => {
    cleanup();
  });

  it("renders the depot and destination selects", () => {
    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(screen.getByText("Select a depot…")).toBeInTheDocument();
    expect(screen.getByText("Laguna Depot")).toBeInTheDocument();
    expect(screen.getByText("Cavite Depot")).toBeInTheDocument();

    expect(screen.getByText("Select a destination…")).toBeInTheDocument();
    expect(screen.getByText("Zone Alpha")).toBeInTheDocument();
  });

  it("shows the loading message while a route is being calculated", () => {
    mockedUseDirections.mockReturnValue(directionsReturn({ loading: true }));

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(screen.getByText("Calculating route…")).toBeInTheDocument();
  });

  it("shows an error message when the directions request fails", () => {
    mockedUseDirections.mockReturnValue(directionsReturn({ error: "Directions request failed" }));

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(screen.getByText("Directions request failed")).toBeInTheDocument();
  });

  it("shows the route summary (distance + duration) once a route resolves", () => {
    const route: DirectionsResult = {
      ok: true,
      distance_m: 5500,
      duration_s: 600,
      geometry: [],
      steps: [makeStep()],
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps: route.steps, currentStepIndex: 0 }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(screen.getByText("5.5 km")).toBeInTheDocument();
    expect(screen.getByText("10 min")).toBeInTheDocument();
  });

  it('appends "(with traffic)" when duration_in_traffic_s is present', () => {
    const route: DirectionsResult = {
      ok: true,
      distance_m: 5500,
      duration_s: 600,
      duration_in_traffic_s: 900,
      geometry: [],
      steps: [makeStep()],
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps: route.steps, currentStepIndex: 0 }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(screen.getByText(/15 min/)).toBeInTheDocument();
    expect(screen.getByText(/\(with traffic\)/)).toBeInTheDocument();
  });

  it("does not show the traffic suffix when duration_in_traffic_s is absent", () => {
    const route: DirectionsResult = {
      ok: true,
      distance_m: 5500,
      duration_s: 600,
      geometry: [],
      steps: [makeStep()],
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps: route.steps, currentStepIndex: 0 }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(screen.queryByText(/with traffic/)).not.toBeInTheDocument();
  });

  it("shows the fallback-reason banner when present", () => {
    const route: DirectionsResult = {
      ok: true,
      distance_m: 5500,
      duration_s: 600,
      geometry: [],
      steps: [makeStep()],
      fallback_reason: "cap_exceeded",
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps: route.steps, currentStepIndex: 0 }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(
      screen.getByText("Basic navigation — premium routing limit reached this month"),
    ).toBeInTheDocument();
  });

  it("does not show a fallback banner when fallback_reason is absent", () => {
    const route: DirectionsResult = {
      ok: true,
      distance_m: 5500,
      duration_s: 600,
      geometry: [],
      steps: [makeStep()],
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps: route.steps, currentStepIndex: 0 }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(screen.queryByText(/premium/)).not.toBeInTheDocument();
  });

  it("renders the turn-by-turn step list with the current step highlighted", () => {
    const steps = [
      makeStep({ instruction: "Head north", distance_m: 500 }),
      makeStep({ instruction: "Turn left", distance_m: 1200 }),
    ];
    const route: DirectionsResult = {
      ok: true,
      distance_m: 1700,
      duration_s: 300,
      geometry: [],
      steps,
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps, currentStepIndex: 1 }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    const firstItem = screen.getByText("Head north").closest("li");
    const secondItem = screen.getByText("Turn left").closest("li");

    expect(firstItem).not.toHaveClass("border-[var(--amber)]");
    expect(secondItem).toHaveClass("border-[var(--amber)]");
  });

  it("clicking Next calls the hook's next() function", () => {
    const next = vi.fn();
    const prev = vi.fn();
    const steps = [makeStep({ instruction: "A" }), makeStep({ instruction: "B" })];
    const route: DirectionsResult = {
      ok: true,
      distance_m: 1000,
      duration_s: 120,
      geometry: [],
      steps,
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps, currentStepIndex: 0, next, prev }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    fireEvent.click(screen.getByText("Next"));

    expect(next).toHaveBeenCalledTimes(1);
  });

  it("clicking Prev calls the hook's prev() function", () => {
    const next = vi.fn();
    const prev = vi.fn();
    const steps = [makeStep({ instruction: "A" }), makeStep({ instruction: "B" })];
    const route: DirectionsResult = {
      ok: true,
      distance_m: 1000,
      duration_s: 120,
      geometry: [],
      steps,
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps, currentStepIndex: 1, next, prev }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    fireEvent.click(screen.getByText("Prev"));

    expect(prev).toHaveBeenCalledTimes(1);
  });

  it("disables Prev at the first step and Next at the last step", () => {
    const steps = [makeStep({ instruction: "A" }), makeStep({ instruction: "B" })];
    const route: DirectionsResult = {
      ok: true,
      distance_m: 1000,
      duration_s: 120,
      geometry: [],
      steps,
    };
    mockedUseDirections.mockReturnValue(
      directionsReturn({ route, steps, currentStepIndex: 0 }),
    );

    render(<DirectionsPanel depots={depots} destinations={destinations} />);

    expect(screen.getByText("Prev")).toBeDisabled();
    expect(screen.getByText("Next")).not.toBeDisabled();
  });
});
