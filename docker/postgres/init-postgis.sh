#!/bin/bash
set -e

# Runs automatically on first container start via
# /docker-entrypoint-initdb.d/init-postgis.sh (see docker-compose.yml).
# Enables PostGIS so `lat`/`lng` columns can later be migrated to
# proper geography(Point, 4326) columns for real spatial queries
# (ST_Distance, ST_DWithin, etc.) instead of the haversine PHP/Python
# fallback used in Services/Gis/GeofenceService.php.

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE EXTENSION IF NOT EXISTS postgis;
    CREATE EXTENSION IF NOT EXISTS postgis_topology;
EOSQL

echo "PostGIS extension enabled on database '$POSTGRES_DB'."
