# WHMCS Colocation Module

Provisioning module that shows PRTG traffic graphs on a colocation service.

## Object ID

An Object ID is the PRTG sensor or object id (the `id` on a PRTG `chart.png` URL). It is not a rack, a port, or the WHMCS service id. An admin types it.

1. **Service.** On the service, the module field **Graph Object ID**. Stored in `mod_colocation_object_ids`. Used when it is set.
2. **Product.** The product module setting **Object ID** (the second module option, `configoption2`). Used when the service has no object id.

Leave the service field blank to use the product value. The module creates `mod_colocation_object_ids` the first time that field is saved.

If neither value is a positive integer, the client area says that no graph is configured and does not call PRTG. `PRTG_SENSOR_ID` from older `.env` files is not read.

The module directory is on the web. Apache's `.htaccess` in this directory refuses to serve `.env`. On nginx, block that file in the server config.

## Graphs

The client area loads charts from `modules/servers/colocation/graph.php`. The script checks that the logged-in client owns the service, resolves the object id on the server, and fetches the PNG from PRTG. The PRTG address and API token are not written into the page.

`graphid` is limited to `0`–`3`: 2 hours, 2 days, 30 days, and 365 days.

## Configuration

Copy `.env.example` to `.env` in this module directory. Do not commit `.env`.

- `PRTG_URL` — PRTG base URL, including the port if it is not 443.
- `PRTG_API_TOKEN` — PRTG API token, used only by the graph proxy.
- `PRTG_GRAPH_WIDTH` — chart width in pixels. Default 800.
- `PRTG_GRAPH_HEIGHT` — chart height in pixels. Default 300.

From this directory:

```bash
composer install
```

## Symbolic linking to a test WHMCS installation

WHMCS loads the module from `modules/servers/colocation`. A symlink is enough. Replace the paths with your own, and point it at a test WHMCS.

```bash
ln -s /Volumes/nvme/Code/whmcs-colocation-prtg /path/to/whmcs/modules/servers/colocation
```

Removing the symbolic link:

```bash
rm /path/to/whmcs/modules/servers/colocation
```

The graph proxy finds WHMCS from the public URL path, so the module directory itself can live outside the WHMCS tree.
