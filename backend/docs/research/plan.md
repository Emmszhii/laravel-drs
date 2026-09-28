# Open a Claude-in-Chrome tab

## Context
The user wants a tab open in their real Chrome through the Claude extension. The extension is connected ("Browser 1", Windows, in use).

## Steps
1. `tabs_context_mcp` with `createIfEmpty: true`. This creates the MCP tab group and window if none exists.
2. If the group already exists, run `tabs_create_mcp` to open a fresh tab.
3. Leave the tab open, since the user asked for it. Optionally navigate to the local app (`http://localhost:8000`) if asked.

## Verification
The `tabs_context_mcp` result lists the new tab ID.