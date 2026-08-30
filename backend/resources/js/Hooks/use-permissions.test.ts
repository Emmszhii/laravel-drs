import { describe, it, expect, vi } from "vitest";
import { renderHook } from "@testing-library/react";
import { usePage } from "@inertiajs/react";
import { usePermission } from "./use-permissions";

vi.mock("@inertiajs/react", () => ({
  usePage: vi.fn(),
}));

const mockedUsePage = vi.mocked(usePage);

function withAuthUser(user: unknown) {
  mockedUsePage.mockReturnValue({
    props: { auth: { user } },
  } as unknown as ReturnType<typeof usePage>);
}

describe("usePermission", () => {
  it("exposes the permissions/roles arrays from the Inertia shared props", () => {
    withAuthUser({ permissions: ["view-dashboard", "edit-depots"], roles: ["coordinator"] });

    const { result } = renderHook(() => usePermission());

    expect(result.current.permissions).toEqual(["view-dashboard", "edit-depots"]);
    expect(result.current.roles).toEqual(["coordinator"]);
  });

  it("can() returns true only for a permission the user has", () => {
    withAuthUser({ permissions: ["view-dashboard"], roles: [] });

    const { result } = renderHook(() => usePermission());

    expect(result.current.can("view-dashboard")).toBe(true);
    expect(result.current.can("delete-depot")).toBe(false);
  });

  it("hasRole() returns true only for a role the user has", () => {
    withAuthUser({ permissions: [], roles: ["admin"] });

    const { result } = renderHook(() => usePermission());

    expect(result.current.hasRole("admin")).toBe(true);
    expect(result.current.hasRole("viewer")).toBe(false);
  });

  it("defaults permissions/roles to empty arrays when the user has none defined", () => {
    withAuthUser({});

    const { result } = renderHook(() => usePermission());

    expect(result.current.permissions).toEqual([]);
    expect(result.current.roles).toEqual([]);
    expect(result.current.can("anything")).toBe(false);
    expect(result.current.hasRole("anything")).toBe(false);
  });

  it("handles no logged-in user at all (auth.user undefined)", () => {
    mockedUsePage.mockReturnValue({
      props: { auth: {} },
    } as unknown as ReturnType<typeof usePage>);

    const { result } = renderHook(() => usePermission());

    expect(result.current.permissions).toEqual([]);
    expect(result.current.roles).toEqual([]);
    expect(result.current.can("x")).toBe(false);
  });
});
