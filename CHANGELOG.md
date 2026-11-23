# Changelog

## 2026-11-23

- Store a PRTG object id on each service, and use the product Object ID when the service has none
- Serve client-area graphs from the module, so the API token never reaches the browser
- Stop reading the shared `.env` sensor id, which showed the same graph on every service
- Remove the sample client-area hooks, the sample manage page, and the unfinished single sign-on redirect
