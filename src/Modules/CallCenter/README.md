# Call Center Module

This module provides server-side actions for AwajDigital Call Center agents and SDK token minting/revocation:
- `ListAgentsAction` (`GET /cc/agents`)
- `ListAgentCallsAction` (`GET /cc/agents/{id}/calls`)
- `MintSdkTokenAction` (`POST /sdk/token`)
- `RevokeSdkSessionAction` (`DELETE /sdk/session`)

## Browser Widget Note

The endpoint `POST /sdk/session` (Exchange Session) is **strictly client-side (browser widget only)**.
It takes no Bearer authentication token and must be called directly by the browser widget (`amarvoice-call.js`)
running on an allowlisted HTTPS origin.

Per package specifications, this server-side Laravel package does not expose a backend action for `POST /sdk/session`.
The server application should mint an SDK token via `MintSdkTokenAction` and pass the returned JSON
directly to the browser widget.

For frontend browser widget documentation and script integration details, visit the official AwajDigital documentation:
[https://awajdigital.com/api-docs/sdk/widget](https://awajdigital.com/api-docs/sdk/widget)
