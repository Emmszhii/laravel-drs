#!/usr/bin/env bash
set -euo pipefail

# One-time OSRM data preparation for the Coolify-deployed server, scoped to
# Laguna province (the app's actual operational area). Unlike
# docker/osrm/prepare.sh (which targets a local bind-mounted directory for
# local dev), this populates the named Docker volume the `osrm` service
# uses in production (docker-compose.yml) — a volume, not a bind mount,
# because Coolify checks this repo out into a fresh ephemeral directory on
# every deploy, so a bind mount here would lose the prepared data the
# moment the next deploy ran.
#
# Run this directly on the Coolify server (e.g. via its web terminal), not
# from a local machine — it populates a Docker volume on whichever host it
# runs on.
#
# Note: routing only works within this bounding box. Seeded demo incidents
# outside Laguna (Albay, Marikina, Tacloban, Itogon, Cagayan de Oro — see
# database/seeders/DisasterDataSeeder.php) will not get real directions
# against this dataset.
#
# Usage: ./docker/osrm/prepare-server.sh [volume-name]
#   volume-name defaults to osrm-data-x11oqof2fbwxdq26ev780h1c, matching
#   docker-compose.yml's `osrm` service — only pass an override if that
#   resource UUID ever changes (e.g. the Coolify resource was recreated).

VOLUME="${1:-osrm-data-x11oqof2fbwxdq26ev780h1c}"
PBF_URL="https://download.geofabrik.de/asia/philippines-latest.osm.pbf"
PBF_FILE="philippines-latest.osm.pbf"
# Laguna province bounding box (left,bottom,right,top), padded slightly
# beyond the provincial boundary so roads crossing it still route correctly.
LAGUNA_BBOX="120.95,13.95,121.70,14.55"
LAGUNA_PBF="laguna-latest.osm.pbf"
OSRM_FILE="laguna-latest.osrm"

echo "==> Creating volume $VOLUME (no-op if it already exists)"
docker volume create "$VOLUME" >/dev/null

file_exists_in_volume() {
  docker run --rm -v "$VOLUME:/data" busybox test -f "/data/$1"
}

if file_exists_in_volume "$PBF_FILE"; then
  echo "==> $PBF_FILE already present in volume, skipping download"
else
  echo "==> Downloading $PBF_URL"
  docker run --rm -v "$VOLUME:/data" curlimages/curl \
    -fL -o "/data/$PBF_FILE" "$PBF_URL"
fi

if file_exists_in_volume "$OSRM_FILE"; then
  echo "==> $OSRM_FILE already present in volume — skipping clip/extract/partition/customize."
  echo "    Delete it from the volume first if you want to regenerate (e.g. after changing the bbox)."
else
  echo "==> Clipping to Laguna province ($LAGUNA_BBOX)"
  docker run --rm -v "$VOLUME:/data" debian:bookworm-slim bash -c \
    "apt-get update -qq && apt-get install -y -qq osmctools >/dev/null && \
     osmconvert /data/$PBF_FILE -b=$LAGUNA_BBOX --complete-ways -o=/data/$LAGUNA_PBF"

  echo "==> Extracting (car profile) — this is the slowest step"
  docker run --rm -t -v "$VOLUME:/data" osrm/osrm-backend \
    osrm-extract -p /opt/car.lua "/data/$LAGUNA_PBF"

  echo "==> Partitioning"
  docker run --rm -t -v "$VOLUME:/data" osrm/osrm-backend \
    osrm-partition "/data/$OSRM_FILE"

  echo "==> Customizing"
  docker run --rm -t -v "$VOLUME:/data" osrm/osrm-backend \
    osrm-customize "/data/$OSRM_FILE"
fi

echo "==> Done. Volume $VOLUME now has $OSRM_FILE and friends."
echo "    Redeploy (or restart) the 'osrm' service to pick it up."
