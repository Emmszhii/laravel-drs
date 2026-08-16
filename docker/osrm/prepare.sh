#!/usr/bin/env bash
set -euo pipefail

# On Git Bash for Windows, MSYS auto-converts any argument that looks like an
# absolute POSIX path (e.g. /opt/car.lua, /data/...) into a Windows path
# before it reaches `docker run` — but those paths are meant to be resolved
# *inside* the Linux container, not on the host. MSYS_NO_PATHCONV disables
# that rewriting; it's a no-op on Linux/macOS.
export MSYS_NO_PATHCONV=1

# One-time OSRM data preparation, scoped to Laguna province (the app's
# actual operational area) rather than the whole Philippines — keeps the
# download and preprocessing fast and light. Run this manually before
# `docker compose up` will serve real directions — it is NOT run
# automatically as part of the compose stack.
#
# Note: routing only works within this bounding box. Seeded demo incidents
# outside Laguna (Albay, Marikina, Tacloban, Itogon, Cagayan de Oro — see
# database/seeders/DisasterDataSeeder.php) will not get real directions
# against this dataset.
#
# Usage: ./docker/osrm/prepare.sh

cd "$(dirname "${BASH_SOURCE[0]}")/data"

PBF_URL="https://download.geofabrik.de/asia/philippines-latest.osm.pbf"
PBF_FILE="philippines-latest.osm.pbf"
# Laguna province bounding box (left,bottom,right,top), padded slightly
# beyond the provincial boundary so roads crossing it still route correctly.
LAGUNA_BBOX="120.95,13.95,121.70,14.55"
LAGUNA_PBF="laguna-latest.osm.pbf"
OSRM_FILE="laguna-latest.osrm"

if [ ! -f "$PBF_FILE" ]; then
  echo "==> Downloading $PBF_URL"
  curl -fL -o "$PBF_FILE" "$PBF_URL"
else
  echo "==> $PBF_FILE already present, skipping download"
fi

echo "==> Clipping to Laguna province ($LAGUNA_BBOX)"
docker run --rm -v "$(pwd):/data" debian:bookworm-slim bash -c \
  "apt-get update -qq && apt-get install -y -qq osmctools >/dev/null && \
   osmconvert /data/$PBF_FILE -b=$LAGUNA_BBOX --complete-ways -o=/data/$LAGUNA_PBF"

echo "==> Extracting (car profile) — this is the slowest step"
docker run --rm -t -v "$(pwd):/data" osrm/osrm-backend \
  osrm-extract -p /opt/car.lua "/data/$LAGUNA_PBF"

echo "==> Partitioning"
docker run --rm -t -v "$(pwd):/data" osrm/osrm-backend \
  osrm-partition "/data/$OSRM_FILE"

echo "==> Customizing"
docker run --rm -t -v "$(pwd):/data" osrm/osrm-backend \
  osrm-customize "/data/$OSRM_FILE"

echo "==> Done. docker/osrm/data now has $OSRM_FILE and friends."
echo "    Start (or restart) the 'osrm' service in docker-compose.yml to serve them:"
echo "      docker compose up -d osrm"
