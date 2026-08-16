#!/usr/bin/env bash
set -euo pipefail

# Runs as the `osrm-prepare` service (docker-compose.yml), against the same
# volume `osrm` serves from. Idempotent — skips already-completed steps, so
# this is fast on every deploy/`docker compose up` after the first
# successful run. Scoped to Laguna province (the app's actual operational
# area) rather than the whole Philippines, to keep this fast and light.
#
# Note: routing only works within this bounding box. Seeded demo incidents
# outside Laguna (Albay, Marikina, Tacloban, Itogon, Cagayan de Oro — see
# database/seeders/DisasterDataSeeder.php) will not get real directions
# against this dataset.

PBF_URL="https://download.geofabrik.de/asia/philippines-latest.osm.pbf"
PBF_FILE="/data/philippines-latest.osm.pbf"
# Laguna province bounding box (left,bottom,right,top), padded slightly
# beyond the provincial boundary so roads crossing it still route correctly.
LAGUNA_BBOX="120.95,13.95,121.70,14.55"
LAGUNA_PBF="/data/laguna-latest.osm.pbf"
OSRM_FILE="/data/laguna-latest.osrm"

if [ -f "$OSRM_FILE" ]; then
  echo "==> $OSRM_FILE already present — nothing to do."
  exit 0
fi

if [ -f "$PBF_FILE" ]; then
  echo "==> $PBF_FILE already present, skipping download"
else
  echo "==> Downloading $PBF_URL"
  curl -fL -o "$PBF_FILE" "$PBF_URL"
fi

echo "==> Clipping to Laguna province ($LAGUNA_BBOX)"
osmconvert "$PBF_FILE" -b="$LAGUNA_BBOX" --complete-ways -o="$LAGUNA_PBF"

echo "==> Extracting (car profile) — this is the slowest step"
osrm-extract -p /opt/car.lua "$LAGUNA_PBF"

echo "==> Partitioning"
osrm-partition "$OSRM_FILE"

echo "==> Customizing"
osrm-customize "$OSRM_FILE"

echo "==> Done."
