#!/usr/bin/env bash
set -euo pipefail

# One-time OSRM data preparation for the Philippines road network.
# Run this manually before `docker compose up` will serve real directions —
# it is NOT run automatically as part of the compose stack because it is
# a large download + CPU/RAM-heavy preprocessing step (ballpark: 10-30+
# minutes, a few GB of disk, 1-3 GB RAM peak during partition/customize).
#
# Usage: ./docker/osrm/prepare.sh

cd "$(dirname "${BASH_SOURCE[0]}")/data"

PBF_URL="https://download.geofabrik.de/asia/philippines-latest.osm.pbf"
PBF_FILE="philippines-latest.osm.pbf"
OSRM_FILE="philippines-latest.osrm"

if [ ! -f "$PBF_FILE" ]; then
  echo "==> Downloading $PBF_URL"
  curl -fL -o "$PBF_FILE" "$PBF_URL"
else
  echo "==> $PBF_FILE already present, skipping download"
fi

echo "==> Extracting (car profile) — this is the slowest step"
docker run --rm -t -v "$(pwd):/data" osrm/osrm-backend \
  osrm-extract -p /opt/car.lua "/data/$PBF_FILE"

echo "==> Partitioning"
docker run --rm -t -v "$(pwd):/data" osrm/osrm-backend \
  osrm-partition "/data/$OSRM_FILE"

echo "==> Customizing"
docker run --rm -t -v "$(pwd):/data" osrm/osrm-backend \
  osrm-customize "/data/$OSRM_FILE"

echo "==> Done. docker/osrm/data now has $OSRM_FILE and friends."
echo "    Start (or restart) the 'osrm' service in docker-compose.yml to serve them:"
echo "      docker compose up -d osrm"
