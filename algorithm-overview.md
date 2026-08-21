# Algorithm Plan Overview

Plain-language companion to [`algorithm.md`](algorithm.md) — what each phase is *for*, not the file-level implementation detail. Read `algorithm.md` for exact files/line numbers to change.

## The two "algorithms" at the core

1. **PuLP allocation solver** (`solver-service/app/solvers/pulp_solver.py`) — a linear program that decides *how much of each resource each depot ships to each zone*, minimizing unmet demand under depot capacity. Answers "who gets what, from where."
2. **OR-Tools routing solver / CVRPTW** (`solver-service/app/solvers/ortools_solver.py`) — decides *what order a vehicle visits its stops in*, respecting vehicle capacity and delivery time windows. Answers "in what order does a truck drive."

Today #1 mostly works but its capacity limit is silently disabled by a bug, and #2 computes a route internally then throws it away — it was never wired to produce usable output. The five phases in `algorithm.md` close that gap step by step.

## Phase 1 — Fix capacity + real road distances

**Purpose:** stop the allocation solver from over-promising a depot's stock, and stop it from reasoning about distance using straight-line haversine when real roads exist (OSRM). Everything downstream depends on this being correct first.

## Phase 2 — Make CVRPTW actually work

**Purpose:** turn the routing solver from a distance-minimizing skeleton that discards its answer into a real two-stage pipeline: allocation LP decides quantities → OR-Tools decides vehicle stop order under capacity/time-window constraints. Produces an actual multi-stop route per vehicle.

## Phase 3 — Persist routes as real dispatches

**Purpose:** connect the solver's output to what coordinators actually look at. Today `Dispatch` rows are typed in by hand; this phase makes the optimizer write dispatch rows directly (with vehicle + allocation linked), so the dispatch board reflects the algorithm's decisions instead of manual entries.

## Phase 4 — PostGIS + road closures

**Purpose:** move spatial math (distance checks, zone/depot geometry) from application-level haversine to the database's native geography type, and add the ability to flag a route as blocked by a known closure. Supporting infrastructure, not a new decision-making algorithm.

## Phase 5 — Traffic-aware navigation

**Purpose:** the driver-facing experience, separate from the allocation/routing solvers above — live traffic data, picking the fastest route (not shortest), and turn-by-turn auto-advance as the driver actually drives, reusing the GPS tracking that already exists for dispatch position updates.

## Summary

Phases 1–3 make the system *decide well* (correct allocations, real routes, real dispatches). Phase 4 makes the *spatial data* correct and closure-aware. Phase 5 makes the *driver's screen* behave like a real navigation app once they're already dispatched.
