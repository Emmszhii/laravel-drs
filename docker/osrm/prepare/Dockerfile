FROM osrm/osrm-backend

# osrm/osrm-backend is built on Debian 9 "stretch", now EOL — its normal
# deb.debian.org/security.debian.org mirrors 404. Only the base `main` repo
# is archived at archive.debian.org in a form apt can still read; drop the
# security/stretch-updates lines entirely (not needed for this throwaway
# build tool) and point the remaining one there.
RUN sed -i \
    -e '/security.debian.org/d' \
    -e '/stretch-updates/d' \
    -e 's|deb.debian.org|archive.debian.org|g' \
    /etc/apt/sources.list

# osmctools for `osmconvert` (clipping to Laguna province), curl for the PBF
# download, ca-certificates so that download is actually verified over TLS.
RUN apt-get -o Acquire::Check-Valid-Until=false update && apt-get install -y --no-install-recommends \
    curl osmctools ca-certificates \
    && rm -rf /var/lib/apt/lists/*

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
