from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    app_name: str = "DisasterResource+ Optimization Service"
    environment: str = "local"
    log_level: str = "INFO"

    # Metrics/observability — wire up Prometheus or similar before production.
    metrics_enabled: bool = False

    # In-network hostname (matches backend's OSRM_URL=http://osrm:5000 in
    # .env.example) — solver-service and the `osrm` container share the same
    # docker-compose network, so this is a direct HTTP call, no new infra.
    osrm_url: str = "http://osrm:5000"

    class Config:
        env_prefix = "SOLVER_"


settings = Settings()
