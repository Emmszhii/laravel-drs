from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    app_name: str = "DisasterResource+ Optimization Service"
    environment: str = "local"
    log_level: str = "INFO"

    # Metrics/observability — wire up Prometheus or similar before production.
    metrics_enabled: bool = False

    class Config:
        env_prefix = "SOLVER_"


settings = Settings()
