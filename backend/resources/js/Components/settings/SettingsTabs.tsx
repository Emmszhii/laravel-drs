import { RouteTabs } from "@/Components/RouteTabs";

/** Shared tab bar for the Settings section — each subtab is a real route (see CLAUDE.md). */
export function SettingsTabs() {
  return <RouteTabs tabs={[{ href: "/settings/users", label: "Users" }, { href: "/settings/smtp", label: "Email (SMTP)" }]} />;
}
